<?php
require_once __DIR__ . '/helpers.php';
function render_header($title = 'Latihan Hafalan Islam') {
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?></title>
  <link rel="stylesheet" href="<?= h(app_url('assets/style.css')) ?>">
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Mulish:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= h(app_url('atelier/atelier.css')) ?>">
<link rel="stylesheet" href="<?= h(app_url('atelier/theme.css')) ?>">
<script src="<?= h(app_url('atelier/theme.js')) ?>"></script>
<script src="<?= h(app_url('atelier/atelier.js')) ?>"></script>
</head>
<body>
  <header class="site-header">
    <div class="container nav-wrap">
      <a class="brand" href="<?= h(app_url('index.php')) ?>">Kartu Hafalan</a>
      <nav class="nav">
        <a href="<?= h(app_url('index.php')) ?>">Materi</a>
        <a href="<?= h(app_url('score.php')) ?>">Kartu Penilaian</a>
        <a href="<?= h(app_url('README.md')) ?>">Panduan</a>
      </nav>
    </div>
  </header>
  <main class="container page">
<?php
}

function render_footer() {
?>
  </main>
  <footer class="site-footer">
    <div class="container">
      <p>Website latihan hafalan sederhana untuk XAMPP. Tema biru, audio Arab, dan bagian Doa Harian memakai materi Hafalan Doa Mahasiswa UDB 2026.</p>
    </div>
  </footer>
  <script src="<?= h(app_url('assets/app.js')) ?>"></script>
</body>
</html>
<?php
}
?>
