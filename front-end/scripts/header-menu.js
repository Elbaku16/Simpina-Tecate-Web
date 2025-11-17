// front-end/scripts/header-menu.js
document.addEventListener('DOMContentLoaded', () => {
  // -- elementos del menu --
  const menuToggle = document.getElementById('menuToggle');
  const menuClose = document.getElementById('menuClose');
  const mobileMenu = document.getElementById('mobileMenu');
  const menuOverlay = document.getElementById('menuOverlay');
  const body = document.body;

  if (!menuToggle || !menuClose || !mobileMenu || !menuOverlay) {
    console.warn('No se encontraron todos los elementos del menú mobile');
    return;
  }

  const openMenu = () => {
    mobileMenu.classList.add('active');
    menuOverlay.classList.add('active');
    body.style.overflow = 'hidden';
  };

  const closeMenu = () => {
    mobileMenu.classList.remove('active');
    menuOverlay.classList.remove('active');
    body.style.overflow = '';
  };

  menuToggle.addEventListener('click', openMenu);
  menuClose.addEventListener('click', closeMenu);
  menuOverlay.addEventListener('click', closeMenu);

  const mobileLinks = document.querySelectorAll('.mobile-nav a');
  mobileLinks.forEach(link => {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
      closeMenu();
    }
  });

  mobileMenu.addEventListener('touchmove', (e) => {
    e.stopPropagation();
  }, { passive: false });

  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      if (window.innerWidth > 768 && mobileMenu.classList.contains('active')) {
        closeMenu();
      }
    }, 250);
  });
});