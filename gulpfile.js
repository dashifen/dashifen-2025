const {src, dest, watch} = require('gulp'),
  sass = require('gulp-sass')(require('sass')),
  autoPrefixer = require('autoprefixer'),
  postcss = require('gulp-postcss'),
  cssNano = require('cssnano');

const publicCss = async function () {
  return src('assets/styles/dashifen.scss', { sourcemaps: true })
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss([autoPrefixer(), cssNano()]))
    .pipe(dest('assets/styles', { sourcemaps: '.' }));
};

const adminCss = async function () {
  return src('assets/styles/admin.scss', { sourcemaps: true })
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss([autoPrefixer(), cssNano()]))
    .pipe(dest('assets/styles', { sourcemaps: '.' }));
}

const css = async function () {
  publicCss();
  adminCss();
}

const watcher = async function () {
  await css();
  watch('assets/styles/**/*.scss', css);
};

exports.adminCss = adminCss;
exports.publicCss = publicCss;
exports.build = css;
exports.watch = watcher;
