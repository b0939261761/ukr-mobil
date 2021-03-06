import shared from './gulp/shared.js';
import home from './gulp/home.js';
import catalog from './gulp/catalog.js';
import sitemap from './gulp/sitemap.js';
import page404 from './gulp/404.js';
import tracking from './gulp/tracking.js';
import about from './gulp/about.js';
import information from './gulp/information.js';
import newsList from './gulp/news_list.js';
import news from './gulp/news.js';
import checkoutSuccess from './gulp/checkout_success.js';
import checkout from './gulp/checkout.js';
import account from './gulp/account.js';
import recovery from './gulp/recovery.js';
import registerSuccess from './gulp/register_success.js';

export default cb => {
  shared();
  home();
  catalog();
  sitemap();
  page404();
  tracking();
  about();
  information();
  newsList();
  news();
  checkoutSuccess();
  checkout();
  account();
  recovery();
  registerSuccess();
  cb();
};
