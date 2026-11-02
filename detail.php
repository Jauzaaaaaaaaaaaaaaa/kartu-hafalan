<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/quran.php';
$exercises = load_json_file(__DIR__ . '/data/exercises.json');
$staticMaterials = load_json_file(__DIR__ . '/data/static_materials.json');
$slug = $_GET['id'] ?? '';
$exercise = find_exercise_by_slug($exercises, $slug);

function data_attr($value) {
    return h($value ?? '');
}

function data_attr_json($value) {
    return h(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}


function quran_external_audio_url($surahNumber) {
    return 'https://server8.mp3quran.net/afs/' . str_pad((string)(int)$surahNumber, 3, '0', STR_PAD_LEFT) . '.mp3';
}

function local_audio_url($path) {
    if (empty($path)) return '';
    return app_url($path);
}

function audio_player($src, $title = 'Audio Arab lengkap', $note = '') {
    if (empty($src)) return;
    echo '<section class="audio-panel card custom-audio-player">';
    echo '<h2>' . h($title) . '</h2>';
    if (!empty($note)) echo '<p class="audio-note">' . h($note) . '</p>';
    echo '<audio preload="metadata" src="' . h($src) . '"></audio>';
    echo '<div class="audio-buttons">';
    echo '<button class="mini-btn primary" type="button" data-audio-play>▶ Play</button>';
    echo '<button class="mini-btn" type="button" data-audio-pause>⏸ Pause</button>';
    echo '<button class="mini-btn stop" type="button" data-audio-stop>■ Stop</button>';
    echo '<button class="mini-btn" type="button" data-audio-back>↩ 10 detik</button>';
    echo '<button class="mini-btn" type="button" data-audio-forward>10 detik ↪</button>';
    echo '</div>';
    echo '<div class="seek-row"><span data-audio-current>00:00</span><input type="range" min="0" max="0" step="0.1" value="0" data-audio-range><span data-audio-duration>00:00</span></div>';
    echo '</section>';
}



function youtube_player($info) {
    $videoId = trim($info['video_id'] ?? '');
    if ($videoId === '') return;
    $label = $info['label'] ?? 'Audio YouTube';
    $note = $info['note'] ?? 'Audio memakai player YouTube resmi. Gunakan tombol dan slider waktu di bawah.';
    $source = $info['source'] ?? 'YouTube';
    $sourceUrl = $info['source_url'] ?? ('https://www.youtube.com/watch?v=' . $videoId);
    echo '<section class="audio-panel card youtube-player" data-youtube-id="' . h($videoId) . '">';
    echo '<h2>' . h($label) . '</h2>';
    echo '<p class="audio-note">' . h($note) . '</p>';
    echo '<div class="youtube-frame-wrap"><div class="youtube-frame" data-youtube-frame></div></div>';
    echo '<div class="audio-buttons">';
    echo '<button class="mini-btn primary" type="button" data-youtube-play>▶ Play</button>';
    echo '<button class="mini-btn" type="button" data-youtube-pause>⏸ Pause</button>';
    echo '<button class="mini-btn stop" type="button" data-youtube-stop>■ Stop</button>';
    echo '<button class="mini-btn" type="button" data-youtube-back>↩ 10 detik</button>';
    echo '<button class="mini-btn" type="button" data-youtube-forward>10 detik ↪</button>';
    echo '</div>';
    echo '<div class="seek-row"><span data-youtube-current>00:00</span><input type="range" min="0" max="0" step="0.1" value="0" data-youtube-range><span data-youtube-duration>00:00</span></div>';
    echo '<p class="source-line">Status: <span data-youtube-status>Memuat YouTube...</span> • Sumber: <a href="' . h($sourceUrl) . '" target="_blank" rel="noopener">' . h($source) . '</a></p>';
    echo '</section>';
}

function audio_playlist($sources, $title = 'Audio Arab lengkap', $note = '') {
    $sources = array_values(array_filter($sources));
    if (empty($sources)) return;
    echo '<section class="audio-panel card custom-audio-player" data-playlist="' . data_attr_json($sources) . '">';
    echo '<h2>' . h($title) . '</h2>';
    if (!empty($note)) echo '<p class="audio-note">' . h($note) . '</p>';
    echo '<audio preload="metadata" src="' . h($sources[0]) . '"></audio>';
    echo '<div class="track-info">Bagian <span data-track-current>1</span> dari <span data-track-total>' . count($sources) . '</span></div>';
    echo '<div class="audio-buttons">';
    echo '<button class="mini-btn primary" type="button" data-audio-play>▶ Play</button>';
    echo '<button class="mini-btn" type="button" data-audio-pause>⏸ Pause</button>';
    echo '<button class="mini-btn stop" type="button" data-audio-stop>■ Stop</button>';
    echo '<button class="mini-btn" type="button" data-audio-prev>⏮ Sebelumnya</button>';
    echo '<button class="mini-btn" type="button" data-audio-next>Berikutnya ⏭</button>';
    echo '<button class="mini-btn" type="button" data-audio-back>↩ 10 detik</button>';
    echo '<button class="mini-btn" type="button" data-audio-forward>10 detik ↪</button>';
    echo '</div>';
    echo '<div class="seek-row"><span data-audio-current>00:00</span><input type="range" min="0" max="0" step="0.1" value="0" data-audio-range><span data-audio-duration>00:00</span></div>';
    echo '</section>';
}

function render_speech_controls($arabicText, $label = 'Putar teks Arab') {
    $arabicText = trim($arabicText ?? '');
    if ($arabicText === '') return;
    echo '<section class="audio-panel card tts-player" data-tts-text="' . h($arabicText) . '">';
    echo '<h2>Audio Arab</h2>';
    echo '<p class="audio-note">' . h($label) . '. Gunakan slider untuk memilih titik mulai bacaan. Untuk audio MP3, slider mengikuti detik asli; untuk suara browser, slider mengikuti bagian bacaan.</p>';
    echo '<div class="audio-buttons">';
    echo '<button class="mini-btn primary" type="button" data-tts-play>▶ Play</button>';
    echo '<button class="mini-btn" type="button" data-tts-pause>⏸ Pause</button>';
    echo '<button class="mini-btn" type="button" data-tts-resume>▶ Lanjut</button>';
    echo '<button class="mini-btn stop" type="button" data-tts-stop>■ Stop</button>';
    echo '<button class="mini-btn" type="button" data-tts-back>↩ Mundur</button>';
    echo '<button class="mini-btn" type="button" data-tts-forward>Maju ↪</button>';
    echo '</div>';
    echo '<div class="seek-row tts-seek-row"><span data-tts-current>Bagian 1</span><input type="range" min="0" max="0" step="1" value="0" data-tts-range><span data-tts-total>Bagian 1</span></div>';
    echo '</section>';
}

function render_illustrations($material) {
    $items = $material['illustrations'] ?? [];
    if (empty($items)) return;
    echo '<section class="visual-grid">';
    foreach ($items as $item) {
        echo '<article class="visual-card card">';
        echo '<img src="' . h(app_url($item['src'])) . '" alt="' . h($item['title'] ?? 'Ilustrasi') . '">';
        echo '<div class="visual-content">';
        echo '<h3>' . h($item['title'] ?? 'Ilustrasi') . '</h3>';
        if (!empty($item['caption'])) echo '<p>' . h($item['caption']) . '</p>';
        echo '</div></article>';
    }
    echo '</section>';
}

function render_quran_full($surah, $exercise) {
    $ayahs = $surah['ayat'] ?? [];
    $arabicParts = [];
    $latinParts = [];
    $translationParts = [];
    foreach ($ayahs as $ayah) {
        $n = $ayah['nomorAyat'] ?? '';
        if (!empty($ayah['teksArab'])) $arabicParts[] = $ayah['teksArab'] . ' ﴿' . $n . '﴾';
        if (!empty($ayah['teksLatin'])) $latinParts[] = $n . '. ' . $ayah['teksLatin'];
        if (!empty($ayah['teksIndonesia'])) $translationParts[] = $n . '. ' . $ayah['teksIndonesia'];
    }
    $arabicText = implode("\n", $arabicParts);
    $latinText = implode("\n", $latinParts);
    $translationText = implode("\n", $translationParts);

    $localAudio = $exercise['local_audio'] ?? '';
    if (!empty($exercise['youtube_audio']['video_id'])) {
        youtube_player($exercise['youtube_audio']);
    } elseif (!empty($localAudio)) {
        audio_player(local_audio_url($localAudio), 'Audio Arab lokal', 'Audio ini sudah disimpan di dalam folder website, jadi tidak bergantung pada suara browser. Slider mengikuti detik asli audio.');
    } elseif (($exercise['type'] ?? '') === 'quran_surah') {
        audio_player(quran_external_audio_url($exercise['surah']), 'Audio surat penuh', 'Audio surat lengkap dari sumber MP3 publik. Gunakan slider untuk memilih detik tertentu, pause, stop, maju, atau mundur 10 detik.');
    } else {
        $rangeAudios = [];
        foreach ($ayahs as $ayah) {
            $rangeAudios[] = $ayah['audio']['05'] ?? ($ayah['audio']['01'] ?? '');
        }
        $rangeAudios = array_values(array_filter($rangeAudios));
        if (count($rangeAudios) === 1) {
            audio_player($rangeAudios[0], 'Audio Arab ayat pilihan', 'Gunakan slider untuk memilih detik tertentu, pause, stop, maju, atau mundur 10 detik.');
        } elseif (count($rangeAudios) > 1) {
            audio_playlist($rangeAudios, 'Audio Arab ayat pilihan lengkap', 'Player ini memutar semua ayat pilihan secara berurutan. Slider mengatur detik pada bagian yang sedang diputar.');
        } else {
            render_speech_controls($arabicText, 'Putar Arab lengkap');
        }
    }

    echo '<article class="ayah-card card full-text-card">';
    echo '<div class="ayah-meta">Teks lengkap</div>';
    echo '<div class="arabic" dir="rtl">' . nl2br(h($arabicText)) . '</div>';
    if (!empty($latinText)) echo '<details class="text-details"><summary>Latin</summary><div class="latin">' . nl2br(h($latinText)) . '</div></details>';
    if (!empty($translationText)) echo '<details class="text-details" open><summary>Terjemahan Indonesia</summary><div class="translation">' . nl2br(h($translationText)) . '</div></details>';
    echo '</article>';
}

function collect_material_texts($material) {
    $arabic = [];
    $latin = [];
    $translation = [];
    foreach (($material['sections'] ?? []) as $section) {
        foreach (($section['items'] ?? []) as $item) {
            if (!empty($item['arabic'])) $arabic[] = $item['arabic'];
            if (!empty($item['latin'])) $latin[] = ($item['label'] ?? '') . ': ' . $item['latin'];
            if (!empty($item['translation'])) $translation[] = ($item['label'] ?? '') . ': ' . $item['translation'];
        }
    }
    return [implode("\n\n", $arabic), implode("\n\n", $latin), implode("\n\n", $translation)];
}

function render_static_material($material) {
    if (!$material) {
        echo '<div class="card empty">Materi belum tersedia. Silakan edit data/static_materials.json.</div>';
        return;
    }
    if (!empty($material['intro'])) echo '<p class="lead">' . h($material['intro']) . '</p>';

    render_illustrations($material);
    [$arabicFull, $latinFull, $translationFull] = collect_material_texts($material);

    if (!empty($material['youtube_audio']['video_id'])) {
        youtube_player($material['youtube_audio']);
    } elseif (!empty($material['local_audio'])) {
        audio_player(local_audio_url($material['local_audio']), 'Audio Arab lokal', $material['audio_note'] ?? 'Audio lokal dari teks Arab halaman ini.');
    } elseif (!empty($material['external_audio']['url'])) {
        audio_player($material['external_audio']['url'], $material['external_audio']['label'] ?? 'Audio Arab eksternal', $material['external_audio']['note'] ?? 'Audio eksternal langsung dari sumber publik.');
    } else {
        render_speech_controls($arabicFull, 'Putar semua teks Arab');
    }

    echo '<article class="ayah-card card full-text-card">';
    echo '<div class="ayah-meta">Materi lengkap dalam satu halaman</div>';
    if (!empty($arabicFull)) echo '<div class="arabic" dir="rtl">' . nl2br(h($arabicFull)) . '</div>';
    if (!empty($latinFull)) echo '<details class="text-details"><summary>Latin</summary><div class="latin">' . nl2br(h($latinFull)) . '</div></details>';
    if (!empty($translationFull)) echo '<details class="text-details" open><summary>Terjemahan Indonesia</summary><div class="translation">' . nl2br(h($translationFull)) . '</div></details>';
    echo '</article>';

    foreach (($material['sections'] ?? []) as $section) {
        if (!empty($section['bullets'])) {
            echo '<section class="material-section"><h2>' . h($section['heading'] ?? 'Checklist') . '</h2><ul class="checklist">';
            foreach ($section['bullets'] as $bullet) echo '<li>' . h($bullet) . '</li>';
            echo '</ul></section>';
        }
    }
}

if (!$exercise) {
    render_header('Materi tidak ditemukan');
    echo '<div class="card empty"><h1>Materi tidak ditemukan</h1><p>Silakan kembali ke beranda.</p><a class="btn primary" href="' . h(app_url('index.php')) . '">Kembali</a></div>';
    render_footer();
    exit;
}

render_header($exercise['title']);
?>
<a class="back-link" href="<?= h(app_url('index.php')) ?>">← Kembali ke daftar materi</a>
<section class="detail-hero card">
  <span class="badge">Latihan #<?= h($exercise['no']) ?> • <?= h($exercise['category']) ?></span>
  <h1><?= h($exercise['title']) ?></h1>
  <p>Audio untuk doa dan materi non-surat sekarang memakai sumber YouTube pilihan, bukan suara sintetis. Surat Al-Qur'an tetap memakai audio surat penuh, dan tombol Play/Pause/Stop/slider tetap tersedia.</p>
</section>

<?php
$type = $exercise['type'] ?? 'static';
if ($type === 'quran_surah') {
    $surah = quran_get_surah($exercise['surah']);
    if (!empty($surah['_error'])) {
        audio_player(quran_external_audio_url($exercise['surah']), 'Audio surat penuh', 'Data teks belum bisa diambil, tetapi audio surat tetap bisa diputar langsung dari sumber MP3 publik.');
        echo '<div class="card alert">' . h($surah['_message']) . '</div>';
    } else {
        echo '<section class="surah-info card">';
        echo '<h2>Surah ' . h($surah['namaLatin'] ?? $exercise['title']) . ' <span dir="rtl">' . h($surah['nama'] ?? '') . '</span></h2>';
        echo '<p><strong>Arti:</strong> ' . h($surah['arti'] ?? '-') . ' • <strong>Jumlah ayat:</strong> ' . h($surah['jumlahAyat'] ?? count($surah['ayat'] ?? [])) . ' • <strong>Tempat turun:</strong> ' . h($surah['tempatTurun'] ?? '-') . '</p>';
        echo '</section>';
        render_quran_full($surah, $exercise);
    }
} elseif ($type === 'quran_range') {
    $surah = quran_get_verse_range($exercise['surah'], $exercise['verse_start'], $exercise['verse_end']);
    if (!empty($surah['_error'])) {
        if (!empty($exercise['youtube_audio']['video_id'])) {
            youtube_player($exercise['youtube_audio']);
        } elseif (!empty($exercise['local_audio'])) {
            audio_player(local_audio_url($exercise['local_audio']), 'Audio Arab lokal', 'Data teks belum bisa diambil, tetapi audio lokal tetap bisa diputar.');
        }
        echo '<div class="card alert">' . h($surah['_message']) . '</div>';
        if (!empty($exercise['fallback_arabic'])) {
            echo '<article class="ayah-card card full-text-card"><div class="ayah-meta">Teks Arab cadangan</div><div class="arabic" dir="rtl">' . nl2br(h($exercise['fallback_arabic'])) . '</div></article>';
        }
    } else {
        echo '<section class="surah-info card">';
        echo '<h2>QS. ' . h($surah['namaLatin'] ?? '') . ' (' . h($exercise['surah']) . ':' . h($exercise['verse_start']);
        if ($exercise['verse_start'] !== $exercise['verse_end']) echo '-' . h($exercise['verse_end']);
        echo ')</h2><p><strong>Arti surah:</strong> ' . h($surah['arti'] ?? '-') . '</p></section>';
        render_quran_full($surah, $exercise);
    }
} else {
    render_static_material($staticMaterials[$slug] ?? null);
}
?>

<section class="practice-box card">
  <h2>Catatan latihan</h2>
  <textarea placeholder="Tulis catatan hafalan, bagian yang belum lancar, atau nilai latihan di sini..."></textarea>
</section>
<?php render_footer(); ?>
