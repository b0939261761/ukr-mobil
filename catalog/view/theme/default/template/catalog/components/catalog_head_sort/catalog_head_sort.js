const onClickCatalogViewBtn = ({ target }) => {
  target.parentElement.querySelector('.catalog-view__btn:disabled').disabled = false;
  target.disabled = true;

  console.log(target.dataset.view);
};

const catalogViewBtn = document.querySelectorAll('.catalog-view__btn');
catalogViewBtn.forEach(el => el.addEventListener('click', onClickCatalogViewBtn));

// ------------------------------------------

const onClickCatalogMobileBtnFilter = ({ target }) => {
  target.parentElement.classList.toggle('catalog-mobile-btn-filter--open');
  console.log(1111);
};

const catalogMobileBtnFilter = document.getElementById('catalogMobileBtnFilter');
catalogMobileBtnFilter.addEventListener('click', onClickCatalogMobileBtnFilter);
