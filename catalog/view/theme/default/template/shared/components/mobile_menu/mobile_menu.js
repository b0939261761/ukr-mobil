const mobileMenu = document.getElementById('mobileMenu');
const btnMobileMenu = document.getElementById('btnMobileMenu');

const onClickBtnMobileMenu = () => {
  if (document.body.classList.contains('body--mobile-menu-open')) {
    document.body.classList.remove('body--mobile-menu-catalog-open');
    const navCategoriesList = document.querySelectorAll('.nav-catalog__list--is-visible');
    navCategoriesList.forEach(el => el.classList.remove('nav-catalog__list--is-visible'));
  }

  document.body.classList.toggle('body--mobile-menu-open');
};

btnMobileMenu.addEventListener('click', onClickBtnMobileMenu);

// ------------------------------------------------------------------

const btnHeaderMenuEl = document.getElementById('btnHeaderMenu');

const onClickBtnHeaderMenu = evt => {
  if (evt.target !== evt.currentTarget) return;
  document.body.classList.toggle('body--mobile-menu-open');
};

btnHeaderMenuEl.addEventListener('click', onClickBtnHeaderMenu);
mobileMenu.addEventListener('click', onClickBtnHeaderMenu);

// ------------------------------------------------------------------

const btnMobileMenuCategories = document.getElementById('btnMobileMenuCategories');

const onClickBtnMobileMenuCategories = () => {
  if (document.body.classList.contains('body--mobile-menu-catalog-open')) {
    const navCategoriesList = document.querySelectorAll('.nav-catalog__list--is-visible');
    if (!navCategoriesList.length) {
      document.body.classList.remove('body--mobile-menu-catalog-open');
      return;
    }
    const navCategoriesListLast = navCategoriesList[navCategoriesList.length - 1];
    navCategoriesListLast.classList.remove('nav-catalog__list--is-visible');
  } else {
    document.body.classList.add('body--mobile-menu-catalog-open');
  }
};

btnMobileMenuCategories.addEventListener('click', onClickBtnMobileMenuCategories);

// ------------------------------------------------------------------

const mmWindowModalFeedbackError = document.getElementById('mmWindowModalFeedbackError');
mmWindowModalFeedbackError.addEventListener('click', () => window.modalWindowFeedbackError());

const mmWindowModalFeedbackManager = document.getElementById('mmWindowModalFeedbackManager');
mmWindowModalFeedbackManager.addEventListener('click', () => window.modalWindowFeedbackManager());

// ------------------------------------------------------------------

const wrapperMobileMenuContacts = document.getElementById('wrapperMobileMenuContacts');

const setMobileMenuContactsHeight = height => wrapperMobileMenuContacts.style.setProperty('--height', height);

const onResizeMobileMenuContacts = () => setMobileMenuContactsHeight(`${wrapperMobileMenuContacts.firstElementChild.scrollHeight}px`);
const onResizeMobileMenuContactsThrottle = window.shared.throttle(onResizeMobileMenuContacts, 500);

const onClickMobileMenuBtnContact = () => {
  if (wrapperMobileMenuContacts.style.getPropertyValue('--height')) {
    setMobileMenuContactsHeight(null);
    window.removeEventListener('resize', onResizeMobileMenuContactsThrottle);
  } else {
    onResizeMobileMenuContacts();
    window.addEventListener('resize', onResizeMobileMenuContactsThrottle);
  }
};

const mobileMenuBtnContact = document.getElementById('mobileMenuBtnContact');
mobileMenuBtnContact.addEventListener('click', onClickMobileMenuBtnContact);

// ------------------------------------------------------------------

const mobileMenuLogin = document.getElementById('mobileMenuLogin');
if (mobileMenuLogin) mobileMenuLogin.addEventListener('click', () => window.modalWindowLogin());
