import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/tracking/tracking.css',
    './catalog/view/theme/default/template/shared/components/breadcrumbs/breadcrumbs.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('tracking.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

const buildJs = () => {
  gulp.src([
    './catalog/view/theme/default/template/tracking/tracking.js'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('tracking.min.js'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/scripts'));
};

export default () => {
  buildCss();
  buildJs();
};
