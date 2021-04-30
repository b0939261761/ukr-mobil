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
  const characteristicList = target.parentElement.querySelector('.product-slide__characteristic-list');
  characteristicList.classList.toggle('product-slide__characteristic-list--open');
};

if (btnOpenOptionsSwiperNewList) {
  btnOpenOptionsSwiperNewList.forEach(el => el.addEventListener('click', onClickBtnOpenOptionsSwiperNewList));
}
