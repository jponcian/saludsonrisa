<?php
/**
 * Utilidades para normalizar y estandarizar roles del sistema.
 */

function normalizar_texto(string $texto): string
{
    $texto = trim($texto);
    if ($texto === '') {
        return '';
    }

    $original = $texto;
    $transliterado = @iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
    if ($transliterado !== false && $transliterado !== null) {
        $texto = $transliterado;
    } else {
        $texto = $original;
    }

    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '_', $texto);
    return trim($texto, '_');
}

function rol_to_slug(string $valor): string
{
    $slug = normalizar_texto($valor);
    return $slug !== '' ? $slug : 'rol';
}

function rol_from_slug(string $slug): string
{
    $slug_normalizado = trim(strtolower($slug));
    if ($slug_normalizado === '') {
        return '';
    }

    $texto = str_replace('_', ' ', $slug_normalizado);
    return mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
}
