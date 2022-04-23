/* jshint node: true */

/**
 * GruntJS task manager config.
 */
module.exports = function( grunt ) {

	'use strict';

	var request = require( 'request' ),
		sass    = require( 'node-sass' );

	/**
	 * Init config.
	 */
	grunt.initConfig( {

		// Get access to package.json.
		pkg: grunt.file.readJSON( 'package.json' ),

		// Setting paths.
		source: {
			sass:   'resources/sass',
			js:     'resources/js',
			deploy: 'deploy' // Hint: Create a ./deploy/ folder and ignore it.
		},

		dist: {
			css:    'assets/css',
			js:     'assets/js',
			dist:   'assets/dist',
		},

		// Minify .js files.
		uglify: {
			options: {
				preserveComments: false
			},
			frontend: {
				files: [ {
					expand: true,
					cwd: '<%= dist.js %>/frontend/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: '<%= dist.js %>/frontend/',
					ext: '.min.js'
				} ]
			},
			dist_frontend: {
				files: [ {
					expand: true,
					cwd: '<%= dist.dist %>/frontend',
					src: [
						'*.js',
						'!*.min.js',
						'!*.php'
					],
					dest: '<%= dist.dist %>/frontend',
					ext: '.min.js'
				} ]
			}
		},

		// Compile SASS.
		sass: {
			dev: {
				options: {
					implementation: sass,
					sourceMap: true,
					outputStyle: 'expanded'
				},
				files: [ {
					expand: true,
					cwd: '<%= source.sass %>/frontend',
					src: [
						'styles.scss'
					],
					dest: '<%= dist.css %>/frontend',
					ext: '.css'
				} ]
			},
			dist: {
				options: {
					implementation: sass,
					sourceMap: false,
					outputStyle: 'compressed'
				},
				files: [ {
					expand: true,
					cwd: '<%= source.sass %>/frontend',
					src: [
						'styles.scss'
					],
					dest: '<%= dist.css %>/frontend',
					ext: '.css'
				} ]
			}
		},

		// Convert to RTL
		rtlcss: {
			main: {
				expand: true,
				ext: '-rtl.css',
				src: [
					'assets/css/frontend/styles.css'
				]
			}
		},

		// Check textdomain errors.
		checktextdomain: {
			options:{
				text_domain: [ 'woocommerce-checkout-integration-example', 'woocommerce' ],
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src:  [
					'**/*.php', // Include all files
					'!deploy/**', // Exclude deploy.
					'!vendor/**', // Exclude vendor.
					'!node_modules/**' // Exclude node_modules/
				],
				expand: true
			}
		},

		exec: {
			options: {
				shell: '/bin/bash'
			},
			npm_build: {
				cmd: function() {
					grunt.log.ok( 'Running `npm run build and i18n`...' );
					return 'npm run build && npm run i18n';
				}
			}
		},

		copy: {
			resources: {
				files: [
					{
						expand: true,
						src: [ '<%= source.js %>/frontend/*.js' ],
						dest: '<%= dist.js %>/frontend',
						flatten: true,
						filter: 'isFile'
					},
				]
			}
		}
	} );

	// Load NPM tasks to be used here.
	grunt.loadNpmTasks( 'grunt-checktextdomain' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-rtlcss' );
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-exec' );

	/**
	 * Custom Tasks.
	 */

	grunt.registerTask( 'build', [
		'exec:npm_build',
		'copy',
		'uglify',
		'sass:dist',
		'rtlcss',
	] );
};
