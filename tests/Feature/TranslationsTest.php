<?php

namespace Tests\Feature;

use Tests\TestCase;

class TranslationsTest extends TestCase
{
    /**
     * On case-insensitive filesystems (macOS), a key like "Site" missing from the JSON file
     * falls through to lang/{locale}/site.php and returns an array. Every UI key must
     * therefore exist in every locale's JSON file.
     */
    public function test_every_ui_key_exists_in_every_locale_json(): void
    {
        $locales = array_keys(config('indexa.locales'));
        $keysByLocale = [];
        foreach ($locales as $locale) {
            $keysByLocale[$locale] = array_keys(json_decode(file_get_contents(lang_path("$locale.json")), true));
        }

        foreach ($locales as $locale) {
            foreach ($locales as $other) {
                $this->assertSame([], array_values(array_diff($keysByLocale[$other], $keysByLocale[$locale])), "$locale.json is missing keys present in $other.json");
            }
        }
    }

    public function test_no_json_key_collides_with_a_translation_group_file(): void
    {
        $groups = array_map(fn ($f) => strtolower(basename($f, '.php')), glob(lang_path('fr/*.php')));

        foreach (array_keys(json_decode(file_get_contents(lang_path('fr.json')), true)) as $key) {
            app()->setLocale('fr');
            $this->assertIsString(__($key), "__('$key') must return a string");
            $this->assertNotContains(strtolower($key), $groups, "Key '$key' has the name of a group file");
        }
    }
}
