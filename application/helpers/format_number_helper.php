<?php
if (!function_exists('format_number')) {
    function format_number($input) {
        if ($input === null || $input === '') {
            return '';
        }
        $numeric_value = str_replace(',', '', $input);
        return number_format($numeric_value, 0, '.', '.');
    }
}
