/* global require, exports */

const babel = require('gulp-babel');
const cleancss = require('gulp-clean-css');
const concat = require('gulp-concat');
const eslint = require('gulp-eslint');
const gulp = require('gulp');
const js = require('gulp-uglify-es').default;
const livereload = require('gulp-livereload');
const notify = require('gulp-notify');
const plumber = require('gulp-plumber');
const scss = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const stylelint = require('gulp-stylelint');

const path_css = 'admin/css/*.css';
const path_dest = './';
const path_git_hooks = '.githooks/*';
const path_js = 'assets/js/**/*.js';
const path_js_admin = 'admin/js/*.js';
const path_scss = 'assets/scss/**/*.scss';
const path_style = 'assets/scss/style.scss';

let error_handler = {
  errorHandler: notify.onError({
    title: 'Gulp',
    message: 'Error: <%= error.message %>'
  })
};

function lint_css() {
  return gulp.src([path_css, path_scss])
    .pipe(plumber(error_handler))
    .pipe(stylelint({
      reporters: [{ formatter: 'string', console: true}]
    }))
    .pipe(livereload());
}

function lint_js() {
  return gulp.src([path_js_admin, path_js])
    .pipe(plumber(error_handler))
    .pipe(eslint())
    .pipe(eslint.format())
    .pipe(eslint.failAfterError())
    .pipe(livereload());
}

function sass() {
  return gulp.src(path_style)
    .pipe(plumber(error_handler))
    .pipe(sourcemaps.init())
    .pipe(scss().on('error', scss.logError))
    .pipe(cleancss({rebase: false}))
    .pipe(sourcemaps.write(path_dest))
    .pipe(gulp.dest(path_dest))
    .pipe(livereload());
}

function uglify() {
  return gulp.src(path_js)
    .pipe(plumber(error_handler))
    .pipe(sourcemaps.init())
    .pipe(concat('main.js'))
    .pipe(babel({
      presets: ['@babel/env']
    }))
    .pipe(js())
    .pipe(sourcemaps.write(path_dest))
    .pipe(gulp.dest(path_dest))
    .pipe(livereload());
}

function watch() {
  livereload.listen({'port': 35729});
  gulp.watch(path_css, gulp.series(lint_css));
  gulp.watch(path_scss, gulp.series(lint_css, sass));
  gulp.watch(path_js, gulp.series(lint_js, uglify));
}

function git_hooks() {
  return gulp.src(path_git_hooks)
    .pipe(plumber(error_handler))
    .pipe(gulp.dest('.git/hooks/', {'mode': '755', 'overwrite': true}))
    .pipe(notify('Copied git hooks'));
}

exports.sass = sass;
exports.uglify = uglify;
exports.watch = watch;
exports.git_hooks = git_hooks;
exports.test = gulp.parallel(lint_css, lint_js);
exports.default = gulp.series(lint_css, lint_js, sass, uglify);
