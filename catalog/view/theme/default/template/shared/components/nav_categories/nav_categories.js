const navCategoriesListActivate = row => {
  if (!window.matchMedia('(min-width: 1080px)').matches) return;
  const navCategoriesList = row.querySelector('.nav-categories__list');
  if (navCategoriesList) navCategoriesList.classList.add('nav-categories__list--is-visible');
};

const deactivateCategoriesListActivate = row => {
  if (!window.matchMedia('(min-width: 1080px)').matches) return;
  const navCategoriesList = row.querySelector('.nav-categories__list');
  if (navCategoriesList) navCategoriesList.classList.remove('nav-categories__list--is-visible');
};

$('.nav-categories__list').menuAim({
  activate: navCategoriesListActivate,
  deactivate: deactivateCategoriesListActivate,
  exitMenu: () => true
});

const navCategoriesBtnNavList = document.querySelectorAll('.nav-categories__btn-nav');
const onClickNavCategoriesBtnNav = evt => {
  if (window.matchMedia('(min-width: 1080px)').matches) return;
  const navCategoriesList = evt.target.parentElement.querySelector('.nav-categories__list');
  if (navCategoriesList) navCategoriesList.classList.add('nav-categories__list--is-visible');
};
navCategoriesBtnNavList.forEach(el => el.addEventListener('click', onClickNavCategoriesBtnNav));
