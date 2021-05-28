const btnScrollToTopEl = document.getElementById('btnScrollToTop');
const onClickbtnScrollToTop = () => window.scrollTo({ left: 0, top: 0, behavior: 'smooth' });
btnScrollToTopEl.addEventListener('click', onClickbtnScrollToTop);

const onHide = entries => {
  entries.forEach(({ isIntersecting }) => {
    isIntersecting
      ? btnScrollToTopEl.classList.add('footer-btn-scroll-to-top--hide')
      : btnScrollToTopEl.classList.remove('footer-btn-scroll-to-top--hide');
  });
};

const interseptorHideFooterBtnScrollToTop = document.createElement('div');
interseptorHideFooterBtnScrollToTop.classList.add('interseptor-hide-footer-btn-scroll-to-top');
document.body.appendChild(interseptorHideFooterBtnScrollToTop);

(new IntersectionObserver(onHide)).observe(interseptorHideFooterBtnScrollToTop);

const onDock = entries => {
  entries.forEach(({ isIntersecting }) => {
    isIntersecting
      ? btnScrollToTopEl.classList.remove('footer-btn-scroll-to-top--float')
      : btnScrollToTopEl.classList.add('footer-btn-scroll-to-top--float');
  });
};

(new IntersectionObserver(onDock))
  .observe(document.getElementById('interseptorDockFooterBtnScrollToTop'));
