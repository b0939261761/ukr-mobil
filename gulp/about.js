import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';
import rename from 'gulp-rename';
import svgmin from 'gulp-svgmin';
import svgstore from 'gulp-svgstore';

const buildSvg = () => {
  const pathIcons = './catalog/view/theme/default/images/icons/about/';
  const iconsName = [
    'service-price', 'service-consultation', 'service-cooperation', 'service-pay'
  ];

  const pathCommonIcons = './catalog/view/theme/default/images/icons/common/';
  const commonIconsName = [
    'benefit-0', 'benefit-1', 'benefit-2', 'benefit-3'
  ];

  const iconsAll = [
    ...iconsName.map(el => `${pathIcons}${el}.svg`),
    ...commonIconsName.map(el => `${pathCommonIcons}${el}.svg`)
  ];

  gulp.src(iconsAll)
    .pipe(svgmin({ plugins: [{ removeUselessStrokeAndFill: false }] }))
    .pipe(rename({ prefix: 'icon-' }))
    .pipe(svgstore({ inlineSvg: true }))
    .pipe(rename({ basename: 'about-sprite-icons' }))
    .pipe(gulp.dest('./resourse/images'));
};

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/components/right_menu/right_menu.css',
    './catalog/view/theme/default/template/about/about.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('about.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

export default () => {
  buildSvg();
  buildCss();
};
