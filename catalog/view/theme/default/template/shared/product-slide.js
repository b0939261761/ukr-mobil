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

// ============================================================================

if (window.isLogged) {
  const productSlideBtnFavoriteList = document.querySelectorAll('.product-slide__btn-favorite');
  if (productSlideBtnFavoriteList) {
    const onClickProductSlideBtnFavorite = async ({ target }) => {
      target.disabled = true;
      await window.modalWindowFavorites(target.closest('.product-slide').dataset.id);
      target.disabled = false;
    };

    productSlideBtnFavoriteList.forEach(el => el.addEventListener('click', onClickProductSlideBtnFavorite));
  }

  // ============================================================================

  const productSlideBtnWishlistList = document.querySelectorAll('.product-slide__btn-wishlist');
  if (productSlideBtnWishlistList) {
    const onClickProductSlideBtnWishlist = async ({ target }) => {
      target.disabled = true;
      await window.modalWindowWishlist(target.closest('.product-slide').dataset.id);
      target.disabled = false;
    };

    productSlideBtnWishlistList.forEach(el => el.addEventListener('click', onClickProductSlideBtnWishlist));
  }
} else {
  const productSlideList = document.querySelectorAll('.product-slide');
  if (productSlideList) {
    const setWidthProductSlide = ({ target }) => target.style.setProperty('--product-slide-width', `${target.offsetWidth}px`);
    productSlideList.forEach(el => el.addEventListener('mouseenter', setWidthProductSlide));
  }

  // ============================================================================

  const productSlideBtnLikePopupBtnList = document.querySelectorAll('.product-slide__btn-popup-btn');
  if (productSlideBtnLikePopupBtnList) {
    productSlideBtnLikePopupBtnList.forEach(el => el.addEventListener('click', () => window.modalWindowLogin()));
  }
}
