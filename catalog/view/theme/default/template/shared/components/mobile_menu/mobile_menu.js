const header = document.getElementById('header');
const mobileMenu = document.getElementById('mobileMenu');
const btnMobileMenu = document.getElementById('btnMobileMenu');

const setMobileMenuTop = () => {
  const { top, height } = header.getBoundingClientRect();
  mobileMenu.style.setProperty('--mobile-menu-top', `${height + top}px`);
};

const onClickBtnMobileMenu = () => {
  if (document.body.classList.contains('body--mobile-menu-open')) {
    document.body.classList.remove('body--mobile-menu-catalog-open');
    const navCategoriesList = document.querySelectorAll('.nav-catalog__list--is-visible');
    navCategoriesList.forEach(el => el.classList.remove('nav-catalog__list--is-visible'));
  } else {
    setMobileMenuTop();
  }

  document.body.classList.toggle('body--mobile-menu-open');
};

btnMobileMenu.addEventListener('click', onClickBtnMobileMenu);

// ------------------------------------------------------------------

const btnHeaderMenuEl = document.getElementById('btnHeaderMenu');

const onClickBtnHeaderMenu = evt => {
  if (evt.target !== evt.currentTarget) return;
  if (!document.body.classList.contains('body--mobile-menu-open')) setMobileMenuTop();
  document.body.classList.toggle('body--mobile-menu-open');
};

btnHeaderMenuEl.addEventListener('click', onClickBtnHeaderMenu);
mobileMenu.addEventListener('click', onClickBtnHeaderMenu);

// ------------------------------------------------------------------

const btnMobileMenuCategories = document.getElementById('btnMobileMenuCategories');
const btnMobileMenuBack = document.getElementById('btnMobileMenuBack');

const onClickBtnMobileMenuCategories = () => {
  document.body.classList.add('body--mobile-menu-catalog-open');
};

btnMobileMenuCategories.addEventListener('click', onClickBtnMobileMenuCategories);

const onClickBtnMobileMenuBack = () => {
  const navCategoriesList = document.querySelectorAll('.nav-catalog__list--is-visible');
  if (!navCategoriesList.length) {
    document.body.classList.remove('body--mobile-menu-catalog-open');
    return;
  }
  const navCategoriesListLast = navCategoriesList[navCategoriesList.length - 1];
  navCategoriesListLast.classList.remove('nav-catalog__list--is-visible');
};

btnMobileMenuBack.addEventListener('click', onClickBtnMobileMenuBack);

// ------------------------------------------------------------------

const mmWindowModalFeedbackError = document.getElementById('mmWindowModalFeedbackError');
mmWindowModalFeedbackError.addEventListener('click', () => window.modalWindowFeedbackError());

const mmWindowModalFeedbackManager = document.getElementById('mmWindowModalFeedbackManager');
mmWindowModalFeedbackManager.addEventListener('click', () => window.modalWindowFeedbackManager());
