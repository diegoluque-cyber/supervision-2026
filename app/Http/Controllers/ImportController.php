<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportExcelRequest;
use App\Services\ExcelImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ImportController extends Controller
{
    public function __construct(
        private ExcelImportService $importService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Import/Index');
    }

    public function preview(ImportExcelRequest $request): RedirectResponse
    {
        $path = $request->file('file')->store('imports');
        $preview = $this->importService->preview(Storage::path($path));

        return back()->with([
            'importPreview' => $preview,
            'importPath' => $path,
            'importFileName' => $request->file('file')->getClientOriginalName(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'import_path' => ['required', 'string'],
            'replace' => ['boolean'],
        ]);

        $fullPath = Storage::path($request->string('import_path'));

        if (! file_exists($fullPath)) {
            return back()->withErrors(['file' => 'No se encontró el archivo. Vuelve a subirlo.']);
        }

        $result = $this->importService->import($fullPath, $request->boolean('replace'));

        return redirect()
            ->route('conductores.index')
            ->with('importResult', $result);
    }
}
