function calculateScores() {
  const table = document.getElementById('scoreTable');
  if (!table) return;
  let grand = 0;
  let filledRows = 0;
  table.querySelectorAll('tbody tr').forEach(row => {
    let rowTotal = 0;
    let filled = 0;
    row.querySelectorAll('.score-input').forEach(input => {
      const value = parseFloat(input.value);
      if (!isNaN(value)) {
        rowTotal += value;
        filled += 1;
      }
    });
    row.querySelector('.row-total').textContent = rowTotal ? rowTotal.toFixed(0) : '0';
    if (filled > 0) {
      grand += rowTotal;
      filledRows += 1;
    }
  });
  const grandTotal = document.getElementById('grandTotal');
  const averageTotal = document.getElementById('averageTotal');
  grandTotal.textContent = grand.toFixed(0);
  averageTotal.textContent = filledRows ? (grand / (filledRows * 6)).toFixed(2) : '0';
}

document.addEventListener('input', event => {
  if (event.target.classList.contains('score-input')) calculateScores();
});
document.addEventListener('DOMContentLoaded', calculateScores);
