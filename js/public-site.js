(function () {
  var cookies = document.cookie ? document.cookie.split('; ') : [];
  var authLink = document.querySelector('[data-auth-link]');
  var navContactLinks = document.querySelectorAll('nav a[href="contact.html"], nav a[href="contact_kk.html"]');
  var nav = document.querySelector('.site-nav');
  var lang = (document.documentElement.getAttribute('lang') || 'ru').toLowerCase();
  var publishHref = lang === 'kk' ? 'vacancy_kk.php' : 'vacancy.php';
  var publishLabel = lang === 'kk' ? 'Жариялау' : 'Опубликовать';

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

  if (nav && !nav.querySelector('a[href="vacancy.php"], a[href="vacancy_kk.php"]')) {
    var vacanciesLink = nav.querySelector('a[href="vacancies.php"], a[href="vacancies_kk.php"]');
    var publishLink = document.createElement('a');
    publishLink.setAttribute('href', publishHref);
    publishLink.textContent = publishLabel;

    if (vacanciesLink && vacanciesLink.nextSibling) {
      nav.insertBefore(publishLink, vacanciesLink.nextSibling);
    } else if (vacanciesLink) {
      nav.appendChild(publishLink);
    } else {
      nav.insertBefore(publishLink, nav.firstChild);
    }
  }
}());
