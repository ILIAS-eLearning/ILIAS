function debugPrinter(){}

var long_menu_language = {
	'edit':            '[Edit]',
	'type':            'Typ',
	'answers':         'Answers',
	'answer_options':  'Answer Options: ',
	'correct_answers': 'Correct Answers: ',
	'add_answers':     '[Add Answers]',
	'info_text_gap':   '{INFO_TEXT_GAP}',
	'manual_editing':  '{MANUAL_EDITING}'
};

$(document).ready(function () {

	QUnit.module("Array/Object Operations");
	QUnit.test('syncWithHiddenTextField', function (assert) {
		$('body').append('<div id="hidden_text_files"</div>');
		longMenuQuestion.answers = [];
		longMenuQuestion.protected.syncWithHiddenTextField();
		assert.equal('[]', $('#hidden_text_files').attr('value'), 'sync empty value');
		longMenuQuestion.answers = [[1, 2], [3, 4]];
		longMenuQuestion.protected.syncWithHiddenTextField();
		assert.equal('[[1,2],[3,4]]', $('#hidden_text_files').attr('value'), 'sync values');

	});

	QUnit.test('inputFieldsStillPossible', function (assert) {
		longMenuQuestion.answers = [[1, 2, 3, 4, 5]];
		longMenuQuestion.questionParts = {'max_input_fields': 1};

		assert.equal(longMenuQuestion.protected.inputFieldsStillPossible(0), false);
		longMenuQuestion.questionParts.max_input_fields = 10;
		assert.equal(longMenuQuestion.protected.inputFieldsStillPossible(0), true);
		longMenuQuestion.questionParts.max_input_fields = 5;
		assert.equal(longMenuQuestion.protected.inputFieldsStillPossible(0), false);
		longMenuQuestion.questionParts.max_input_fields = 6;
		assert.equal(longMenuQuestion.protected.inputFieldsStillPossible(0), true);
	});

	QUnit.test('removeNonExistingCorrectAnswers', function (assert) {
		longMenuQuestion.answers = [];
		longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
		longMenuQuestion.protected.removeNonExistingCorrectAnswersByKey(0, [1]);
		assert.deepEqual(longMenuQuestion.questionParts.list[0][0], [1, 3]);
		longMenuQuestion.protected.removeNonExistingCorrectAnswersByKey(0, [0, 1]);
		assert.deepEqual(longMenuQuestion.questionParts.list[0][0], []);
		longMenuQuestion.questionParts = {'list': [[[1, 2, 3, 4, 5, 6]]]};
		longMenuQuestion.protected.removeNonExistingCorrectAnswersByKey(0, [0]);
		assert.deepEqual(longMenuQuestion.questionParts.list[0][0], [2, 3, 4, 5, 6]);
		longMenuQuestion.protected.removeNonExistingCorrectAnswersByKey(0, [4]);
		assert.deepEqual(longMenuQuestion.questionParts.list[0][0], [2, 3, 4, 5]);
	});

	QUnit.test('syncWithCorrectAnswers', function (assert) {
		longMenuQuestion.answers = [];
		longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
		longMenuQuestion.protected.syncWithCorrectAnswers(0);
		assert.deepEqual(longMenuQuestion.questionParts.list[0][0], []);

		longMenuQuestion.answers = [[1, 2, 3]];
		longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
		longMenuQuestion.protected.syncWithCorrectAnswers(0);
		assert.deepEqual(longMenuQuestion.questionParts.list[0][0], [1, 2, 3]);

		longMenuQuestion.questionParts = {'list': [[[1, 3]]]};
		longMenuQuestion.protected.syncWithCorrectAnswers(0);
		assert.deepEqual(longMenuQuestion.questionParts.list[0][0], [1, 3]);

	});

	QUnit.test('checkAnswersArray', function (assert) {

		longMenuQuestion.answers = [[]];
		longMenuQuestion.questionParts = {'list': [[[]]]};
		longMenuQuestion.protected.checkAnswersArray(0);
		assert.deepEqual(longMenuQuestion.answers[0], []);

		longMenuQuestion.answers = [[1, 2, 3]];
		longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
		longMenuQuestion.protected.checkAnswersArray(0);
		assert.deepEqual(longMenuQuestion.answers[0], ["1", "2", "3"]);

		longMenuQuestion.answers = [[1, 2, 3, 1]];
		longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
		longMenuQuestion.protected.checkAnswersArray(0);
		assert.deepEqual(longMenuQuestion.answers[0], ["1", "2", "3"]);

		longMenuQuestion.answers = [[1, 2, 3, "", "", "B"]];
		longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
		longMenuQuestion.protected.checkAnswersArray(0);
		assert.deepEqual(longMenuQuestion.answers[0], ["1", "2", "3", "B"]);
	});


	QUnit.test('checkDataConsistency', function (assert) {
		longMenuQuestion.answers = [[]];
		longMenuQuestion.questionParts = {'list': [[[]]]};
		longMenuQuestion.protected.checkDataConsistency([]);
		assert.deepEqual(longMenuQuestion.answers, []);
		assert.deepEqual(longMenuQuestion.questionParts.list, []);
		longMenuQuestion.answers = [[1]];
		longMenuQuestion.questionParts = {'list': [[[1]]]};
		longMenuQuestion.protected.checkDataConsistency([1,2]);
		assert.deepEqual(longMenuQuestion.answers, [[1]]);
		assert.deepEqual(longMenuQuestion.questionParts.list, [[[1]]]);
		longMenuQuestion.questionParts = {
			list 					: [[[1,2,3]],[[1,2,3]],[[1,2,3]]],
		};
		longMenuQuestion.answers = [[1]];
		longMenuQuestion.protected.checkDataConsistency([0,1]);
		assert.deepEqual(longMenuQuestion.answers, [[1]]);
		assert.deepEqual(longMenuQuestion.questionParts.list, [[[1,2,3]]]);
	});

	QUnit.test('sliceInNewQuestionPart', function (assert) {
		longMenuQuestion.answers = [[]];
		longMenuQuestion.questionParts = {'list': [[[]]]};
		longMenuQuestion.protected.sliceInNewQuestionPart(0);
		assert.deepEqual(longMenuQuestion.answers, [[],[]]);
		assert.deepEqual(longMenuQuestion.questionParts.list, [{0:[]},[[]]]);
		longMenuQuestion.questionParts = {
			list 					: [[[1,2,3]]],
			gap_placeholder			: "placeholder",
			last_updated_element 	: 0,
			replacement_word 		: '',
			filereader_usable		: false,
			max_input_fields		: 500
		};
		longMenuQuestion.answers = [[1]];
		longMenuQuestion.protected.sliceInNewQuestionPart(0);
		assert.deepEqual(longMenuQuestion.answers, [[],[1]]);
		assert.deepEqual(longMenuQuestion.questionParts.list, [{0:[]},[[1,2,3]]]);
	});

	QUnit.module("DOM Operations", {
		beforeEach: function () {
			$('.test_dummy').html('');
			longMenuQuestion.questionParts ={
				list 					: {},
				gap_placeholder			: {},
				last_updated_element 	: 0,
				replacement_word 		: 'replacement_word',
				filereader_usable		: false,
				max_input_fields		: 500
			};
		},
		afterEach:  function () {

		}
	});
	QUnit.test('appendSelectBox', function () {
		ok($('#select_type_0').length == 0, "select box element does not exists");
		longMenuQuestion.protected.appendSelectBox($('.test_dummy'), 0);
		ok($('#select_type_0').length != 0, "select box element exists");
	});

	QUnit.test('appendPointsField', function () {
		ok($('#points_0').length == 0, "points element does not exists");
		longMenuQuestion.protected.appendPointsField($('.test_dummy'), 0);
		ok($('#points_0').length != 0, "points element exists");
	});

	QUnit.test('buildAnswerOverview', function (assert) {
		longMenuQuestion.answers = [[]];
		longMenuQuestion.questionParts = {'list': [[[1, 2]]]};
		var html = longMenuQuestion.protected.buildAnswerOverview(0);
		assert.equal(html, ' <a data-id=\"0\" class=\"answer_options\">[Add Answers]</a></p>');
		longMenuQuestion.answers = [[1, 2]];
		html = longMenuQuestion.protected.buildAnswerOverview(0);
		assert.equal(html, '<p>Answer Options: 2 <a data-id=\"0\" class=\"answer_options\"> [Edit]</a></p><p>Correct Answers:  1, 2<a data-id=\"0\" class=\"correct_answers\"> [Edit]</a></p>');
	});
	QUnit.test('appendUploadButtons', function () {
		var html = longMenuQuestion.protected.appendUploadButtons();
		ok($('#layout_dummy_buttons').length == 1 );
	});

	QUnit.test('appendAnswersOverview', function (assert) {
		longMenuQuestion.answers = [[1, 2, 3]];
		longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
		ok($('#answer_overview_0').length == 0 );
		longMenuQuestion.protected.appendAnswersOverview($('.test_dummy'), 0);
		ok($('#answer_overview_0').length == 1 );
	});

	QUnit.test('appendModalTitle', function (assert) {
		longMenuQuestion.protected.appendModalTitle('text' , 0);
		assert.equal($('.modal-title').html() , 'replacement_word 1 text' );
		longMenuQuestion.protected.appendModalTitle('hello' , 5);
		assert.equal($('.modal-title').html() , 'replacement_word 6 hello' );
	});

	QUnit.test('recalculateAnswerListDataIds', function (assert) {
		
		$('.test_dummy').append('<div class="answerlist" data-id="5"></div>');
		longMenuQuestion.protected.recalculateAnswerListDataIds();
		assert.equal($('.answerlist').attr('data-id') , '0' );
		$('.test_dummy').append('<div class="answerlist" data-id="5"></div>');
		$('.test_dummy').append('<div class="answerlist" data-id="5"></div>');
		longMenuQuestion.protected.recalculateAnswerListDataIds();
		assert.equal($('.answerlist').eq(1).attr('data-id') , '1' );
	});

	QUnit.test('appendFormParts', function (assert) {

		assert.equal($('.test_dummy').html() , '' );
		$('.test_dummy').append('<div class="ilFormFooter"></div>');
		longMenuQuestion.answers = [[1, 2, 3]];
		longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
		longMenuQuestion.protected.appendFormParts();
		assert.notEqual($('.test_dummy').html() , '' );
	});

	QUnit.test('redrawFormParts', function (assert) {

		assert.equal($('.test_dummy').html() , '' );
		$('.test_dummy').append('<div class="longmenu"></div>');
		assert.equal($('.test_dummy').find('.longmenu').length , 1 );
		longMenuQuestion.protected.redrawFormParts();
		assert.equal($('.test_dummy').html() , '' );
	});

	QUnit.test('redrawAnswerList', function (assert) {

		longMenuQuestion.answers = [[1, 2, 3]];
		longMenuQuestion.questionParts = {
			'list': [[[1, 2, 3]]],
			max_input_fields: 5
		};
		longMenuQuestion.protected.redrawAnswerList(0);
		assert.equal($('.answerlist').length , 3 );
		longMenuQuestion.answers = [[1, 2, 3,4,5,6]];
		longMenuQuestion.questionParts = {
			'list': [[[1, 2, 3,4,5,6]]],
			max_input_fields: 5
		};
		longMenuQuestion.protected.redrawAnswerList(0);
		assert.equal($('.modal_answer_options textarea').html() , "1\n2\n3\n4\n5\n6\n" );
	});

	QUnit.test('redrawAnswerListFast', function (assert) {

		assert.equal($('.answerlist').length , 0 );
		longMenuQuestion.answers = [[1, 2, 3, 123]];
		longMenuQuestion.questionParts = {
			'list': [[[1, 2, 3]]],
			max_input_fields: 5
		};
		longMenuQuestion.protected.redrawAnswerList(0);
		assert.equal($('.answerlist').length , 4 );
		longMenuQuestion.protected.redrawAnswerListFast(0, 3, true);
		assert.equal($('.answerlist').length , 5 );
		longMenuQuestion.protected.redrawAnswerListFast(0, 3, false);
		assert.equal($('.answerlist').length , 4 );

	});
	
	QUnit.module("Events", {
		beforeEach: function () {
			$('.test_dummy').html('');
			longMenuQuestion.questionParts ={
				list 					: {},
				gap_placeholder			: {},
				last_updated_element 	: 0,
				replacement_word 		: 'replacement_word',
				filereader_usable		: false,
				max_input_fields		: 500
			};
		},
		afterEach:  function () {
			$('.test_dummy').html('');
		}
	});
	QUnit.test('addEditListeners', function (assert) {
		longMenuQuestion.answers = [[1, 2, 3]];
		longMenuQuestion.questionParts = {'list': [[[1, 2]]]};
		var html = longMenuQuestion.protected.buildAnswerOverview(0);
		$('.test_dummy').html(html);
		assert.equal($._data( $(".correct_answers")[0], "events"), undefined, 'no click event on correct answer');
		assert.equal($._data( $(".answer_options")[0], "events"), undefined, 'no click event on answer options');
		longMenuQuestion.protected.addEditListeners();
		assert.equal($._data( $(".correct_answers")[0], "events")['click'].length, 1, 'click event on correct answer');
		assert.equal($._data( $(".answer_options")[0], "events")['click'].length, 1, 'click event on answer options')
	});

	QUnit.test('appendModalCloseListener', function (assert) {
		assert.equal($._data( $("#ilGapModal")[0], "events"), undefined, 'no hidden event on ilGapModal');
		longMenuQuestion.protected.appendModalCloseListener();
		assert.equal($._data( $("#ilGapModal")[0], "events")['hidden'].length, 1, 'hidden event on ilGapModal')
	});

	QUnit.test('appendSaveModalButtonEvent', function (assert) {
		$('.save-modal').remove();
		$('.test_dummy').append('<div class="save-modal"></div>');
		assert.equal($._data( $(".save-modal")[0], "events"), undefined, 'no save event on ilGapModal');
		longMenuQuestion.protected.appendSaveModalButtonEvent();
		assert.equal($._data( $(".save-modal")[0], "events")['click'].length, 1, 'save event on ilGapModal');
	});

	QUnit.test('appendAbstractModalButtonEvent', function (assert) {
		$('.testing').remove();
		$('.test_dummy').append('<div class="testing"></div>');
		assert.equal($._data( $(".testing")[0], "events"), undefined, 'no event');
		longMenuQuestion.protected.appendAbstractModalButtonEvent('.testing', '');
		assert.equal($._data( $(".testing")[0], "events")['click'].length, 1, 'click event')
	});

	QUnit.test('appendCancelModalButtonEvent', function (assert) {
		$('.cancel-modal').remove();
		$('.test_dummy').append('<div class="cancel-modal"></div>');
		assert.equal($._data( $(".cancel-modal")[0], "events"), undefined, 'no event');
		longMenuQuestion.protected.appendCancelModalButtonEvent('.testing', '');
		assert.equal($._data( $(".cancel-modal")[0], "events")['click'].length, 1, 'click event')
	});

	QUnit.test('appendAbstractCloneButtonEvent', function (assert) {
		$('.appendAbstractCloneButtonEvent').remove();
		$('.test_dummy').append('<div class="appendAbstractCloneButtonEvent"></div>');
		assert.equal($._data( $(".appendAbstractCloneButtonEvent")[0], "events"), undefined, 'no event');
		longMenuQuestion.protected.appendAbstractCloneButtonEvent('.appendAbstractCloneButtonEvent', '');
		assert.equal($._data( $(".appendAbstractCloneButtonEvent")[0], "events")['click'].length, 1, 'click event')
	});

	QUnit.test('appendAddButtonEvent', function (assert) {
		$('.clone_fields_add').remove();
		$('.test_dummy').append('<div class="clone_fields_add"></div>');
		assert.equal($._data( $(".clone_fields_add")[0], "events"), undefined, 'no event');
		longMenuQuestion.protected.appendAddButtonEvent();
		assert.equal($._data( $(".clone_fields_add")[0], "events")['click'].length, 1, 'click event')
	});

	QUnit.test('appendRemoveButtonEvent', function (assert) {
		$('.clone_fields_remove').remove();
		$('.test_dummy').append('<div class="clone_fields_remove"></div>');
		assert.equal($._data( $(".clone_fields_remove")[0], "events"), undefined, 'no event');
		longMenuQuestion.protected.appendRemoveButtonEvent();
		assert.equal($._data( $(".clone_fields_remove")[0], "events")['click'].length, 1, 'click event')
	});

	QUnit.module("Helper", {
		beforeEach: function () {
		},
		afterEach:  function () {

		}
	});
	QUnit.test('benchmarkCallsDummyNotForUsage', function (assert) {
		var t0 = longMenuQuestion.protected.benchmarkCallsDummyNotForUsage('testing');
		assert.notEqual(t0, 0);
		var t1 = longMenuQuestion.protected.benchmarkCallsDummyNotForUsage('testing');
		ok(t1 > t0);
		var t2 = longMenuQuestion.protected.benchmarkCallsDummyNotForUsage('testing', t0);
		assert.notEqual(t2, 0);

	});

	QUnit.module("Public functions", {
		beforeEach: function () {
			$('.test_dummy').html('');
			longMenuQuestion.questionParts ={
				list 					: {},
				gap_placeholder			: {},
				last_updated_element 	: 0,
				replacement_word 		: 'replacement_word',
				filereader_usable		: false,
				max_input_fields		: 500
			};
		},
		afterEach:  function () {

		}
	});

	QUnit.test('Init', function (assert) {
	//	var GapInsertingWizard = '';
	//	var t0 = longMenuQuestion.Init();
	//	assert.notEqual(t2, 0);
	// Todo implement test
	});
});