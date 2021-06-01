import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/components/breadcrumbs/breadcrumbs.css',
    './catalog/view/theme/default/template/shared/components/right_menu/right_menu.css',
    './catalog/view/theme/default/template/news/news.css',
    './catalog/view/theme/default/template/shared/product-slide.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('news.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

const buildJs = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/product-slide.js'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('news.min.js'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/scripts'));
};

export default () => {
  buildCss();
  buildJs();
};
