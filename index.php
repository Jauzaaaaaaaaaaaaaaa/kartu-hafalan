<?php
require_once __DIR__ . '/includes/layout.php';
$exercises = load_json_file(__DIR__ . '/data/exercises.json');
$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$categories = array_values(array_unique(array_map(fn($e) => $e['category'] ?? 'Lainnya', $exercises)));
$filtered = array_filter($exercises, function($exercise) use ($q, $category) {
    $matchText = $q === '' || text_contains(($exercise['title'] ?? '') . ' ' . ($exercise['category'] ?? ''), $q);
    $matchCategory = $category === '' || ($exercise['category'] ?? '') === $category;
    return $matchText && $matchCategory;
});
render_header('Website Hafalan Islam');
?>
<section class="hero">
  <div>
    <p class="eyebrow">Arab • Latin • Terjemahan Indonesia • Audio Arab</p>
    <h1>Website Hafalan Islam</h1>
    <p>Berisi daftar latihan dari kartu penilaian: surat pendek, Ayat Kursi, doa, bacaan shalat, shalat jenazah, doa harian, membaca Al-Qur'an, dan latihan menulis surat pendek. Semua detail materi sekarang fokus ke suara Arab, bacaan panjang diputar lengkap, dan materi gerakan shalat diberi gambar ilustrasi.</p>
    <div class="hero-actions">
      <a class="btn primary" href="<?= h(app_url('score.php')) ?>">Buka Kartu Penilaian</a>
      <a class="btn" href="#materi">Lihat Materi</a>
    </div>
  </div>
  <div class="hero-card">
    <span class="big-number"><?= count($exercises) ?></span>
    <span>Latihan</span>
    <small>Siap dibuka di XAMPP tanpa database</small>
  </div>
</section>

<section id="materi" class="toolbar card">
  <form method="get" class="filter-form">
    <input type="search" name="q" value="<?= h($q) ?>" placeholder="Cari: Al-Ikhlas, doa, shalat...">
    <select name="category">
      <option value="">Semua kategori</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= h($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= h($cat) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn primary" type="submit">Cari</button>
    <?php if ($q !== '' || $category !== ''): ?>
      <a class="btn" href="<?= h(app_url('index.php')) ?>">Reset</a>
    <?php endif; ?>
  </form>
</section>

<section class="grid">
  <?php foreach ($filtered as $exercise): ?>
    <article class="exercise-card card">
      <div class="card-top">
        <span class="number">#<?= h($exercise['no']) ?></span>
        <span class="badge"><?= h($exercise['category']) ?></span>
      </div>
      <h2><?= h($exercise['title']) ?></h2>
      <p>
        <?php if (($exercise['type'] ?? '') === 'quran_surah'): ?>
          Surat nomor <?= h($exercise['surah']) ?>. Buka untuk Arab, latin, terjemahan Indonesia, audio Arab penuh, tombol pause, dan slider waktu.
        <?php elseif (($exercise['type'] ?? '') === 'quran_range'): ?>
          QS. <?= h($exercise['surah']) ?>:<?= h($exercise['verse_start']) ?><?= $exercise['verse_start'] !== $exercise['verse_end'] ? '-' . h($exercise['verse_end']) : '' ?> dengan suara Arab lengkap.
        <?php else: ?>
          Materi statis lengkap dengan teks Arab, latin, terjemahan Indonesia, dan suara Arab.
        <?php endif; ?>
      </p>
      <a class="read-more" href="<?= h(app_url('detail.php?id=' . urlencode($exercise['slug']))) ?>">Buka latihan →</a>
    </article>
  <?php endforeach; ?>
</section>
<?php if (empty($filtered)): ?>
  <div class="empty card">Tidak ada materi yang cocok. Coba kata kunci lain.</div>
<?php endif; ?>
<?php render_footer(); ?>
