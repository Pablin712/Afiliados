<?php

namespace App\Services;

use App\Models\CourseModule;
use App\Models\CourseVideo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseCatalogImportService
{
    public function importExistingVideos(): int
    {
        $sourceDisk = Storage::disk('public');
        $targetDisk = Storage::disk('local');

        if (! $sourceDisk->exists('videos')) {
            return 0;
        }

        $imported = 0;

        foreach ($sourceDisk->files('videos') as $path) {
            $filename = basename($path);
            $metadata = $this->resolveMetadata($filename);
            $module = $this->firstOrCreateModule($metadata);
            $protectedPath = $this->ensureProtectedCopy($sourceDisk, $targetDisk, $path, $filename);

            $video = CourseVideo::query()->firstOrNew([
                'file_path' => $protectedPath,
            ]);

            if ($video->exists) {
                if ($sourceDisk->exists($path)) {
                    $sourceDisk->delete($path);
                }

                continue;
            }

            $video->fill([
                'course_module_id' => $module->id,
                'title' => $metadata['title'],
                'slug' => $this->uniqueVideoSlug($metadata['title']),
                'description' => $metadata['description'],
                'disk' => 'local',
                'mime_type' => File::mimeType($targetDisk->path($protectedPath)) ?: 'video/mp4',
                'file_size' => (int) ($targetDisk->size($protectedPath) ?? 0),
                'sort_order' => $metadata['video_order'],
                'is_active' => true,
            ]);
            $video->save();

            $imported++;
        }

        return $imported;
    }

    protected function ensureProtectedCopy($sourceDisk, $targetDisk, string $path, string $filename): string
    {
        $targetPath = $this->uniqueTargetPath('course-videos/'.$filename, $targetDisk);

        if (! $targetDisk->exists($targetPath)) {
            $stream = $sourceDisk->readStream($path);

            if ($stream !== false) {
                $targetDisk->writeStream($targetPath, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }

        if ($sourceDisk->exists($path)) {
            $sourceDisk->delete($path);
        }

        return $targetPath;
    }

    protected function uniqueTargetPath(string $path, $disk): string
    {
        if (! $disk->exists($path)) {
            return $path;
        }

        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $suffix = 2;

        do {
            $candidate = $directory.'/'.$filename.'-'.$suffix.($extension !== '' ? '.'.$extension : '');
            $suffix++;
        } while ($disk->exists($candidate));

        return $candidate;
    }

    protected function firstOrCreateModule(array $metadata): CourseModule
    {
        return CourseModule::query()->firstOrCreate(
            ['slug' => $metadata['module_slug']],
            [
                'name' => $metadata['module_name'],
                'description' => $metadata['module_description'],
                'sort_order' => $metadata['module_order'],
                'is_active' => true,
            ]
        );
    }

    protected function resolveMetadata(string $filename): array
    {
        $normalized = Str::lower(trim($filename));

        $map = [
            'instala el scanner.mp4' => [
                'module_name' => 'Modulo scanner',
                'module_order' => 1,
                'video_order' => 1,
                'title' => 'Instala el scanner',
                'description' => 'Instalacion inicial del scanner para comenzar con el entorno de trabajo.',
            ],
            'scanner y volumenes.mp4' => [
                'module_name' => 'Modulo scanner',
                'module_order' => 1,
                'video_order' => 2,
                'title' => 'Scanner y volumenes',
                'description' => 'Introduccion al uso del scanner combinado con lectura de volumen.',
            ],
            'scanner con simetria y reposicionamiento.mp4' => [
                'module_name' => 'Modulo scanner',
                'module_order' => 1,
                'video_order' => 3,
                'title' => 'Scanner con simetria y reposicionamiento',
                'description' => 'Aplicacion del scanner con criterios de simetria y reposicionamiento.',
            ],
            'modulo 1.  simetrias.mp4' => [
                'module_name' => 'Modulo 1',
                'module_order' => 2,
                'video_order' => 1,
                'title' => 'Simetrias',
                'description' => 'Clase del modulo 1 enfocada en lectura de simetrias.',
            ],
            'scanner modulo 1  tendencias.mp4' => [
                'module_name' => 'Modulo 1',
                'module_order' => 2,
                'video_order' => 2,
                'title' => 'Tendencias',
                'description' => 'Clase del modulo 1 centrada en tendencias y contexto direccional.',
            ],
            '1_modulo 1.  temporalidades mp4.mp4' => [
                'module_name' => 'Modulo 1',
                'module_order' => 2,
                'video_order' => 3,
                'title' => 'Temporalidades',
                'description' => 'Clase del modulo 1 sobre lectura y coordinacion de temporalidades.',
            ],
            'mod 2   fractalidades y clean trades 2 clases.mp4' => [
                'module_name' => 'Modulo 2',
                'module_order' => 3,
                'video_order' => 1,
                'title' => 'Fractalidades y clean trades',
                'description' => 'Material del modulo 2 enfocado en fractalidades y clean trades.',
            ],
            'fvgs, transiciones.mp4' => [
                'module_name' => 'Modulo 3',
                'module_order' => 4,
                'video_order' => 1,
                'title' => 'FVGs y transiciones',
                'description' => 'Clase del modulo 3 enfocada en FVGs y transiciones.',
            ],
            'volumen y su funcion.mp4' => [
                'module_name' => 'Modulo 3',
                'module_order' => 4,
                'video_order' => 2,
                'title' => 'Volumen y su funcion',
                'description' => 'Clase del modulo 3 sobre el papel del volumen dentro de la lectura del mercado.',
            ],
        ];

        $metadata = $map[$normalized] ?? [
            'module_name' => 'Material adicional',
            'module_order' => 99,
            'video_order' => 99,
            'title' => $this->humanizeFilename($filename),
            'description' => 'Video importado desde storage/public/videos sin clasificacion manual todavia.',
        ];

        $metadata['module_slug'] = Str::slug($metadata['module_name']);
        $metadata['module_description'] = match ($metadata['module_name']) {
            'Modulo scanner' => 'Configuracion y primeras tecnicas guiadas alrededor del scanner.',
            'Modulo 1' => 'Base del proceso trader: simetrias, tendencias y temporalidades.',
            'Modulo 2' => 'Continuidad del aprendizaje con fractalidades y clean trades.',
            'Modulo 3' => 'Bloque avanzado con FVGs, transiciones y lectura de volumen.',
            default => 'Repositorio de videos pendientes de clasificacion definitiva.',
        };

        return $metadata;
    }

    protected function humanizeFilename(string $filename): string
    {
        $withoutExtension = pathinfo($filename, PATHINFO_FILENAME);
        $normalized = preg_replace('/[_\-.]+/', ' ', $withoutExtension) ?? $withoutExtension;

        return Str::of($normalized)
            ->squish()
            ->title()
            ->value();
    }

    protected function uniqueVideoSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (CourseVideo::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
