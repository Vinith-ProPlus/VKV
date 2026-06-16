<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class AllowedUpload implements ValidationRule
{
    public function __construct(
        private readonly array $extensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'],
        private readonly int $maxKilobytes = 2048,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('The :attribute must be a valid uploaded file.');

            return;
        }

        if (!$value->isValid()) {
            $fail('The :attribute upload failed.');

            return;
        }

        if ($value->getSize() > ($this->maxKilobytes * 1024)) {
            $fail("The :attribute must not be greater than {$this->maxKilobytes} kilobytes.");

            return;
        }

        $extension = strtolower($value->getClientOriginalExtension() ?: '');

        if (!in_array($extension, $this->extensions, true)) {
            $fail('The :attribute must be a file of type: ' . implode(', ', $this->extensions) . '.');
        }
    }
}
