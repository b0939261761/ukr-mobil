const initBtnScrollOnTop = () => {
  const btnScrollToTopEl = document.getElementById('btnScrollToTop');
  const onClickbtnScrollToTop = () => window.scrollTo({ left: 0, top: 0, behavior: 'smooth' });
  btnScrollToTopEl.addEventListener('click', onClickbtnScrollToTop);

  const onHide = entries => {
    entries.forEach(({ isIntersecting }) => {
      isIntersecting
        ? btnScrollToTopEl.classList.add('btn-scroll-to-top--hide')
        : btnScrollToTopEl.classList.remove('btn-scroll-to-top--hide');
    });
  };

  (new IntersectionObserver(onHide))
    .observe(document.getElementById('interseptorHideBtnScrollToTop'));

  const onDock = entries => {
    entries.forEach(({ isIntersecting }) => {
      isIntersecting
        ? btnScrollToTopEl.classList.remove('btn-scroll-to-top--float')
        : btnScrollToTopEl.classList.add('btn-scroll-to-top--float');
    });
  };

  (new IntersectionObserver(onDock))
    .observe(document.getElementById('interseptorDockBtnScrollToTop'));
};

initBtnScrollOnTop();
