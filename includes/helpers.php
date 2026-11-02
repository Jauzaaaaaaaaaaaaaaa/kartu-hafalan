<?php
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function load_json_file($path, $fallback = []) {
    if (!file_exists($path)) {
        return $fallback;
    }
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    return is_array($data) ? $data : $fallback;
}

function find_exercise_by_slug($exercises, $slug) {
    foreach ($exercises as $exercise) {
        if (($exercise['slug'] ?? '') === $slug) {
            return $exercise;
        }
    }
    return null;
}

function text_contains($haystack, $needle) {
    $haystack = strtolower((string)$haystack);
    $needle = strtolower((string)$needle);
    return $needle === '' || strpos($haystack, $needle) !== false;
}

function app_url($path = '') {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    if ($base === '/' || $base === '\\') {
        $base = '';
    }
    return $base . '/' . ltrim($path, '/');
}
?>
