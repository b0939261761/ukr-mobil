import shared from './gulp/shared.js';
import home from './gulp/home.js';

export default cb => {
  shared();
  home();
  cb();
};
