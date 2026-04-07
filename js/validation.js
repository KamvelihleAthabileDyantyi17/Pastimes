/* validation.js – Frontend form validation for Pastimes */

document.addEventListener('DOMContentLoaded', () => {

  // ── Register form ──────────────────────────────────────────────────────
  const regForm = document.getElementById('register-form');
  if (regForm) {
    regForm.addEventListener('submit', e => {
      clearErrors(regForm);
      let ok = true;

      const username = regForm.querySelector('#username');
      const email    = regForm.querySelector('#email');
      const password = regForm.querySelector('#password');
      const confirm  = regForm.querySelector('#confirm_password');

      if (!username.value.trim() || username.value.trim().length < 3) {
        showError(username, 'Username must be at least 3 characters.'); ok = false;
      }
      if (!isValidEmail(email.value)) {
        showError(email, 'Please enter a valid email address.'); ok = false;
      }
      if (password.value.length < 8) {
        showError(password, 'Password must be at least 8 characters.'); ok = false;
      }
      if (password.value !== confirm.value) {
        showError(confirm, 'Passwords do not match.'); ok = false;
      }
      if (!ok) e.preventDefault();
    });
  }

  // ── Login form ────────────────────────────────────────────────────────
  const loginForm = document.getElementById('login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', e => {
      clearErrors(loginForm);
      let ok = true;
      const email    = loginForm.querySelector('#email');
      const password = loginForm.querySelector('#password');
      if (!isValidEmail(email.value)) {
        showError(email, 'Please enter a valid email address.'); ok = false;
      }
      if (!password.value) {
        showError(password, 'Password is required.'); ok = false;
      }
      if (!ok) e.preventDefault();
    });
  }

  // ── Add listing form ──────────────────────────────────────────────────
  const listingForm = document.getElementById('listing-form');
  if (listingForm) {
    listingForm.addEventListener('submit', e => {
      clearErrors(listingForm);
      let ok = true;
      const title     = listingForm.querySelector('#title');
      const price     = listingForm.querySelector('#price');
      const desc      = listingForm.querySelector('#description');
      const category  = listingForm.querySelector('#category_id');
      const condition = listingForm.querySelector('#condition_label');

      if (!title.value.trim() || title.value.trim().length < 3) {
        showError(title, 'Title must be at least 3 characters.'); ok = false;
      }
      if (!price.value || parseFloat(price.value) <= 0) {
        showError(price, 'Please enter a valid price greater than 0.'); ok = false;
      }
      if (!desc.value.trim() || desc.value.trim().length < 10) {
        showError(desc, 'Description must be at least 10 characters.'); ok = false;
      }
      if (!category.value) {
        showError(category, 'Please select a category.'); ok = false;
      }
      if (!condition.value) {
        showError(condition, 'Please select a condition.'); ok = false;
      }
      if (!ok) e.preventDefault();
    });

    // Image preview
    const fileInput   = listingForm.querySelector('#image');
    const previewImg  = listingForm.querySelector('#image-preview');
    const uploadZone  = listingForm.querySelector('.upload-zone');
    if (fileInput && previewImg) {
      fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (file) {
          const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
          if (!allowed.includes(file.type)) {
            showError(fileInput, 'Only JPG, PNG, GIF, or WEBP files allowed.');
            return;
          }
          const reader = new FileReader();
          reader.onload = ev => {
            previewImg.src = ev.target.result;
            previewImg.style.display = 'block';
          };
          reader.readAsDataURL(file);
        }
      });

      // Drag and drop
      uploadZone?.addEventListener('dragover', ev => { ev.preventDefault(); uploadZone.classList.add('dragover'); });
      uploadZone?.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
      uploadZone?.addEventListener('drop', ev => {
        ev.preventDefault();
        uploadZone.classList.remove('dragover');
        if (ev.dataTransfer.files[0]) {
          fileInput.files = ev.dataTransfer.files;
          fileInput.dispatchEvent(new Event('change'));
        }
      });
    }
  }

  // ── Helpers ───────────────────────────────────────────────────────────
  function showError(input, msg) {
    input.style.borderColor = 'var(--danger)';
    let err = input.parentElement.querySelector('.form-error');
    if (!err) {
      err = document.createElement('span');
      err.className = 'form-error';
      input.parentElement.appendChild(err);
    }
    err.textContent = msg;
  }

  function clearErrors(form) {
    form.querySelectorAll('.form-error').forEach(el => el.remove());
    form.querySelectorAll('.form-control').forEach(el => el.style.borderColor = '');
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }
});
