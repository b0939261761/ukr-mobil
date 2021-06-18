import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/sitemap/sitemap.css',
    './catalog/view/theme/default/template/shared/components/pagination/pagination.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('sitemap.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

export default () => {
  buildCss();
};
