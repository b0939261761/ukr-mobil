import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';
import rename from 'gulp-rename';
import svgmin from 'gulp-svgmin';
import svgstore from 'gulp-svgstore';

const buildSvg = () => {
  const pathIcons = './catalog/view/theme/default/images/icons/shared/';
  const iconsName = [
    'usd', 'download', // header_top
    'close', // header_bottom, modal_windows
    'mobile-menu', 'menu', 'search', 'heart', 'user', 'cart', 'check', // header_bottom
    'nav-display', 'nav-tablet', 'nav-clock', 'nav-phone', 'nav-camera', // nav_catalog
    'nav-tool', 'nav-xioami', 'nav-battery', 'nav-accessory', // nav_catalog
    'arrow-right', // menu,
    'schedule', 'docs', 'warning', 'apple-pay', 'google-pay', 'mastercard', 'visa', // footer
    'phone', 'facebook', 'telegram', 'instagram', 'viber', '32x32', // footer
    'arrow-up', // footer, catalog
    'home', // breadcrumbs
    'notify', // product
    'info', 'news', 'service', 'delivery', 'warranty', 'docs-full', // right-menu
    'calendar', // home, news_list
    'update', // news_list, catalog
    'eye', // form password,
    'plus', 'minus', // cart
    'thin-arrow' // header-search
  ];

  gulp.src(iconsName.map(el => `${pathIcons}${el}.svg`))
    .pipe(svgmin({ plugins: [{ removeUselessStrokeAndFill: false }] }))
    .pipe(rename({ prefix: 'icon-' }))
    .pipe(svgstore({ inlineSvg: true }))
    .pipe(rename({ basename: 'shared-sprite-icons' }))
    .pipe(gulp.dest('./resourse/images'));
};

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/main.css',
    './catalog/view/theme/default/template/shared/header_search/header_search.css',
    './catalog/view/theme/default/template/shared/components/breadcrumbs/breadcrumbs.css',
    './catalog/view/theme/default/template/shared/components/header/header.css',
    './catalog/view/theme/default/template/shared/components/header_banner/header_banner.css',
    './catalog/view/theme/default/template/shared/components/header_top/header_top.css',
    './catalog/view/theme/default/template/shared/components/header_bottom/header_bottom.css',
    './catalog/view/theme/default/template/shared/components/footer/footer.css',
    './catalog/view/theme/default/template/shared/components/footer_btn_scroll_to_top/footer_btn_scroll_to_top.css',
    './catalog/view/theme/default/template/shared/components/nav_catalog/nav_catalog.css',
    './catalog/view/theme/default/template/shared/components/mobile_menu/mobile_menu.css',
    './catalog/view/theme/default/template/shared/modal_window.css',
    './catalog/view/theme/default/template/shared/modal_window_login.css',
    './catalog/view/theme/default/template/shared/modal_window_recovery.css',
    './catalog/view/theme/default/template/shared/modal_window_favorites.css',
    './catalog/view/theme/default/template/shared/cart/cart.css',
    './catalog/view/theme/default/template/shared/cart_toast/cart_toast.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('shared.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

const buildJs = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/main.js',
    './catalog/view/theme/default/template/shared/components/header_banner/header_banner.js',
    './catalog/view/theme/default/template/shared/header_search/header_search.js',
    './catalog/view/theme/default/template/shared/components/header_bottom/header_bottom.js',
    './catalog/view/theme/default/template/shared/components/header/header.js',
    './catalog/view/theme/default/template/shared/components/footer/footer.js',
    './catalog/view/theme/default/template/shared/components/footer_btn_scroll_to_top/footer_btn_scroll_to_top.js',
    './catalog/view/theme/default/template/shared/components/nav_catalog/nav_catalog.js',
    './catalog/view/theme/default/template/shared/components/mobile_menu/mobile_menu.js',
    './catalog/view/theme/default/template/shared/modal_window.js',
    './catalog/view/theme/default/template/shared/modal_window_feedback_error.js',
    './catalog/view/theme/default/template/shared/modal_window_feedback_manager.js',
    './catalog/view/theme/default/template/shared/modal_window_login.js',
    './catalog/view/theme/default/template/shared/modal_window_recovery.js',
    './catalog/view/theme/default/template/shared/modal_window_buy.js',
    './catalog/view/theme/default/template/shared/modal_window_favorites.js',
    './catalog/view/theme/default/template/shared/modal_window_wishlist.js',
    './catalog/view/theme/default/template/shared/cart/cart.js',
    './catalog/view/theme/default/template/shared/cart_toast/cart_toast.js'

  ])
    .pipe(sourcemaps.init())
    .pipe(concat('shared.min.js'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/scripts'));
};

export default () => {
  buildSvg();
  buildCss();
  buildJs();
};
