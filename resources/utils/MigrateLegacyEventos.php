<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyEventos extends Command
{
    protected $signature = 'legacy:migrate-eventos
                            {--dry-run : No escribe nada, solo reporta lo que haría}
                            {--legacy-connection=legacy_casa_angel : Conexión de la BD legacy (el dump crudo)}
                            {--tenant-connection=tenant_casa_angel : Conexión del tenant destino (esquema normalizado)}';

    protected $description = 'Migra las tablas dinámicas de eventos (una tabla por evento) a eventos/multimedia/evento_multimedia';

    protected string $legacyConn;
    protected string $tenantConn;
    protected bool $dryRun;

    // Tablas del dump que NO son tablas de fotos por evento
    protected array $systemTables = ['Clientes', 'temporary_events', 'Mostrario', 'Mostrarios', 'Usuarios'];

    public function handle(): int
    {
        $this->legacyConn = $this->option('legacy-connection');
        $this->tenantConn = $this->option('tenant-connection');
        $this->dryRun = (bool) $this->option('dry-run');

        $this->info('Conexión legacy: ' . $this->legacyConn . ' | Conexión tenant: ' . $this->tenantConn . ($this->dryRun ? ' [DRY RUN]' : ''));

        $eventosLegacy = DB::connection($this->legacyConn)->table('temporary_events')->get();
        $allTables = $this->allLegacyTables();
        $eventTables = array_values(array_diff($allTables, $this->systemTables));

        $codigosConTabla = [];
        $stats = ['eventos' => 0, 'multimedia' => 0, 'pivotes' => 0];

        foreach ($eventosLegacy as $row) {
            $codigo = trim($row->Codigo);
            $codigosConTabla[] = $codigo;

            if (!in_array($codigo, $allTables, true)) {
                $this->warn("  ⚠ Codigo '{$codigo}' no tiene tabla de fotos asociada. Se crea el evento sin multimedia.");
            }

            $eventoId = $this->upsertEvento($row);
            $stats['eventos']++;

            if (in_array($codigo, $eventTables, true)) {
                [$mm, $pv] = $this->migrarFotos($codigo, $eventoId);
                $stats['multimedia'] += $mm;
                $stats['pivotes'] += $pv;
            }
        }

        // Tablas de fotos que no tienen fila en Eventos: se crea un evento "huérfano" usando el código como nombre
        $huerfanas = array_diff($eventTables, $codigosConTabla);
        foreach ($huerfanas as $codigo) {
            $this->warn("  ⚠ Tabla '{$codigo}' no tiene evento asociado en Eventos. Creando evento huérfano.");
            $eventoId = $this->upsertEventoHuerfano($codigo);
            $stats['eventos']++;
            [$mm, $pv] = $this->migrarFotos($codigo, $eventoId);
            $stats['multimedia'] += $mm;
            $stats['pivotes'] += $pv;
        }

        $this->newLine();
        $this->info("Eventos: {$stats['eventos']} | Multimedia: {$stats['multimedia']} | Pivotes evento_multimedia: {$stats['pivotes']}");

        if ($this->dryRun) {
            $this->comment('Esto fue un dry-run: no se escribió nada. Vuelve a correr sin --dry-run para aplicar.');
        }

        return self::SUCCESS;
    }

    protected function allLegacyTables(): array
    {
        $database = DB::connection($this->legacyConn)->getDatabaseName();
        $rows = DB::connection($this->legacyConn)->select(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?',
            [$database]
        );

        return array_map(fn ($r) => $r->TABLE_NAME, $rows);
    }

    protected function upsertEvento(object $row): int
    {
        $data = [
            'nombre'       => $row->N_Event,
            'fecha_evento' => $this->parseFecha($row->Fecha),
            'entregado'    => strcasecmp(trim((string) $row->Estado), 'Entregado') === 0 ? $this->parseFecha($row->Fecha) : null,
            'ubicacion'    => null,
            'codigo'       => $row->Codigo,
            'updated_at'   => now(),
        ];

        if ($this->dryRun) {
            $this->line("  [dry-run] evento: {$data['nombre']} ({$data['codigo']})");
            return 0;
        }

        $existing = DB::connection($this->tenantConn)->table('eventos')->where('codigo', $row->Codigo)->first();

        if ($existing) {
            DB::connection($this->tenantConn)->table('eventos')->where('id', $existing->id)->update($data);
            return $existing->id;
        }

        $data['created_at'] = now();

        return DB::connection($this->tenantConn)->table('eventos')->insertGetId($data);
    }

    protected function upsertEventoHuerfano(string $codigo): int
    {
        $data = [
            'nombre'     => $codigo,
            'codigo'     => $codigo,
            'updated_at' => now(),
        ];

        if ($this->dryRun) {
            $this->line("  [dry-run] evento huérfano: {$codigo}");
            return 0;
        }

        $existing = DB::connection($this->tenantConn)->table('eventos')->where('codigo', $codigo)->first();
        if ($existing) {
            return $existing->id;
        }

        $data['created_at'] = now();

        return DB::connection($this->tenantConn)->table('eventos')->insertGetId($data);
    }

    protected function migrarFotos(string $tabla, int $eventoId): array
    {
        $multimediaCount = 0;
        $pivoteCount = 0;

        DB::connection($this->legacyConn)
            ->table($tabla)
            ->orderBy('Foto')
            ->chunk(500, function ($fotos) use ($eventoId, &$multimediaCount, &$pivoteCount) {
                foreach ($fotos as $foto) {
                    $url = trim((string) $foto->Foto);
                    if ($url === '') {
                        continue;
                    }

                    $cantidad = is_numeric($foto->cant) ? (int) $foto->cant : 0;

                    if ($this->dryRun) {
                        $multimediaCount++;
                        $pivoteCount++;
                        continue;
                    }

                    // Evita duplicar multimedia si el mismo archivo ya fue registrado
                    $multimediaId = DB::connection($this->tenantConn)->table('multimedia')
                        ->where('url', $url)
                        ->value('id');

                    if (!$multimediaId) {
                        $multimediaId = DB::connection($this->tenantConn)->table('multimedia')->insertGetId([
                            'url'        => $url,
                            'type'       => 'image',
                            'alt'        => trim((string) $foto->orientacion) ?: null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    $multimediaCount++;

                    $pivoteExiste = DB::connection($this->tenantConn)->table('evento_multimedia')
                        ->where('evento_id', $eventoId)
                        ->where('multimedia_id', $multimediaId)
                        ->exists();

                    if (!$pivoteExiste) {
                        DB::connection($this->tenantConn)->table('evento_multimedia')->insert([
                            'evento_id'     => $eventoId,
                            'multimedia_id' => $multimediaId,
                            'cantidad'      => $cantidad,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                        $pivoteCount++;
                    }
                }
            });

        return [$multimediaCount, $pivoteCount];
    }

    protected function parseFecha(?string $fecha): ?string
    {
        if (!$fecha || $fecha === '0000-00-00') {
            return null;
        }

        try {
            return Carbon::parse($fecha)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
