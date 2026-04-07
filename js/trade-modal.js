/* trade-modal.js – Propose Trade modal logic */

document.addEventListener('DOMContentLoaded', () => {
  const overlay    = document.getElementById('trade-modal-overlay');
  const openBtn    = document.getElementById('propose-trade-btn');
  const closeBtn   = document.getElementById('trade-modal-close');
  const grid       = document.getElementById('trade-items-grid');
  const submitBtn  = document.getElementById('trade-submit-btn');
  const form       = document.getElementById('trade-form');

  if (!overlay || !openBtn) return;

  openBtn.addEventListener('click', () => {
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  });

  function closeModal() {
    overlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  closeBtn?.addEventListener('click', closeModal);
  overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

  // Enable submit only when an item is selected
  grid?.addEventListener('change', () => {
    const selected = grid.querySelector('input[type="radio"]:checked');
    if (submitBtn) submitBtn.disabled = !selected;
  });

  // Validate before submit
  form?.addEventListener('submit', e => {
    const selected = grid?.querySelector('input[type="radio"]:checked');
    if (!selected) {
      e.preventDefault();
      alert('Please select one of your items to offer in exchange.');
    }
  });
});
