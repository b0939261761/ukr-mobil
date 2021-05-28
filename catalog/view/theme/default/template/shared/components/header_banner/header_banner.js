const headerBanner = document.getElementById('headerBanner');

if (headerBanner) {
  const onResizeHeaderBanner = () => {
    const { height } = headerBanner.getBoundingClientRect();
    document.body.style.setProperty('--header-banner-top', `${height}px`);
  };
  const onResizeHeaderBannerThrottle = window.shared.throttle(onResizeHeaderBanner, 1000, true);
  onResizeHeaderBannerThrottle();

  window.addEventListener('resize', onResizeHeaderBannerThrottle);
}
