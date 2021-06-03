new Swiper('#swiperNew', {
  slidesPerView: 2,
  spaceBetween: 8,
  breakpoints: {
    601: {
      slidesPerView: 3,
      spaceBetween: 16
    },
    854: {
      slidesPerView: 4,
      spaceBetween: 16
    },
    1441: {
      slidesPerView: 5,
      spaceBetween: 16
    }
  },
  navigation: {
    nextEl: '.product-swiper__button-nav--next',
    prevEl: '.product-swiper__button-nav--prev'
  },
  scrollbar: {
    el: '.product-swiper__scrollbar',
    dragClass: 'product-swiper__scrollbar-drag',
    draggable: true
  }
});

const btnOpenOptionsSwiperNewList = document.querySelectorAll('#swiperNew .product-slide__options-btn-open');

const onClickBtnOpenOptionsSwiperNewList = ({ target }) => {
  const options = target.parentElement;
  const wrapperCharacteristicList = options.querySelector('.product-slide__wrapper-characteristic-list');
  const maxHeight = wrapperCharacteristicList.style.getPropertyValue('--max-height') ? null : `${options.offsetTop}px`;
  wrapperCharacteristicList.style.setProperty('--max-height', maxHeight);
  target.firstElementChild.classList.toggle('product-slide__options-btn-open-img--open');
};

if (btnOpenOptionsSwiperNewList) {
  btnOpenOptionsSwiperNewList.forEach(el => el.addEventListener('click', onClickBtnOpenOptionsSwiperNewList));
}
