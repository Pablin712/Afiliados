<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('module:scaffold-view {module : Module slug in plural snake_case (e.g. membership_types)} {--model= : Model class name (e.g. MembershipType)} {--force : Overwrite existing files}', function () {
    $module = Str::of((string) $this->argument('module'))->trim()->lower()->replace('-', '_')->toString();

    if (! preg_match('/^[a-z][a-z0-9_]*$/', $module)) {
        $this->error('Invalid module name. Use plural snake_case, for example: membership_types');
        return;
    }

    $model = trim((string) $this->option('model'));
    $model = $model !== ''
        ? Str::studly($model)
        : Str::studly(Str::singular($module));

    $moduleStudly = Str::studly($module);
    $moduleTitle = Str::headline($module);
    $controllerClass = $moduleStudly.'Controller';

    $indexRequestClass = 'Index'.$moduleStudly.'Request';
    $storeRequestClass = 'Store'.$moduleStudly.'Request';
    $updateRequestClass = 'Update'.$moduleStudly.'Request';

    $requestNamespace = 'App\\Http\\Requests\\'.$moduleStudly;

    $controllerPath = app_path('Http/Controllers/'.$controllerClass.'.php');
    $requestDir = app_path('Http/Requests/'.$moduleStudly);

    $viewDir = resource_path('views/'.$module);
    $partialDir = $viewDir.'/partials';
    $exportsDir = $viewDir.'/exports';

    $langEnPath = resource_path('lang/en/'.$module.'.php');
    $langEsPath = resource_path('lang/es/'.$module.'.php');

    File::ensureDirectoryExists(dirname($controllerPath));
    File::ensureDirectoryExists($requestDir);
    File::ensureDirectoryExists($partialDir);
    File::ensureDirectoryExists($exportsDir);
    File::ensureDirectoryExists(dirname($langEnPath));
    File::ensureDirectoryExists(dirname($langEsPath));

    $writeFile = function (string $path, string $content) {
        if (File::exists($path) && ! $this->option('force')) {
            $this->warn('Skip existing: '.$path);
            return;
        }

        File::put($path, $content);
        $this->info('Created: '.$path);
    };

    $controllerContent = <<<PHP
<?php

namespace App\\Http\\Controllers;

use App\\Http\\Requests\\{$moduleStudly}\\{$indexRequestClass};
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;
use Illuminate\\View\\View;

class {$controllerClass} extends Controller
{
    public function index({$indexRequestClass} \$request): View|JsonResponse
    {
        // TODO: Build module query using validated filters from request.
        \$records = collect();
        \$totalRecords = 0;

        if (\$request->boolean('ajax')) {
            return response()->json([
                'html' => view('{$module}.partials.table-rows', ['records' => \$records])->render(),
                'total_records' => \$totalRecords,
                'current_page' => (int) \$request->input('page', 1),
                'per_page' => (int) \$request->input('per_page', 10),
            ]);
        }

        \$canReport = \$request->user()?->can('report {$module}') ?? false;

        return view('{$module}.index', [
            'records' => \$records,
            'totalRecords' => \$totalRecords,
            'canReport' => \$canReport,
        ]);
    }

    public function store(Request \$request)
    {
        // TODO: Use {$storeRequestClass} and implement create flow.
    }

    public function update(Request \$request, int \$id)
    {
        // TODO: Use {$updateRequestClass} and implement update flow.
    }

    public function destroy(int \$id)
    {
        // TODO: Implement delete flow with permission delete {$module}.
    }
}
PHP;

    $indexRequestContent = <<<PHP
<?php

namespace {$requestNamespace};

use Illuminate\\Foundation\\Http\\FormRequest;

class {$indexRequestClass} extends FormRequest
{
    public function authorize(): bool
    {
        return \$this->user()?->can('view {$module}') ?? false;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'search' => ['nullable', 'string', 'max:150'],
            'sort_by' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'in:asc,desc'],
            'ajax' => ['nullable', 'boolean'],
            'export' => ['nullable', 'in:csv,excel,json,pdf'],
        ];
    }
}
PHP;

    $storeRequestContent = <<<PHP
<?php

namespace {$requestNamespace};

use Illuminate\\Foundation\\Http\\FormRequest;

class {$storeRequestClass} extends FormRequest
{
    public function authorize(): bool
    {
        return \$this->user()?->can('create {$module}') ?? false;
    }

    public function rules(): array
    {
        return [
            // TODO: Define create validation rules for {$moduleTitle}.
        ];
    }
}
PHP;

    $updateRequestContent = <<<PHP
<?php

namespace {$requestNamespace};

