const catalogItemBtnOpenOptionsList = document.querySelectorAll('.catalog-item__btn-open-options');

const onClickCatalogItemBtnOpenOptionsList = ({ target }) => {
  target
    .closest('.catalog-item')
    .querySelector('.catalog-item__characteristic-list')
    .classList.toggle('catalog-item__characteristic-list--open');

  target.classList.toggle('catalog-item__btn-open-options--open');
};

if (catalogItemBtnOpenOptionsList) {
  catalogItemBtnOpenOptionsList.forEach(el => el.addEventListener('click', onClickCatalogItemBtnOpenOptionsList));
}
