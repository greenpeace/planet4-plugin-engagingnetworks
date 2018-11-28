/* global require */

const gulp = require('gulp');
const stylelint = require('gulp-stylelint');
const eslint = require('gulp-eslint');
const uglify = require('gulp-uglify-es').default;
const concat = require('gulp-concat');
const sass = require('gulp-sass');
const cleancss = require('gulp-clean-css');
const sourcemaps = require('gulp-sourcemaps');
const notify = require('gulp-notify');
const plumber = require('gulp-plumber');
const livereload = require('gulp-livereload');

const path_js = 'assets/js/**/*.js';
const path_scss = 'assets/scss/**/*.scss';
const path_style = 'assets/scss/style.scss';
const path_dest = './';

let error_handler = {
  errorHandler: notify.onError({
    title: 'Gulp',
    message: 'Error: <%= error.message %>'
  })
};

gulp.task('css:lint', () => {
  return gulp.src(path_scss)
    .pipe(plumber(error_handler))
    .pipe(stylelint({
      reporters: [{ formatter: 'string', console: true}]
    }))
    .pipe(livereload());
});

gulp.task('js:lint', () => {
  return gulp.src(path_js)
    .pipe(plumber(error_handler))
    .pipe(eslint())
    .pipe(eslint.format())
    .pipe(eslint.failAfterError())
    .pipe(livereload());
});

gulp.task('sass', function () {
  return gulp.src(path_style)
    .pipe(plumber(error_handler))
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(cleancss({rebase: false}))
    .pipe(sourcemaps.write(path_dest))
    .pipe(gulp.dest(path_dest))
    .pipe(livereload());
});

gulp.task('uglify', function(){
  return gulp.src(path_js)
    .pipe(plumber(error_handler))
    .pipe(sourcemaps.init())
    .pipe(concat('main.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write(path_dest))
    .pipe(gulp.dest(path_dest))
    .pipe(livereload());
});

gulp.task('watch', function () {
  livereload.listen({'port': 35729});
  gulp.watch(path_scss, ['css:lint', 'sass']);
  gulp.watch(path_js, ['js:lint', 'uglify']);
});

gulp.task('test', function() {
  gulp.start('css:lint');
  gulp.start('js:lint');
});

gulp.task('default', function() {
  gulp.start('test');
  gulp.start('sass');
  gulp.start('uglify');
});
