new Swiper('#swiperSpecial', {
  effect: 'fade',
  fadeEffect: {
    crossFade: true
  },
  autoplay: {
    delay: 5000,
    waitForTransition: false,
    disableOnInteraction: false
  },
  pagination: {
    el: '.swipper-special__swiper-pagination',
    bulletClass: 'swipper-special__swiper-pagination-bullet',
    bulletActiveClass: 'swipper-special__swiper-pagination-bullet--active',
    bulletElement: 'button',
    type: 'bullets',
    clickable: true
  }
});
