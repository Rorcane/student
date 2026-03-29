  </main>
  <footer class="footer">
    <div class="container">
      <div class="cols">
        <div>
          <strong>TruWork</strong>
          <div class="text-muted">Поиск работы и подбор персонала</div>
        </div>
        <div class="text-muted">© <?= date('Y') ?> TruWork</div>
      </div>
    </div>
  </footer>
  <script>
    // простая логика переключения навигации на мобильных
    document.addEventListener('click', e=>{
      if(e.target.matches('.hamburger')) {
        const nav = document.querySelector('.nav');
        if(!nav) return;
        nav.style.display = nav.style.display === 'flex' ? '' : 'flex';
      }
    });
  </script>
</body>
</html>
