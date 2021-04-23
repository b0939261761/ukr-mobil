import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';
import rename from 'gulp-rename';
import svgmin from 'gulp-svgmin';
import svgstore from 'gulp-svgstore';

const buildSvg = () => {
  const pathIcons = './catalog/view/theme/default/images/icons/home/';
  const iconsName = [
    'benefit-0', 'benefit-1', 'benefit-2', 'benefit-3',
    'lines-lighter-left', 'lines-lighter-right',
    'calendar'
  ];

  gulp.src(iconsName.map(el => `${pathIcons}${el}.svg`))
    .pipe(svgmin({ plugins: [{ removeUselessStrokeAndFill: false }] }))
    .pipe(rename({ prefix: 'icon-' }))
    .pipe(svgstore({ inlineSvg: true }))
    .pipe(rename({ suffix: '-sprite-icons' }))
    .pipe(gulp.dest('./resourse/images'));
};

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/shared/product-swiper.css',
    './catalog/view/theme/default/template/shared/product-slide.css',
    './catalog/view/theme/default/template/home/home.css',
    './catalog/view/theme/default/template/home/components/special/special.css',
    './catalog/view/theme/default/template/home/components/benefits/benefits.css',
    './catalog/view/theme/default/template/home/components/incomes/incomes.css',
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
    './catalog/view/theme/default/template/home/components/new/new.js',
    './catalog/view/theme/default/template/home/components/promotions/promotions.js',
    './catalog/view/theme/default/template/home/components/incomes/incomes.js',
    './catalog/view/theme/default/template/home/components/news/news.js'

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
