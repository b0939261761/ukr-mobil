import shared from './gulp/shared.js';
import home from './gulp/home.js';
import catalog from './gulp/catalog.js';
import sitemap from './gulp/sitemap.js';
import page404 from './gulp/404.js';

export default cb => {
  shared();
  home();
  // catalog();
  // sitemap();
  page404();
  cb();
};
