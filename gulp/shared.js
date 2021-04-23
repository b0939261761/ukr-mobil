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
    'mobile-menu', 'close', 'menu', 'search', 'heart', 'user', 'cart', // header_bottom
    'nav-display', 'nav-tablet', 'nav-clock', 'nav-phone', 'nav-camera', // nav_categories
    'nav-tool', 'nav-xioami', 'nav-battery', 'nav-accessory', // nav_categories
    'arrow-right', // menu,
    'schedule', 'docs', 'warning', 'apple-pay', 'google-pay', 'mastercard', 'visa', // footer
    'phone', 'facebook', 'telegram', 'instagram', 'viber', '32x32', 'arrow-up' // footer
  ];

  gulp.src(iconsName.map(el => `${pathIcons}${el}.svg`))
    .pipe(svgmin({ plugins: [{ removeUselessStrokeAndFill: false }] }))
    .pipe(rename({ prefix: 'icon-' }))
    .pipe(svgstore({ inlineSvg: true }))
    .pipe(rename({ suffix: '-sprite-icons' }))
    .pipe(gulp.dest('./resourse/images'));
};

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/main.css',
    './catalog/view/theme/default/template/shared/components/header/header.css',
    './catalog/view/theme/default/template/shared/components/header_top/header_top.css',
    './catalog/view/theme/default/template/shared/components/header_bottom/header_bottom.css',
    './catalog/view/theme/default/template/shared/components/footer/footer.css',
    './catalog/view/theme/default/template/shared/components/nav_categories/nav_categories.css',
    './catalog/view/theme/default/template/shared/components/mobile_menu/mobile_menu.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('shared.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

const buildJs = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/components/footer/footer.js',
    './catalog/view/theme/default/template/shared/components/nav_categories/nav_categories.js',
    './catalog/view/theme/default/template/shared/components/mobile_menu/mobile_menu.js'
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
