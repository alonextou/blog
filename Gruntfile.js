module.exports = function(grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    cfg: grunt.file.readJSON('config.json'),

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
      },

      js: {
        files: 'app/js/**/*.js',
        tasks: ['copy']
      }
    },

    shipit: {
      options: {
        workspace: '<%= cfg.workspace %>',
        deployTo: '<%= cfg.deployTo %>',
        repositoryUrl: '<%= pkg.repository.url %>',
        ignores: ['.git', 'node_modules'],
        keepReleases: 2
      },
      production: {
        servers: ['<%= cfg.user %>@<%= cfg.production %>']
      }
    }
  });

  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-shipit');
  grunt.loadNpmTasks('shipit-deploy');

  grunt.registerTask('build', ['sass']);
  grunt.registerTask('default', ['build', 'watch']);
}
