import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';
import rename from 'gulp-rename';
import svgmin from 'gulp-svgmin';
import svgstore from 'gulp-svgstore';

const buildSvg = () => {
  const pathIcons = './catalog/view/theme/default/images/icons/checkout/';
  const iconsName = [
    'location', 'clock'
  ];

  const pathCommonIcons = './catalog/view/theme/default/images/icons/common/';
  const commonIconsName = [
    'new-post', 'car', 'courier-new-post', 'courier', 'ukrpost', 'justin',
    'google', 'apple', 'privat', 'cash-delivery', 'card', 'cash-less', 'debt-pay', 'mono'
  ];

  const iconsAll = [
    ...iconsName.map(el => `${pathIcons}${el}.svg`),
    ...commonIconsName.map(el => `${pathCommonIcons}${el}.svg`)
  ];

  gulp.src(iconsAll)
    .pipe(svgmin({ plugins: [{ removeUselessStrokeAndFill: false }] }))
    .pipe(rename({ prefix: 'icon-' }))
    .pipe(svgstore({ inlineSvg: true }))
    .pipe(rename({ basename: 'checkout-sprite-icons' }))
    .pipe(gulp.dest('./resourse/images'));
};

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/checkout/checkout.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('checkout.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

const buildJs = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/new_post.js',
    './catalog/view/theme/default/template/checkout/checkout.js'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('checkout.min.js'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/scripts'));
};

export default () => {
  buildSvg();
  buildCss();
  buildJs();
};
