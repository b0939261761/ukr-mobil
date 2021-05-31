import shared from './gulp/shared.js';
import home from './gulp/home.js';
import catalog from './gulp/catalog.js';
import sitemap from './gulp/sitemap.js';
import page404 from './gulp/404.js';
import tracking from './gulp/tracking.js';
import about from './gulp/about.js';
import information from './gulp/information.js';
import news_list from './gulp/news_list.js';

export default cb => {
  // shared();
  // home();
  // catalog();
  // sitemap();
  // page404();
  // tracking();
  // about();
  // information();
  news_list();
  cb();
};
