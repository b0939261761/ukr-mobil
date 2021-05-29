new Swiper('#swiperIncome', {
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

const btnSwiperIncomeLast = document.getElementById('btnSwiperIncomeLast');
const btnSwiperIncomeExpected = document.getElementById('btnSwiperIncomeExpected');

const onClickSwiperIncome = () => {
  if (btnSwiperIncomeLast.disabled) {
    btnSwiperIncomeLast.disabled = false;
    btnSwiperIncomeExpected.disabled = true;
  } else {
    btnSwiperIncomeLast.disabled = true;
    btnSwiperIncomeExpected.disabled = false;
  }
};

btnSwiperIncomeLast.addEventListener('click', onClickSwiperIncome);
btnSwiperIncomeExpected.addEventListener('click', onClickSwiperIncome);