use Illuminate\\Foundation\\Http\\FormRequest;

class {$updateRequestClass} extends FormRequest
{
    public function authorize(): bool
    {
        return \$this->user()?->can('edit {$module}') ?? false;
    }

    public function rules(): array
    {
        return [
            // TODO: Define update validation rules for {$moduleTitle}.
        ];
    }
}
PHP;

    $indexViewContent = <<<BLADE
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('{$module}.title') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-enhanced-table
                id="{$module}-table"
                :headers="[
                    ['label' => __('{$module}.columns.id'), 'type' => 'number', 'sort_by' => 'id'],
                    ['label' => __('{$module}.columns.name'), 'type' => 'string', 'sort_by' => 'name'],
                    ['label' => __('{$module}.columns.created_at'), 'type' => 'string', 'sort_by' => 'created_at'],
                ]"
                :serverSide="true"
                :totalRecords="\$totalRecords"
                :searchUrl="route('{$module}.index')"
                :csv="\$canReport"
                :excel="\$canReport"
                :json="\$canReport"
                :pdf="\$canReport"
                :print="true"
                :table_void="\$records->isEmpty()"
            >
                <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                    @include('{$module}.partials.table-rows', ['records' => \$records])
                </tbody>
            </x-enhanced-table>
        </div>
    </div>
</x-app-layout>
BLADE;

    $rowsViewContent = <<<BLADE
@forelse (\$records as \$record)
    <tr class="hover:bg-gray-50 dark:hover:bg-graphite-800/60 transition-colors duration-150">
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ \$record->id ?? '-' }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ \$record->name ?? '-' }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-graphite-400">{{ optional(\$record->created_at)->format('Y-m-d H:i:s') }}</td>
    </tr>
@empty
    <tr>
        <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('{$module}.messages.empty') }}
        </td>
    </tr>
@endforelse
BLADE;

    $pdfViewContent = <<<BLADE
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('{$module}.title') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px; }
        th { background: #eef2ff; text-transform: uppercase; font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ __('{$module}.title') }}</h1>
    <p>{{ __('{$module}.messages.report_generated_at') }}: {{ now()->format('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('{$module}.columns.id') }}</th>
                <th>{{ __('{$module}.columns.name') }}</th>
                <th>{{ __('{$module}.columns.created_at') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach(\$records as \$record)
            <tr>
                <td>{{ \$record->id ?? '-' }}</td>
                <td>{{ \$record->name ?? '-' }}</td>
                <td>{{ optional(\$record->created_at)->format('Y-m-d H:i:s') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
BLADE;

    $langEnContent = <<<PHP
<?php

return [
    'title' => '{$moduleTitle}',
    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'created_at' => 'Created At',
    ],
    'messages' => [
        'empty' => 'No records available.',
        'report_generated_at' => 'Generated at',
    ],
];
PHP;

    $langEsContent = <<<PHP
<?php

return [
    'title' => '{$moduleTitle}',
    'columns' => [
        'id' => 'ID',
        'name' => 'Nombre',
        'created_at' => 'Creado En',
    ],
    'messages' => [
        'empty' => 'No hay registros disponibles.',
        'report_generated_at' => 'Generado en',
    ],
];
PHP;

    $writeFile($controllerPath, $controllerContent);
    $writeFile($requestDir.'/'.$indexRequestClass.'.php', $indexRequestContent);
    $writeFile($requestDir.'/'.$storeRequestClass.'.php', $storeRequestContent);
    $writeFile($requestDir.'/'.$updateRequestClass.'.php', $updateRequestContent);

    $writeFile($viewDir.'/index.blade.php', $indexViewContent);
    $writeFile($partialDir.'/table-rows.blade.php', $rowsViewContent);
    $writeFile($exportsDir.'/pdf.blade.php', $pdfViewContent);

    $writeFile($langEnPath, $langEnContent);
    $writeFile($langEsPath, $langEsContent);

    $this->newLine();
    $this->info('Scaffold ready for module: '.$module);
    $this->line('Next steps:');
    $this->line('1) Add routes in routes/web.php with middleware permission:view '.$module);
    $this->line('2) Add nav/dropdown links in resources/views/layouts/navigation.blade.php using @can');
    $this->line('3) Implement query/export logic in '.$controllerClass.' with model '.$model);
    $this->line('4) Implement create/edit/delete with x-modal partials (no modals inside foreach).');
    $this->line('5) Use reusable icon action buttons in table rows and open modals by entity id.');
    $this->line('6) Add permission seeds for '.$module.' if missing.');
})->purpose('Generate base scaffold for a module view (controller, requests, views, translations)');
