import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';
import rename from 'gulp-rename';
import svgmin from 'gulp-svgmin';
import svgstore from 'gulp-svgstore';

const buildSvg = () => {
  const pathIcons = './catalog/view/theme/default/images/icons/checkout_success/';
  const iconsName = [
    'circle-check'
  ];

  const pathCommonIcons = './catalog/view/theme/default/images/icons/common/';
  const commonIconsName = [
    'new-post', 'car', 'courier-new-post', 'courier', 'ukrpost', 'justin',
    'google', 'apple', 'privat', 'cash-delivery', 'card', 'cash-less', 'debt-pay'
  ];

  const iconsAll = [
    ...iconsName.map(el => `${pathIcons}${el}.svg`),
    ...commonIconsName.map(el => `${pathCommonIcons}${el}.svg`)
  ];

  gulp.src(iconsAll)
    .pipe(svgmin({ plugins: [{ removeUselessStrokeAndFill: false }] }))
    .pipe(rename({ prefix: 'icon-' }))
    .pipe(svgstore({ inlineSvg: true }))
    .pipe(rename({ basename: 'checkout-success-sprite-icons' }))
    .pipe(gulp.dest('./resourse/images'));
};

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/components/breadcrumbs/breadcrumbs.css',
    './catalog/view/theme/default/template/checkout_success/checkout_success.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('checkout-success.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

export default () => {
  buildSvg();
  buildCss();
};
