import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/product-swiper.css',
    './catalog/view/theme/default/template/shared/product-slide.css',
    './catalog/view/theme/default/template/shared/components/slider_income/slider_income.css',
    './catalog/view/theme/default/template/register_success/register_success.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('register-success.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

const buildJs = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/components/slider_income/slider_income.js'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('register-success.min.js'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/scripts'));
};

export default () => {
  buildCss();
  buildJs();
};
