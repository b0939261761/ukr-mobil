import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/components/breadcrumbs/breadcrumbs.css',
    './catalog/view/theme/default/template/shared/components/right_menu/right_menu.css',
    './catalog/view/theme/default/template/news_list/news_list.css',
    './catalog/view/theme/default/template/shared/btn-load-more.css',
    './catalog/view/theme/default/template/shared/components/pagination/pagination.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('news-list.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

const buildJs = () => {
  gulp.src([
    './catalog/view/theme/default/template/news_list/news_list.js'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('news_list.min.js'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/scripts'));
};

export default () => {
  buildCss();
  buildJs();
};
