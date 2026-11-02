<?php
require_once __DIR__ . '/includes/layout.php';
$exercises = load_json_file(__DIR__ . '/data/exercises.json');
render_header('Kartu Penilaian Hafalan');
?>
<section class="detail-hero card print-hide">
  <span class="badge">Tahfid • Kompetensi Dasar Islam</span>
  <h1>Kartu Penilaian Hafalan</h1>
  <p>Isi nilai 60–100 untuk setiap indikator. Jumlah dan nilai total akan dihitung otomatis di browser.</p>
  <button class="btn primary" onclick="window.print()">Cetak / Simpan PDF</button>
</section>

<section class="assessment card">
  <div class="assessment-title">
    <h2>TAHFID<br>KOMPETENSI DASAR ISLAM<br>KARTU PENILAIAN HAFALAN</h2>
  </div>
  <div class="student-grid">
    <label>NAMA <input type="text" placeholder="................................"></label>
    <label>HARI/TANGGAL <input type="text" placeholder="................................"></label>
    <label>KELAS/NIM <input type="text" placeholder="................................"></label>
    <label>PENGUJI <input type="text" placeholder="................................"></label>
  </div>
  <div class="table-wrap">
    <table class="score-table" id="scoreTable">
      <thead>
        <tr>
          <th rowspan="2">No</th>
          <th rowspan="2">Nama Surat / Materi</th>
          <th colspan="2">Adab</th>
          <th colspan="2">Tajwid</th>
          <th colspan="2">Kefasihan</th>
          <th rowspan="2">Jumlah</th>
        </tr>
        <tr>
          <th>Sikap</th>
          <th>Pakaian</th>
          <th>Ketepatan Huruf</th>
          <th>Hukum Tajwid</th>
          <th>Tartil</th>
          <th>Nada</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($exercises as $exercise): ?>
          <tr>
            <td><?= h($exercise['no']) ?></td>
            <td><?= h($exercise['title']) ?></td>
            <?php for ($i = 0; $i < 6; $i++): ?>
              <td><input class="score-input" type="number" min="60" max="100" step="1" placeholder="60-100"></td>
            <?php endfor; ?>
            <td class="row-total">0</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><th colspan="8">Jumlah Nilai</th><th id="grandTotal">0</th></tr>
        <tr><th colspan="8">Nilai Total / Rata-rata</th><th id="averageTotal">0</th></tr>
      </tfoot>
    </table>
  </div>
</section>
<script src="<?= h(app_url('assets/score.js')) ?>"></script>
<?php render_footer(); ?>
