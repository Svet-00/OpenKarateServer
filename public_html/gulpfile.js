'use strict'

// Load plugins
const autoprefixer = require('gulp-autoprefixer')
const cleanCSS = require('gulp-clean-css')
const del = require('del')
const gulp = require('gulp')
const header = require('gulp-header')
const merge = require('merge-stream')
const plumber = require('gulp-plumber')
const rename = require('gulp-rename')
const sass = require('gulp-sass')
const path = require('path')
const log = require('fancy-log')
const using = require('gulp-using')
const terser = require('gulp-terser')

// Load package.json for banner
const pkg = require('./package.json')

const paths = {
  root: './releases/current/public_html/',
  sass: './releases/current/public_html/scss/',
  css: './releases/current/public_html/css/',
  js: './releases/current/public_html/js/',
}

// Clean vendor
function clean() {
  return del([paths.root + 'vendor/'])
}

// Bring third party dependencies from node_modules into vendor directory
function modules() {
  // Bootstrap JS
  var bootstrapJS = gulp
    .src(paths.root + 'node_modules/bootstrap/dist/js/*')
    .pipe(gulp.dest(paths.root + './vendor/bootstrap/js'))
  // Bootstrap SCSS
  var bootstrapSCSS = gulp
    .src(paths.root + 'node_modules/bootstrap/scss/**/*')
    .pipe(gulp.dest(paths.root + './vendor/bootstrap/scss'))
  // ChartJS
  var chartJS = gulp
    .src(paths.root + 'node_modules/chart.js/dist/*.js')
    .pipe(gulp.dest(paths.root + 'vendor/chart.js'))
  // dataTables
  var dataTables = gulp
    .src([
      paths.root + 'node_modules/datatables.net/js/*.js',
      paths.root + 'node_modules/datatables.net-bs4/js/*.js',
      paths.root + 'node_modules/datatables.net-bs4/css/*.css',
    ])
    .pipe(gulp.dest(paths.root + 'vendor/datatables'))
  // Font Awesome
  var fontAwesome = gulp
    .src(paths.root + 'node_modules/@fortawesome/**/*')
    .pipe(gulp.dest(paths.root + 'vendor'))
  // jQuery Easing
  var jqueryEasing = gulp
    .src(paths.root + 'node_modules/jquery.easing/*.js')
    .pipe(gulp.dest(paths.root + 'vendor/jquery-easing'))
  // jQuery
  var jquery = gulp
    .src([
      paths.root + 'node_modules/jquery/dist/*',
      '!' + paths.root + 'node_modules/jquery/dist/core.js',
    ])
    .pipe(gulp.dest(paths.root + 'vendor/jquery'))
  return merge(
    bootstrapJS,
    bootstrapSCSS,
    chartJS,
    dataTables,
    fontAwesome,
    jquery,
    jqueryEasing
  )
}

// CSS task
function css() {
  return gulp
    .src(paths.sass + '**/*.scss')
    .pipe(using({}))
    .pipe(plumber())
    .pipe(
      sass({
        outputStyle: 'expanded',
        includePaths: paths.root + 'node_modules',
      })
    )
    .on('error', sass.logError)
    .pipe(
      autoprefixer({
        cascade: false,
      })
    )
    .pipe(gulp.dest(paths.css))
    .pipe(
      rename({
        suffix: '.min',
      })
    )
    .pipe(cleanCSS())
    .pipe(gulp.dest(paths.css))
}

// JS task
function js() {
  return gulp
    .src([paths.js + '**/*.js', '!' + paths.js + '**/*.min.js'])
    .pipe(using({}))
    .pipe(
      new terser({
        toplevel: true,
        nameCache: {},
        ecma: 6,
      })
    )
    .pipe(
      rename({
        suffix: '.min',
      })
    )
    .pipe(gulp.dest(paths.js))
}

// Watch files
function watchFiles() {
  gulp.watch(paths.sass + '**/*', css)
  gulp.watch([paths.js + '**/*', '!' + paths.js + '**/*.min.js'], js)
}

// Define complex tasks
const vendor = gulp.series(clean, modules)
const build = gulp.series(vendor, gulp.parallel(css, js))
const watch = gulp.series(build, watchFiles)

// Export tasks
exports.css = css
exports.js = js
exports.clean = clean
exports.vendor = vendor
exports.build = build
exports.watch = watch
exports.default = build
