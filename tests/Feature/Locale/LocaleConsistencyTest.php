<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use App\Enums\Locales;

/*
|--------------------------------------------------------------------------
| Locale Consistency Test
|--------------------------------------------------------------------------
|
| Test ini memastikan:
| - Semua locale memiliki struktur key yang sama
| - Tidak ada key translation yang missing
| - Menampilkan key mana yang missing
|
| Support:
| - PHP translation files
| - Nested translation arrays
|
*/

describe('Locale Consistency', function () {
    // Helper: Flatten Translation Array
    function flattenTranslations(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {

            $fullKey = $prefix
                ? "{$prefix}.{$key}"
                : $key;

            if (is_array($value)) {
                $result += flattenTranslations($value, $fullKey);
            } else {
                $result[$fullKey] = $value;
            }
        }

        return $result;
    }

    // Helper: Get Translation Files
    function getTranslationFiles(string $localePath): array
    {
        return collect(File::files($localePath))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(fn ($file) => $file->getFilenameWithoutExtension())
            ->values()
            ->toArray();
    }

    it('has consistent translation keys across all locales', function () {
        $baseLocale = 'en';
        $locales = Locales::toArray();
        $langPath = lang_path();

        $baseLocalePath = "{$langPath}/{$baseLocale}";

        expect(File::exists($baseLocalePath))
            ->toBeTrue("Base locale directory [{$baseLocale}] does not exist.");

        $translationFiles = getTranslationFiles($baseLocalePath);

        $missingKeys = [];
        $missingFiles = [];

        foreach ($translationFiles as $file) {

            $baseTranslations = require "{$baseLocalePath}/{$file}.php";

            $baseKeys = array_keys(
                flattenTranslations($baseTranslations)
            );

            foreach ($locales as $locale) {

                if ($locale === $baseLocale) {
                    continue;
                }

                $localeFilePath = "{$langPath}/{$locale}/{$file}.php";

                if (! File::exists($localeFilePath)) {

                    $missingFiles[] = "[{$locale}] Missing file: {$file}.php";

                    continue;
                }

                $localeTranslations = require $localeFilePath;

                $localeKeys = array_keys(
                    flattenTranslations($localeTranslations)
                );

                $missing = array_diff($baseKeys, $localeKeys);

                foreach ($missing as $key) {

                    $missingKeys[] = "[{$locale}] Missing key in {$file}.php => {$key}";
                }
            }
        }

        $errors = array_merge($missingFiles, $missingKeys);

        expect($errors)
            ->toBe(
                [],
                "\n\nMissing translations detected:\n\n"
                . implode("\n", $errors)
            );
    });
});