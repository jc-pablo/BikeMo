<?php
/* Shared PHP helper; current front-end logic remains unchanged. */
function bikemo_escape($value) { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
