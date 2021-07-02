import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';
import rename from 'gulp-rename';
import svgmin from 'gulp-svgmin';
import svgstore from 'gulp-svgstore';

const buildSvg = () => {
  const pathCommonIcons = './catalog/view/theme/default/images/icons/common/';
  const commonIconsName = [
    'benefit-0', 'benefit-1', 'benefit-2', 'benefit-3'
  ];

  gulp.src(commonIconsName.map(el => `${pathCommonIcons}${el}.svg`))
    .pipe(svgmin({ plugins: [{ removeUselessStrokeAndFill: false }] }))
    .pipe(rename({ prefix: 'icon-' }))
    .pipe(svgstore({ inlineSvg: true }))
    .pipe(rename({ basename: 'home-sprite-icons' }))
    .pipe(gulp.dest('./resourse/images'));
};

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/product-swiper.css',
    './catalog/view/theme/default/template/shared/product-slide.css',
    './catalog/view/theme/default/template/home/home.css',
    './catalog/view/theme/default/template/home/components/special/special.css',
    './catalog/view/theme/default/template/home/components/benefits/benefits.css',
    './catalog/view/theme/default/template/shared/components/slider_income/slider_income.css',
    './catalog/view/theme/default/template/home/components/news/news.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('home.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

const buildJs = () => {
  gulp.src([
    './catalog/view/theme/default/template/home/components/special/special.js',
    './catalog/view/theme/default/template/home/components/benefits/benefits.js',
    './catalog/view/theme/default/template/home/components/new/new.js',
    './catalog/view/theme/default/template/home/components/promotions/promotions.js',
    './catalog/view/theme/default/template/shared/components/slider_income/slider_income.js',
    './catalog/view/theme/default/template/home/components/news/news.js',
    './catalog/view/theme/default/template/shared/product-slide.js',
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('home.min.js'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/scripts'));
};

export default () => {
  buildSvg();
  buildCss();
  buildJs();
};
