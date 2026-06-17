<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportExcelRequest extends FormRequest
{
    private const ALLOWED_EXTENSIONS = ['xlsx', 'xls', 'xlsm', 'csv', 'txt'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:51200',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value instanceof \Illuminate\Http\UploadedFile) {
                        $fail('Debe seleccionar un archivo.');

                        return;
                    }

                    $extension = strtolower($value->getClientOriginalExtension());

                    if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                        $fail('El archivo debe ser Excel (.xlsx, .xls) o CSV. Extensión detectada: .'.$extension);
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Selecciona un archivo Excel o CSV.',
            'file.file' => 'El archivo no se subió correctamente. Intenta de nuevo.',
            'file.max' => 'El archivo no debe superar 50 MB.',
        ];
    }
}
