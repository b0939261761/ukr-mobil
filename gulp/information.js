import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';
import rename from 'gulp-rename';
import svgmin from 'gulp-svgmin';
import svgstore from 'gulp-svgstore';

const buildSvg = () => {
  const pathCommonIcons = './catalog/view/theme/default/images/icons/common/';
  const commonIconsName = [
    'info', 'news', 'service', 'delivery', 'warranty', 'docs-full'
  ];

  gulp.src(commonIconsName.map(el => `${pathCommonIcons}${el}.svg`))
    .pipe(svgmin({ plugins: [{ removeUselessStrokeAndFill: false }] }))
    .pipe(rename({ prefix: 'icon-' }))
    .pipe(svgstore({ inlineSvg: true }))
    .pipe(rename({ suffix: '-sprite-icons' }))
    .pipe(gulp.dest('./resourse/images'));
};

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
  buildSvg();
  buildCss();
};
