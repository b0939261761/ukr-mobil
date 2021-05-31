import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/product-swiper.css',
    './catalog/view/theme/default/template/shared/product-slide.css',
    './catalog/view/theme/default/template/shared/components/slider_income/slider_income.css',
    './catalog/view/theme/default/template/404/404.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('404.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

const buildJs = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/components/slider_income/slider_income.js'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('404.min.js'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/scripts'));
};

export default () => {
  buildCss();
  buildJs();
};
