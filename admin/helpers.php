
<?php

if (!function_exists('adminEscape')) {
    
function adminEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

}
