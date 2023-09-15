// Karma configuration
// Generated on Fri Aug 21 2015 15:40:37 GMT+0200 (CEST)

module.exports = function (config) {
	config.set({

		// base path that will be used to resolve all patterns (eg. files, exclude)
		basePath: '',

		// frameworks to use
		// available frameworks: https://npmjs.org/browse/keyword/karma-adapter
		frameworks: ['jasmine-jquery', 'jasmine'],


		// list of files / patterns to load in the browser
		files: [
			'../../../../Services/jQuery/js/2_1_4/jquery.js',
			'../../../../Services/RTE/tiny_mce_3_5_11/tiny_mce.js', '../../../../Services/RTE/tiny_mce_3_5_11/langs/en.js', '../../../../Services/RTE/tiny_mce_3_5_11/themes/advanced/editor_template.js', '../../../../Services/RTE/tiny_mce_3_5_11/themes/advanced/skins/default/ui.css', '../../../../Services/RTE/tiny_mce_3_5_11/themes/advanced/skins/default/content.css',
			//'../../templates/default/cloze_gap_builder.js', 'cloze_gap_builderTest.js', 
			'../../templates/default/gapInsertingWizard.js', 'gapInsertingWizardTest.js',
			'../../templates/default/longMenuQuestion.js', 'longMenuQuestionTest.js',
			'spec/javascripts/fixtures/gapInsertingWizard.html',
			'spec/javascripts/fixtures/longMenuQuestion.html',
			{pattern: 'spec/javascripts/fixtures/*'}
		],


		// list of files to exclude
		exclude: [],


		// test results reporter to use
		// possible values: 'dots', 'progress'
		// available reporters: https://npmjs.org/browse/keyword/karma-reporter
		reporters: ['progress', 'coverage'],

		preprocessors: {
			'../../templates/default/gapInsertingWizard.js': ['coverage'],
			'../../templates/default/longMenuQuestion.js':   ['coverage']
		},


		// web server port
		port: 9876,

		// enable / disable colors in the output (reporters and logs)
		colors: true,


		// level of logging
		// possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
		logLevel: config.LOG_INFO,


		// enable / disable watching file and executing tests whenever any file changes
		autoWatch: true,


		// start these browsers
		// available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
		browsers: ['PhantomJS'],


		// Continuous Integration mode
		// if true, Karma captures browsers, runs the tests and exits
		singleRun: false
	})
};
