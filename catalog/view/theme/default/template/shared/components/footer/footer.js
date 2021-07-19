const footerWindowModalFeedbackError = document.getElementById('footerWindowModalFeedbackError');
footerWindowModalFeedbackError.addEventListener('click', () => window.modalWindowFeedbackError());

const footerWindowModalFeedbackManager = document.getElementById('footerWindowModalFeedbackManager');
footerWindowModalFeedbackManager.addEventListener('click', () => window.modalWindowFeedbackManager());

const siteLoaderEl = document.getElementById('siteLoader');

window.showSiteLoader = () => {
  siteLoaderEl.classList.remove('hide');
  document.body.classList.add('body-site-loader');
};

window.hideSiteLoader = () => {
  siteLoaderEl.classList.add('hide');
  document.body.classList.remove('body-site-loader');
};
