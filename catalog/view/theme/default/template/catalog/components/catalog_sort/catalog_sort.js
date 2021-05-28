const onClickCatalogSortBtnValue = ({ target }) => target.parentElement.classList.toggle('catalog-sort--open');
const catalogSortBtnValue = document.getElementById('catalogSortBtnValue');
if (catalogSortBtnValue) catalogSortBtnValue.addEventListener('click', onClickCatalogSortBtnValue);
