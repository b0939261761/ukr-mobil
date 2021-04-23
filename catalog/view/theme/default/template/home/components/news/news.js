new Swiper('#swiperNews', {
  spaceBetween: 16,
  slidesPerView: 1,
  breakpoints: {
    481: {
      slidesPerView: 2
    },
    769: {
      slidesPerView: 3
    },
    961: {
      slidesPerView: 4
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
