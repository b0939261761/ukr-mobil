import shared from './gulp/shared.js';
import home from './gulp/home.js';
import catalog from './gulp/catalog.js';
import sitemap from './gulp/sitemap.js';

export default cb => {
  // shared();
  // home();
  // catalog();
  sitemap();
  cb();
};
