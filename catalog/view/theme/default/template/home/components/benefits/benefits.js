const onClickBtnMobileCatalog = () => {
  document.body.classList.add('body--mobile-menu-open', 'body--mobile-menu-catalog-open');
};

const btnMobileCatalog = document.getElementById('btnMobileCatalog');
btnMobileCatalog.addEventListener('click', onClickBtnMobileCatalog);
