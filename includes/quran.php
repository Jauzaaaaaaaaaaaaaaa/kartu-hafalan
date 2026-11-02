<?php
require_once __DIR__ . '/helpers.php';

function quran_cache_path($key) {
    $safe = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $key);
    return __DIR__ . '/../cache/' . $safe . '.json';
}

function quran_fetch_json($url, $cacheKey) {
    $cachePath = quran_cache_path($cacheKey);
    $cacheDir = dirname($cachePath);
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }

    if (file_exists($cachePath) && filesize($cachePath) > 50) {
        $cached = json_decode(file_get_contents($cachePath), true);
        if (is_array($cached)) {
            $cached['_from_cache'] = true;
            return $cached;
        }
    }

    $raw = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Hafalan-Islam-XAMPP/1.0'
        ]);
        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode < 200 || $httpCode >= 300) {
            $raw = false;
        }
    } else {
        $context = stream_context_create([
            'http' => ['timeout' => 20, 'header' => "User-Agent: Hafalan-Islam-XAMPP/1.0\r\n"],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        $raw = @file_get_contents($url, false, $context);
    }

    if ($raw !== false && strlen($raw) > 20) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            file_put_contents($cachePath, $raw);
            $decoded['_from_cache'] = false;
            return $decoded;
        }
    }

    return [
        '_error' => true,
        '_message' => 'Data Al-Qur\'an belum bisa diambil. Pastikan internet aktif saat pertama kali membuka halaman ini, lalu refresh.'
    ];
}

function quran_get_surah($number) {
    $number = (int)$number;
    $response = quran_fetch_json('https://equran.id/api/v2/surat/' . $number, 'surah_' . $number);
    if (isset($response['data']) && is_array($response['data'])) {
        return $response['data'];
    }
    return $response;
}

function quran_get_verse_range($surahNumber, $start, $end) {
    $surah = quran_get_surah($surahNumber);
    if (!isset($surah['ayat']) || !is_array($surah['ayat'])) {
        return $surah;
    }
    $ayahs = array_values(array_filter($surah['ayat'], function($ayah) use ($start, $end) {
        $n = (int)($ayah['nomorAyat'] ?? 0);
        return $n >= (int)$start && $n <= (int)$end;
    }));
    $surah['ayat'] = $ayahs;
    $surah['jumlahAyatDitampilkan'] = count($ayahs);
    return $surah;
}
?>
