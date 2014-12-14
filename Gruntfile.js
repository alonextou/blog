module.exports = function(grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    copy: {
      main: {
        cwd: 'app/',
        src: 'js/**/*',
        dest: 'public/assets/',
        expand: true
      },
    },

    sass: {
      options: {
        includePaths: [
          'bower_components/foundation/scss',
          'bower_components/font-awesome/scss',
          'bower_components/slick.js/slick'
        ],
      },
      dist: {
        options: {
          outputStyle: 'compressed'
        },
        files: {
          'public/assets/css/app.css': 'app/scss/app.scss'
        }        
      }
    },

    watch: {
      grunt: { files: ['Gruntfile.js'] },

      sass: {
        files: 'app/scss/**/*.scss',
        tasks: ['sass']
      }
    }
  });

  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-copy');

  grunt.registerTask('build', ['sass']);
  grunt.registerTask('default', ['build','watch']);
}
