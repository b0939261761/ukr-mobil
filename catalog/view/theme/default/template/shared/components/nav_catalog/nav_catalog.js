const navCategoriesListActivate = row => {
  if (!window.matchMedia('(min-width: 1080px)').matches) return;
  const navCategoriesList = row.querySelector('.nav-catalog__list');
  if (navCategoriesList) navCategoriesList.classList.add('nav-catalog__list--is-visible');
};

const deactivateCategoriesListActivate = row => {
  if (!window.matchMedia('(min-width: 1080px)').matches) return;
  const navCategoriesList = row.querySelector('.nav-catalog__list');
  if (navCategoriesList) navCategoriesList.classList.remove('nav-catalog__list--is-visible');
};

$('.nav-catalog__list').menuAim({
  activate: navCategoriesListActivate,
  deactivate: deactivateCategoriesListActivate,
  exitMenu: () => true
});

const navCategoriesBtnNavList = document.querySelectorAll('.nav-catalog__btn-nav');
const onClickNavCategoriesBtnNav = evt => {
  if (window.matchMedia('(min-width: 1080px)').matches) return;
  const navCategoriesList = evt.target.parentElement.querySelector('.nav-catalog__list');
  if (navCategoriesList) navCategoriesList.classList.add('nav-catalog__list--is-visible');
};
navCategoriesBtnNavList.forEach(el => el.addEventListener('click', onClickNavCategoriesBtnNav));
