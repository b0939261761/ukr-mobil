const onResizeHeader = () => {
  const { bottom } = document.getElementById('header').getBoundingClientRect();
  document.body.style.setProperty('--header-bottom', `${bottom}px`);
};

onResizeHeader();

const onResizeHeaderThrottle = window.shared.throttle(onResizeHeader, 100);
window.addEventListener('resize', onResizeHeaderThrottle);
window.addEventListener('scroll', onResizeHeaderThrottle);
