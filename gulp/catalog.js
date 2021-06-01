import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import concat from 'gulp-concat';
import rename from 'gulp-rename';
import svgmin from 'gulp-svgmin';
import svgstore from 'gulp-svgstore';

const buildSvg = () => {
  const pathIcons = './catalog/view/theme/default/images/icons/catalog/';
  const iconsName = [
    'view-row', 'view-grid',
    'sort-popular', 'sort-new', 'sort-increase-price', 'sort-decrease-price', 'sort-promotions',
    'info'
  ];

  gulp.src(iconsName.map(el => `${pathIcons}${el}.svg`))
    .pipe(svgmin({ plugins: [{ removeUselessStrokeAndFill: false }] }))
    .pipe(rename({ prefix: 'icon-' }))
    .pipe(svgstore({ inlineSvg: true }))
    .pipe(rename({ basename: 'catalog-sprite-icons' }))
    .pipe(gulp.dest('./resourse/images'));
};

const buildCss = () => {
  gulp.src([
    './catalog/view/theme/default/template/catalog/catalog.css',
    './catalog/view/theme/default/template/catalog/components/breadcrumbs/breadcrumbs.css',
    './catalog/view/theme/default/template/catalog/components/catalog_head_sort/catalog_head_sort.css',
    './catalog/view/theme/default/template/catalog/components/catalog_sort/catalog_sort.css',
    './catalog/view/theme/default/template/catalog/components/catalog_filters/catalog_filters.css',
    './catalog/view/theme/default/template/catalog/components/catalog_items/catalog_items.css',
    './catalog/view/theme/default/template/catalog/components/catalog_pagination/catalog_pagination.css'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('catalog.min.css'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/styles'));
};

const buildJs = () => {
  gulp.src([
    './catalog/view/theme/default/template/catalog/catalog.js',
    './catalog/view/theme/default/template/catalog/components/breadcrumbs/breadcrumbs.js',
    './catalog/view/theme/default/template/catalog/components/catalog_head_sort/catalog_head_sort.js',
    './catalog/view/theme/default/template/catalog/components/catalog_sort/catalog_sort.js',
    './catalog/view/theme/default/template/catalog/components/catalog_filters/catalog_filters.js',
    './catalog/view/theme/default/template/catalog/components/catalog_items/catalog_items.js',
    './catalog/view/theme/default/template/catalog/components/catalog_pagination/catalog_pagination.js'
  ])
    .pipe(sourcemaps.init())
    .pipe(concat('catalog.min.js'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./resourse/scripts'));
};

export default () => {
  buildSvg();
  buildCss();
  buildJs();
};
