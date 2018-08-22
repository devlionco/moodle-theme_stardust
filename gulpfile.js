"use strict";

var gulp = require("gulp");
var sass = require("gulp-sass");
var rename = require("gulp-rename");
var plumber = require("gulp-plumber");
var postcss = require("gulp-postcss");
var autoprefixer = require("autoprefixer");
var mqpacker = require("css-mqpacker");
var csso = require("gulp-csso");
var sequence = require("gulp-sequence");
var del = require("del");
var minify = require('gulp-minify');

var svgstore = require('gulp-svgstore');
var svgmin = require('gulp-svgmin');
var path = require('path');
var inject = require('gulp-inject');
var changeCase = require('change-case');
var rsp = require('remove-svg-properties');

// sharingactivities - style
gulp.task("clean", function() {
  del("style/stardust.css");
});

gulp.task("style", function() {
  gulp.src("scss/style.scss")
    .pipe(plumber())
    .pipe(sass())
    .pipe(postcss([
      autoprefixer({browsers: ["last 2 versions"]}),
      mqpacker({sort: true})
    ]))
    .pipe(rename("stardust.css"))
    .pipe(gulp.dest('style'));
    // .pipe(csso())
    // .pipe(rename("style.css"))
    // .pipe(gulp.dest("../blocks/search_custom/css"))
    // .pipe(server.stream());
});


gulp.watch("scss/**/*.{scss,sass}", ["style"]);

// gulp.task("build", function(end) {
//   sequence("clean","style", end);
// });

// ***************** svgStore  ********************** //


gulp.task('toLowerCase', function(){
  del('src/svg-use/*');
  return gulp.src('src/svg/*')
  .pipe(rename(function(path) {
     path.basename = changeCase.lowerCase(path.basename);
     path.extname = changeCase.lowerCase(path.extname);
   }))
   .pipe(gulp.dest('src/svg-use'));

});

gulp.task('removePropSVG', function(){

  rsp.remove({
      src: 'src/svg-use/*.svg',
      out: 'src/svg-use',
      stylesheets: false,
      properties: [rsp.PROPS_STROKE, rsp.PROPS_FILL]
  });

});

gulp.task('svgstore', function () {

  del(['src/sprite/*']);

  return gulp.src('src/svg-use/*.svg')
        .pipe(svgmin(function (file) {
            var prefix = path.basename(file.relative, path.extname(file.relative));
            return {
                plugins: [{
                    cleanupIDs: {
                        prefix: prefix + '-',
                        minify: true
                    }
                }]
            }
        }))
        .pipe(svgstore({ inlineSvg: true }))
        .pipe(gulp.dest('src/sprite'));

});

gulp.task('injection', function () {

  // del(['./templates/header.*']);

  var src = gulp.src(['src/sprite/*.svg']);
  function fileContents (filePath, file) {
      return file.contents.toString();
  }

  return gulp.src('src/header.*')
    .pipe(inject(src, {
      starttag: '<!-- inject:{{ext}} -->',
      transform: fileContents
    }))
    .pipe(gulp.dest('./templates'));

});


gulp.task("build", function(cb) {
  sequence (
    'clean',
    // 'toLowerCase',
    // 'removePropSVG',
    // 'svgstore',
    // 'injection',
    'style',
    cb
  );
});
