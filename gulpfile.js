'use strict';

var gulp = require('gulp');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var mainBowerFiles = require('main-bower-files');
var rename = require('gulp-rename');
var sass = require('gulp-sass');
var cssmin = require('gulp-cssmin');
var imagemin = require('gulp-imagemin');
var autoprefixer = require('gulp-autoprefixer');
var sourcemaps = require('gulp-sourcemaps');

gulp.task('bower-scripts', function() {
  return gulp.src(mainBowerFiles({filter: '**/*.js'}))
    .pipe(concat('libs.js'))
    .pipe(gulp.dest('dist/scripts'))
    .pipe(rename({ suffix: '.min' }))
    .pipe(uglify())
    .pipe(gulp.dest('dist/scripts'));
});

gulp.task('scripts', function () {
  return gulp.src('assets/scripts/script.js')
    .pipe(rename({ suffix: '.min' }))
    .pipe(uglify())
    .pipe(gulp.dest('dist/scripts'));
});

gulp.task('bower-styles', function() {
  return gulp.src(mainBowerFiles({filter: '**/*.css'}))
    .pipe(concat('libs.css'))
    .pipe(gulp.dest('dist/styles'))
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.init())
    .pipe(cssmin())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('dist/styles'));
});

gulp.task('styles', function () {
  return gulp.src('assets/styles/**/*.scss')
    .pipe(sass({sourcemap: true, outputStyle: 'expanded'}))
    .pipe(autoprefixer())
    .pipe(gulp.dest('dist/styles'))
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.init())
    .pipe(cssmin())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('dist/styles'));
});

gulp.task('bower-images', function() {
  return gulp.src(mainBowerFiles('**/*.{gif,png,jpg,jpeg}'))
    .pipe(imagemin({
      progressive: true,
      interlaced: true,
      svgoPlugins: [{removeUnknownsAndDefaults: false}, {cleanupIDs: false}]
    }))
    .pipe(gulp.dest('dist/images'));
});

gulp.task('images', function() {
  return gulp.src('assets/images/*')
    .pipe(imagemin({
      progressive: true,
      interlaced: true,
      svgoPlugins: [{removeUnknownsAndDefaults: false}, {cleanupIDs: false}]
    }))
    .pipe(gulp.dest('dist/images'));
});

gulp.task('bower-fonts', function () {
  return gulp.src(mainBowerFiles('**/*.{eot,svg,ttf,woff,woff2}'))
    .pipe(gulp.dest('dist/fonts'));
});

gulp.task('watch', function() {
  gulp.watch(['assets/styles/**/*'], ['styles']);
  gulp.watch(['assets/scripts/script.js'], ['scripts']);
  gulp.watch(['assets/image/**/*'], ['images']);
});

gulp.task('default', function() {
  gulp.start([
    'bower-scripts',
    'bower-styles',
    'bower-images',
    'bower-fonts',
    'scripts',
    'styles',
    'images',
  ]);
});
