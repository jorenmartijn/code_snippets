var gulp = require('gulp'),
    path = require('path'),
    wpPot = require('gulp-wp-pot'),
    compass = require('gulp-compass'),
    notify = require('gulp-notify'),
    sort = require('gulp-sort'),
    cssnano = require('gulp-cssnano'),
    cssmin = require('gulp-cssmin'),
    rename = require('gulp-rename'),
    elixir = require('laravel-elixir'),
    autoprefixer = require('gulp-autoprefixer');


var settings = {
    assets: "assets",
    sass: "sass",
    css: "css",
    js: "js",
    scripts: "scripts",
    lang: "lang",
    langDomain: 'tusken',
    langFiles: ['./*.php', './includes/*.php', './parts/**/**/*.php'],
    jsFiles: './assets/scripts/src/app.js'
};
var paths = {
    css: path.join(settings.assets, settings.css),
    sass: path.join(settings.assets, settings.sass),
    js: path.join(settings.assets, settings.js),
    scripts: path.join(settings.assets, settings.scripts),
    lang: path.join(settings.assets, settings.lang)
};
elixir.config.assetsDir = './'+paths.scripts+'/';
elixir.config.assetsPath = './'+paths.js+'/';

gulp.task('debug', function(){
    console.log(paths);
});


// Add default icon to notifications
function notice(options){
    var params = options;
    params['icon'] = path.join(__dirname, "favicon.png");
    return notify(params);
}

gulp.task('translate', function () { // Generate translation file
    return gulp.src(settings.langFiles)
        .pipe(sort())
        .pipe(wpPot( {
            domain: settings.langDomain,
            destFile: paths.lang,
            package: 'tuskendemarren_wordpress_theme',
        } ))
        .pipe(gulp.dest('./'))
        .pipe(notice({ message: 'Translations generated'}));
});

gulp.task('cssmin', function(){ // Minify CSS
    return gulp.src(paths.css+'/app.css')
        .pipe(autoprefixer('last 2 version'))
        .pipe(cssmin())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(paths.css));

});

gulp.task('compass', function() {   // Use Compass to compile SCSS files
  return gulp.src(paths.sass+'/app.scss')

    .pipe(compass({
      project: path.join(__dirname, settings.assets),
      css: settings.css,
      sass: settings.sass
    }))
    .pipe(gulp.dest('./'+settings.css+'/'))
    .pipe(notice({ message: 'Styles task complete'}));
});

// Standard tasks
gulp.task('default', ['styles', 'translate']);  // Default task
gulp.task('styles', ['compass', 'cssmin']);     // Styles related task
gulp.task('watch', function(){                  // Watch task
    gulp.watch('./'+settings.assets+'/'+settings.sass+'/**.scss', ['styles', 'cssmin']);
});