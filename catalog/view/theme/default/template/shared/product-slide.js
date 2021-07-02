const productSlideBtnOpenOptionsList = document.querySelectorAll('.product-slide__options-btn-open');

if (productSlideBtnOpenOptionsList) {
  const onClickProductSlideBtnOpenOptions = ({ target }) => {
    const options = target.parentElement;
    const wrapperCharacteristicList = options.querySelector('.product-slide__wrapper-characteristic-list');
    const maxHeight = wrapperCharacteristicList.style.getPropertyValue('--max-height') ? null : `${options.offsetTop}px`;
    wrapperCharacteristicList.style.setProperty('--max-height', maxHeight);
    target.firstElementChild.classList.toggle('product-slide__options-btn-open-img--open');
  };

  productSlideBtnOpenOptionsList.forEach(el => el.addEventListener('click', onClickProductSlideBtnOpenOptions));
}

const productSlideBtnBuyList = document.querySelectorAll('.product-slide__btn-buy');

if (productSlideBtnBuyList) {
  const onClickProductSlideBtnBuy = ({ target }) => window.modalWindowBuy(target.closest('.product-slide').dataset.id);
  productSlideBtnBuyList.forEach(el => el.addEventListener('click', onClickProductSlideBtnBuy));
}

const productSlideBtnCartList = document.querySelectorAll('.product-slide__btn-cart');

if (productSlideBtnCartList) {
  const onClickProductSlideBtnCart = async ({ target }) => {
    target.disabled = true;
    const slide = target.closest('.product-slide');
    try {
      if (await window.cartAdd(slide.dataset.id)) {
        window.cartToast(slide.querySelector('.product-slide__img').src);
      }
      await window.cartGet();
    } catch (err) {
      console.error(err);
    }
    target.disabled = false;
  };

  productSlideBtnCartList.forEach(el => el.addEventListener('click', onClickProductSlideBtnCart));
}
