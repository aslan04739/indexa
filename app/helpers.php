<?php

if (! function_exists('num')) {
    /**
     * Format an integer with narrow no-break spaces as thousands separators, wrapped in
     * Unicode left-to-right isolate marks so it reads correctly inside Arabic (RTL) text.
     */
    function num(int|float|null $value): string
    {
        return "\u{2066}".number_format((int) $value, 0, ',', "\u{202F}")."\u{2069}";
    }
}

if (! function_exists('dzd')) {
    /** Format an amount of Algerian dinars: 12 500 DZD, kept in one left-to-right run. */
    function dzd(int|float|null $amount): string
    {
        return "\u{2066}".number_format((int) $amount, 0, ',', "\u{202F}")."\u{00A0}DZD\u{2069}";
    }
}
