const gulp = require('gulp');
const plumber = require('gulp-plumber');
const sass = require('gulp-sass');
const rsass = require('gulp-ruby-sass');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const sourcemaps = require('gulp-sourcemaps');
const groupmq = require('gulp-group-css-media-queries');
const bs = require('browser-sync');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var path = require('path');
var jsImport = require('gulp-js-import');
let cleanCSS = require('gulp-clean-css');

const SASS_SOURCES = [
  'assets/scss/*.scss',
  'assets/scss/*.sass'
];

const JS_SOURCES = [
  'assets/sjs/*.js',
];

/**
 * Compile Sass files
 */
gulp.task('compile:sass', () =>
  rsass(SASS_SOURCES,{ base: './', style: 'expanded', verbose: true })
    .on('error', sass.logError)
    .pipe(plumber())
    .pipe(postcss([
      autoprefixer({
        browsers: ['last 2 versions'],
        cascade: false,
      })
    ]))
    .pipe(rename(function(file) {
        file.dirname = './';
        file.basename = file.basename + '.min';
        return file;
     }))
    .pipe(concat('app.min.css',{newLine: "\n\n\n"}))
    .pipe(groupmq())
    .pipe(cleanCSS({level: {1: {specialComments: 0}}}))
    .pipe(gulp.dest('assets/css')) );

gulp.task('watch:sass', ['compile:sass'], () => {
  gulp.watch(SASS_SOURCES, ['compile:sass']);
});

gulp.task('compile:js', () => {
  gulp.src(JS_SOURCES)
    .pipe(jsImport({hideConsole: false}))
    .pipe(concat('app.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('assets/js'));
});

gulp.task('watch:js', ['compile:js'], () => {
  gulp.watch(JS_SOURCES, ['compile:js']);
});

gulp.task('compile',['compile:sass','compile:js'], () => {});

gulp.task('watch',['watch:sass','watch:js'], () => {});

gulp.task('default',['watch'], () => {});