</main>

<footer class="footer">
  <div class="container">
    <p>© <?= date('Y') ?> <span>Pastimes</span> — Pre-Loved Fashion Marketplace 🇿🇦 &nbsp;|&nbsp; Giving clothes a second life.</p>
  </div>
</footer>

<script>
  // Navbar scroll effect
  window.addEventListener('scroll', () => {
    document.getElementById('navbar')?.classList.toggle('scrolled', window.scrollY > 30);
  });

  // Auto-dismiss flash messages
  setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => {
      el.style.transition = 'opacity 0.4s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 400);
    });
  }, 4500);
</script>
</body>
</html>
