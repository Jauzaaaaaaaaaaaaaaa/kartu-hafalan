window.AT = {
  label: 'Kartu Hafalan',
  ms: 1800,
  intro: '<svg class="hf-star" viewBox="0 0 200 200" aria-hidden="true">' +
    '<rect pathLength="1" x="42" y="42" width="116" height="116"/>' +
    '<rect pathLength="1" x="42" y="42" width="116" height="116"/>' +
    '<circle pathLength="1" cx="100" cy="100" r="96"/>' +
    '<text x="100" y="116">حفظ</text></svg>'
};

/* Progres hafalan: ditandai per materi dan disimpan di browser ini */
document.addEventListener('DOMContentLoaded', function () {
  var cards = document.querySelectorAll('.exercise-card');
  if (!cards.length) return;
  var KEY = 'hafalan-selesai', done = {};
  try { done = JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) {}
  var hero = document.querySelector('.hero-card');
  var info = document.createElement('div');
  info.className = 'hf-progress';
  info.innerHTML = '<i><b></b></i><small></small>';
  if (hero) hero.appendChild(info);
  var total = document.querySelector('.big-number') ? +document.querySelector('.big-number').textContent : cards.length;
  function refresh() {
    var n = Object.keys(done).length;
    info.querySelector('b').style.width = (total ? n / total * 100 : 0) + '%';
    info.querySelector('small').textContent = n + ' dari ' + total + ' sudah hafal';
  }
  cards.forEach(function (c) {
    var link = c.querySelector('a.read-more');
    var id = link ? link.getAttribute('href').split('id=')[1] : null;
    if (!id) return;
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'hf-mark';
    var paint = function () {
      b.textContent = done[id] ? 'Sudah hafal' : 'Tandai hafal';
      b.setAttribute('aria-pressed', done[id] ? 'true' : 'false');
      c.classList.toggle('is-done', !!done[id]);
    };
    b.addEventListener('click', function () {
      if (done[id]) delete done[id]; else done[id] = 1;
      try { localStorage.setItem(KEY, JSON.stringify(done)); } catch (e) {}
      paint();
      refresh();
    });
    paint();
    c.appendChild(b);
  });
  refresh();
});
