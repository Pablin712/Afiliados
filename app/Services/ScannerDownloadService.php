<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class ScannerDownloadService
{
    public function ensureCompilerConfigured(): void
    {
        $this->resolveMetaEditorPath();
    }

    /**
     * @return array<int, string>
     */
    public function patternsForBroker(string $broker): array
    {
        return match (Str::lower($broker)) {
            'deriv' => ['BOOM', 'CRASH'],
            'weltrade' => ['GAINX', 'PAINX'],
            default => [],
        };
    }

    /**
     * @return array{content: string, fileName: string}
     */
    public function buildScanner(User $user, string $broker, string $pattern, string $accountId): array
    {
        $normalizedBroker = Str::lower($broker);
        $normalizedPattern = Str::upper($pattern);

        if (! in_array($normalizedPattern, $this->patternsForBroker($normalizedBroker), true)) {
            throw new RuntimeException('Patron no permitido para este broker.');
        }

        $expiresAt = $user->membership?->expires_at;
        if (! $expiresAt instanceof CarbonInterface) {
            throw new RuntimeException('No se encontro fecha de expiracion de membresia.');
        }

        $templatePath = sprintf('scanners/AET%s.mq5', $normalizedPattern);

        if (! Storage::disk('public')->exists($templatePath)) {
            throw new RuntimeException('No existe la plantilla del scanner solicitada.');
        }

        $template = Storage::disk('public')->get($templatePath);

        $sourceCode = $this->replaceLicenseValues(
            $template,
            $accountId,
            $expiresAt->format('Y.m.d')
        );

        $safeUserName = Str::of($user->name)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]/', '')
            ->value();

        if ($safeUserName === '') {
            $safeUserName = 'user'.$user->id;
        }

        $binaryContent = $this->compileToEx5($sourceCode, sprintf('AET%s_%s', $normalizedPattern, $safeUserName));

        return [
            'content' => $binaryContent,
            'fileName' => sprintf('AET%s_%s.ex5', $normalizedPattern, $safeUserName),
        ];
    }

    private function compileToEx5(string $sourceCode, string $baseFileName): string
    {
        $metaEditorPath = $this->resolveMetaEditorPath();

        $runtimePath = storage_path('app/scanners-runtime');
        if (! File::isDirectory($runtimePath)) {
            File::makeDirectory($runtimePath, 0755, true);
        }

        $uniqueBase = sprintf('%s_%s', $baseFileName, Str::uuid()->toString());
        $mq5Path = $runtimePath.DIRECTORY_SEPARATOR.$uniqueBase.'.mq5';
        $ex5Path = $runtimePath.DIRECTORY_SEPARATOR.$uniqueBase.'.ex5';
        $logPath = $runtimePath.DIRECTORY_SEPARATOR.$uniqueBase.'.log';
        $startedAt = microtime(true);

        File::put($mq5Path, $sourceCode);

        $process = new Process([
            $metaEditorPath,
            '/compile:'.$mq5Path,
            '/log:'.$logPath,
        ]);

        $process->setTimeout((int) config('scanners.compile_timeout_seconds', 60));
        $process->run();

        $logSnippet = File::exists($logPath) ? trim((string) File::get($logPath)) : '';
        $compileErrors = $this->extractCompileErrorLines($logSnippet);
        $resolvedEx5Path = File::exists($ex5Path)
            ? $ex5Path
            : $this->findCompiledEx5Path($uniqueBase, $startedAt);

        if ($compileErrors !== []) {
            $errorsText = implode(' | ', $compileErrors);

            if (str_contains(Str::lower($errorsText), 'n8n.mqh')) {
                throw new RuntimeException(__('messages.user.dashboard.scanner.compiler_dependencies_missing'));
            }

            throw new RuntimeException($errorsText !== ''
                ? __('messages.user.dashboard.scanner.compile_failed_with_log', ['log' => Str::limit($errorsText, 500)])
                : __('messages.user.dashboard.scanner.compile_failed'));
        }

        if ($resolvedEx5Path === null || ! File::exists($resolvedEx5Path)) {
            throw new RuntimeException($logSnippet !== ''
                ? __('messages.user.dashboard.scanner.compile_failed_with_log', ['log' => Str::limit($logSnippet, 500)])
                : __('messages.user.dashboard.scanner.compile_failed'));
        }

        $binaryContent = File::get($resolvedEx5Path);

        // Cleanup temp artifacts after successful compilation.
        File::delete([$mq5Path, $ex5Path, $resolvedEx5Path, $logPath]);

        return $binaryContent;
    }

    /**
     * @return array<int, string>
     */
    private function extractCompileErrorLines(string $logContent): array
    {
        if ($logContent === '') {
            return [];
        }

        $errors = [];
        $lines = preg_split('/\r\n|\r|\n/', $logContent) ?: [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/:\s*error\b/i', $trimmed) === 1) {
                $errors[] = $trimmed;
            }
        }

        if ($errors !== []) {
            return $errors;
        }

        if (preg_match('/Result:\s*(\d+)\s+errors?/i', $logContent, $matches) === 1) {
            return ((int) $matches[1]) > 0 ? [trim($matches[0])] : [];
        }

        return [];
    }

    private function hasCompileErrors(string $logContent): bool
    {
        if ($logContent === '') {
            return false;
        }

        if (preg_match('/Result:\s*(\d+)\s+errors?/i', $logContent, $matches) === 1) {
            return ((int) $matches[1]) > 0;
        }

        return str_contains(Str::lower($logContent), 'error');
    }

    private function findCompiledEx5Path(string $uniqueBase, float $startedAt): ?string
    {
        $appData = (string) getenv('APPDATA');
        if ($appData === '') {
            return null;
        }

        $terminalRoot = $appData.DIRECTORY_SEPARATOR.'MetaQuotes'.DIRECTORY_SEPARATOR.'Terminal';
        if (! File::isDirectory($terminalRoot)) {
            return null;
        }

        $targetName = $uniqueBase.'.ex5';

        /** @var array<int, string> $candidates */
        $candidates = collect(File::allFiles($terminalRoot))
            ->filter(fn ($file): bool => $file->getFilename() === $targetName)
            ->map(fn ($file): string => $file->getPathname())
            ->values()
            ->all();

        if ($candidates === []) {
            return null;
        }

        usort($candidates, static function (string $left, string $right): int {
            return filemtime($right) <=> filemtime($left);
        });

        foreach ($candidates as $candidate) {
            if (filemtime($candidate) >= (int) floor($startedAt) - 2) {
                return $candidate;
            }
        }

        return $candidates[0] ?? null;
    }

    private function resolveMetaEditorPath(): string
    {
        $configuredPath = (string) config('scanners.metaeditor_path', '');

        if ($configuredPath !== '' && File::exists($configuredPath)) {
            return $configuredPath;
        }

        $commonPaths = [
            'C:\\Program Files\\MetaTrader 5\\metaeditor64.exe',
            'C:\\Program Files (x86)\\MetaTrader 5\\metaeditor64.exe',
            'C:\\Program Files\\MetaTrader 5 Terminal\\MetaEditor64.exe',
            'C:\\Program Files\\MetaTrader 5 Terminal\\MetaEditor.exe',
        ];

        foreach ($commonPaths as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        throw new RuntimeException(__('messages.user.dashboard.scanner.compiler_not_configured'));
    }

    private function replaceLicenseValues(string $content, string $accountId, string $expirationDate): string
    {
        $content = preg_replace(
            "/input\\s+long\\s+CUENTA_AUTORIZADA\\s*=\\s*\\d+\\s*;/",
            'input long     CUENTA_AUTORIZADA ='.$accountId.';',
            $content,
            1,
            $accountReplaced
        );

        $content = preg_replace(
            "/input\\s+datetime\\s+FECHA_EXPIRACION\\s*=\\s*D'\\d{4}\\.\\d{2}\\.\\d{2}'\\s*;/",
            "input datetime FECHA_EXPIRACION  = D'{$expirationDate}';",
            $content,
            1,
            $dateReplaced
        );

        if (($accountReplaced ?? 0) === 0 || ($dateReplaced ?? 0) === 0) {
            throw new RuntimeException('No fue posible personalizar las variables de licencia en la plantilla.');
        }

        return $content;
    }
}
