import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/components/breadcrumbs/breadcrumbs.css',
    './catalog/view/theme/default/template/shared/components/right_menu/right_menu.css',
    './catalog/view/theme/default/template/information/infromation.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('information.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

export default () => {
  buildCss();
};
