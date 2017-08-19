var gulp 	= require('gulp');
var uglify 	= require('gulp-uglify');
var rename 	= require('gulp-rename');
 
gulp.task('build', function() {
  return gulp.src('src/jquery.dynamicmaxheight.js')
    .pipe(uglify())
    .pipe(rename('jquery.dynamicmaxheight.min.js'))
    .pipe(gulp.dest('dist'));
});
