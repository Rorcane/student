(function () {
  var cookies = document.cookie ? document.cookie.split('; ') : [];
  var authLink = document.querySelector('[data-auth-link]');
  var navContactLinks = document.querySelectorAll('nav a[href="contact.html"], nav a[href="contact_kk.html"]');

  var user = '';
  cookies.forEach(function (cookie) {
    var parts = cookie.split('=');
    var name = parts.shift();
    if (name === 'user') {
      user = decodeURIComponent(parts.join('='));
    }
  });

  if (authLink && user) {
    authLink.textContent = user;
    authLink.setAttribute('href', 'profile.php');
  }

  navContactLinks.forEach(function (link) {
    link.remove();
  });
}());
