if (typeof window.__karma__ === 'undefined') {
	var $j = $;
}


function debugPrinter(){}
var ilBootstrapTaggingOnLoad = {};
ilBootstrapTaggingOnLoad.Init = function(){}
$.fn.tagsinput = function(){};
var ilTinyMceInitCallbackRegistry = {addCallback : function(){}};

var performance = {
	now : function(){}
};
describe("LongMenuQuestion", function() {
	beforeEach(function () {
		loadFixtures('longMenuQuestion.html');
		jQuery.fn.extend({
			modal: function () {
			}
		});
		window.long_menu_language = {
			'edit':            '[Edit]',
			'type':            'Typ',
			'answers':         'Answers',
			'answer_options':  'Answer Options: ',
			'correct_answers': 'Correct Answers: ',
			'add_answers':     '[Add Answers]',
			'info_text_gap':   '{INFO_TEXT_GAP}',
			'manual_editing':  '{MANUAL_EDITING}'
		};

		window.longMenuQuestion.questionParts ={
			list 					: {},
			gap_placeholder			: {},
			last_updated_element 	: 0,
			replacement_word 		: 'replacement_word',
			filereader_usable		: false,
			max_input_fields		: 500
		};

		/*window.GapInsertingWizard = (function () {
			var pub = {}, cursorPos;
			pub.Init = function () {
			};
			pub.done = true;
			return pub;
		}());*/
		$('.test_dummy').html('');
	});

	describe("syncWithHiddenTextField", function() {
		beforeEach(function () {
			$('body').append('<div id="hidden_text_files"</div>');
		});

		it("should be empty when sync with no value", function () {
			longMenuQuestion.answers = [];
			longMenuQuestion.protect.syncWithHiddenTextField();
			expect('[]').toEqual($('#hidden_text_files').attr('value'));
		});
	
		it("should contain the answer values", function () {
			longMenuQuestion.answers = [[1, 2], [3, 4]];
			longMenuQuestion.protect.syncWithHiddenTextField();
			expect('[[1,2],[3,4]]').toEqual( $('#hidden_text_files').attr('value'));
		});
	});
	describe("inputFieldsStillPossible", function() {
		beforeEach(function () {
			longMenuQuestion.answers = [[1, 2, 3, 4, 5]];
			longMenuQuestion.questionParts = {'max_input_fields': 1};
		});
		
		it("should return true if input fields can be displayed", function () {
			longMenuQuestion.questionParts.max_input_fields = 10;
			expect(longMenuQuestion.protect.inputFieldsStillPossible(0)).toEqual(true);
			longMenuQuestion.questionParts.max_input_fields = 6;
			expect(longMenuQuestion.protect.inputFieldsStillPossible(0)).toEqual(true);
		});
		
		it("should return false if input fields can not be displayed", function () {
			expect(longMenuQuestion.protect.inputFieldsStillPossible(0)).toEqual(false);
			longMenuQuestion.questionParts.max_input_fields = 5;
			expect(longMenuQuestion.protect.inputFieldsStillPossible(0)).toEqual(false);
		});
	});
	describe("removeNonExistingCorrectAnswersByKey", function() {
		beforeEach(function () {
			longMenuQuestion.answers = [];
		});
	
		it("should remove non existing correct answers", function () {
			longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
			longMenuQuestion.protect.removeNonExistingCorrectAnswersByKey(0, [1]);
			expect(longMenuQuestion.questionParts.list[0][0]).toEqual([1, 3]);
			longMenuQuestion.protect.removeNonExistingCorrectAnswersByKey(0, [0, 1]);
			expect(longMenuQuestion.questionParts.list[0][0]).toEqual([]);
			longMenuQuestion.questionParts = {'list': [[[1, 2, 3, 4, 5, 6]]]};
			longMenuQuestion.protect.removeNonExistingCorrectAnswersByKey(0, [0]);
			expect(longMenuQuestion.questionParts.list[0][0]).toEqual([2, 3, 4, 5, 6]);
			longMenuQuestion.protect.removeNonExistingCorrectAnswersByKey(0, [4]);
			expect(longMenuQuestion.questionParts.list[0][0]).toEqual([2, 3, 4, 5]);
		});
	});

	describe("syncWithCorrectAnswers", function() {
		beforeEach(function () {
			longMenuQuestion.answers = [];
		});

		it("should remove correct answers which do not exist anymore", function () {
			longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
			longMenuQuestion.protect.syncWithCorrectAnswers(0);
			expect(longMenuQuestion.questionParts.list[0][0]).toEqual([]);
		});
		it("should do nothing if correct answers still exists", function () {
			longMenuQuestion.answers = [[1, 2, 3]];
			longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
			longMenuQuestion.protect.syncWithCorrectAnswers(0);
			expect(longMenuQuestion.questionParts.list[0][0]).toEqual([1, 2, 3]);
		});
		it("should remove single correct answers which does not exist anymore", function () {
			longMenuQuestion.answers = [[1, 2, 3]];
			longMenuQuestion.questionParts = {'list': [[[1, 3]]]};
			longMenuQuestion.protect.syncWithCorrectAnswers(0);
			expect(longMenuQuestion.questionParts.list[0][0]).toEqual([1, 3]);
		});
	});


	describe("checkAnswersArray", function() {
		beforeEach(function () {
			longMenuQuestion.answers = [[]];
			longMenuQuestion.questionParts = {'list': [[[]]]};
		});

		it("should do nothing if arrays are empty", function () {
			longMenuQuestion.protect.checkAnswersArray(0);
			expect(longMenuQuestion.answers[0]).toEqual([]);
		});

		it("should do nothing if arrays are correct", function () {
			longMenuQuestion.answers = [[1, 2, 3]];
			longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
			longMenuQuestion.protect.checkAnswersArray(0);
			expect(longMenuQuestion.answers[0]).toEqual(["1", "2", "3"]);
		});
		
		it("should remove duplicated entries", function () {
			longMenuQuestion.answers = [[1, 2, 3, 1]];
			longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
			longMenuQuestion.protect.checkAnswersArray(0);
			expect(longMenuQuestion.answers[0]).toEqual(["1", "2", "3"]);
		});

		it("should remove emtpy entries", function () {
			longMenuQuestion.answers = [[1, 2, 3, "", "", "B"]];
			longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
			longMenuQuestion.protect.checkAnswersArray(0);
			expect(longMenuQuestion.answers[0]).toEqual(["1", "2", "3", "B"]);
		});
	});

	describe("checkDataConsistency", function() {
		beforeEach(function () {
			longMenuQuestion.answers = [[]];
			longMenuQuestion.questionParts = {'list': [[[]]]};
		});

		it("should do nothing if arrays are empty", function () {
			longMenuQuestion.protect.checkDataConsistency([]);
			expect(longMenuQuestion.answers).toEqual([]);
			expect(longMenuQuestion.questionParts.list).toEqual([]);
		});

		it("should do nothing if given array is bigger", function () {
			longMenuQuestion.answers = [[1]];
			longMenuQuestion.questionParts = {'list': [[[1]]]};
			longMenuQuestion.protect.checkDataConsistency([1,2]);
			expect(longMenuQuestion.answers).toEqual([[1]]);
			expect(longMenuQuestion.questionParts.list).toEqual([[[1]]]);
		});

		it("should remove list entries if given array is smaller", function () {
			longMenuQuestion.questionParts = {
				list 					: [[[1,2,3]],[[1,2,3]],[[1,2,3]]],
			};
			longMenuQuestion.answers = [[1]];
			longMenuQuestion.protect.checkDataConsistency([0,1]);
			expect(longMenuQuestion.answers).toEqual([[1]]);
			expect(longMenuQuestion.questionParts.list).toEqual([[[1,2,3]]]);
		});
	});

	describe("sliceInNewQuestionPart", function() {
		beforeEach(function () {
			longMenuQuestion.answers = [[]];
			longMenuQuestion.questionParts = {'list': [[[]]]};
		});

		it("should slice in new question object in empty array", function () {
			longMenuQuestion.protect.sliceInNewQuestionPart(0);
			expect(longMenuQuestion.answers).toEqual( [[],[]]);
			expect(longMenuQuestion.questionParts.list).toEqual([[[], '0', '1' ], [[]]]);
		});
		it("should slice in new question object in populated array", function () {
			longMenuQuestion.questionParts = {
				list 					: [[[1,2,3]]],
				gap_placeholder			: "placeholder",
				last_updated_element 	: 0,
				replacement_word 		: '',
				filereader_usable		: false,
				max_input_fields		: 500
			};
			longMenuQuestion.answers = [[1]];
			longMenuQuestion.protect.sliceInNewQuestionPart(0);
			expect(longMenuQuestion.answers).toEqual([[],[1]]);
			expect(longMenuQuestion.questionParts.list).toEqual([[[], '0', '1' ], [[1, 2, 3]]]);
		});
	});

	describe("DOM append Operations", function() {
		beforeEach(function () {
			longMenuQuestion.questionParts.list = [[[1,2,3][1]]];
		});

		it("should append a select box", function () {
			expect($('#select_type_0').length).toEqual(0);
			longMenuQuestion.protect.appendSelectBox($('.test_dummy'), 0);
			expect($('#select_type_0').length).toEqual(1);
		});

		it("should append a points field", function () {
			expect($('#points_0').length).toEqual(0);
			longMenuQuestion.protect.appendPointsField($('.test_dummy'), 0);
			expect($('#points_0').length).toEqual(1);
		});

		it("should append an upload button", function () {
			longMenuQuestion.protect.appendUploadButtons();
			expect($('#layout_dummy_buttons').length ).toEqual(1);
		});

		it("should append the answer overview", function () {
			longMenuQuestion.answers = [[1, 2, 3]];
			longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
			expect($('#answer_overview_0').length ).toEqual(0);
			longMenuQuestion.protect.appendAnswersOverview($('.test_dummy'), 0);
			expect($('#answer_overview_0').length ).toEqual(1);
		});

		it("should append the modal title", function () {
			longMenuQuestion.protect.appendModalTitle('text' , 0);
			expect($('.modal-title').html() ).toEqual('replacement_word 1 text');
			longMenuQuestion.protect.appendModalTitle('hello' , 5);
			expect($('.modal-title').html() ).toEqual('replacement_word 6 hello');
		});

		it("should append the form parts", function () {
			expect($('.test_dummy').html() ).toEqual('');
			$('.test_dummy').append('<div class="ilFormFooter"></div>');
			longMenuQuestion.answers = [[1, 2, 3]];
			longMenuQuestion.questionParts = {'list': [[[1, 2, 3]]]};
			longMenuQuestion.protect.appendFormParts();
			expect($('.test_dummy').html() ).not.toEqual('');
		});
	});

	describe("DOM redraw Operations", function() {
		it("should redraw the form parts", function () {
			expect($('.test_dummy').html() ).toEqual('');
			$('.test_dummy').append('<div class="longmenu"></div>');
			expect($('.test_dummy').find('.longmenu').length ).toEqual(1);
			longMenuQuestion.protect.redrawFormParts();
		});
		
		it("should redraw the answer list", function () {
			longMenuQuestion.answers = [[]];
			longMenuQuestion.questionParts = {
				'list': [[[]]],
				max_input_fields: 5
			};
			longMenuQuestion.protect.redrawAnswerList(0);
			expect($('.answerlist').length ).toEqual(1);
			longMenuQuestion.answers = [[1, 2, 3]];
			longMenuQuestion.questionParts = {
				'list': [[[1, 2, 3]]],
				max_input_fields: 5
			};
			longMenuQuestion.protect.redrawAnswerList(0);
			expect($('.answerlist').length ).toEqual(3);
			longMenuQuestion.answers = [[1, 2, 3, 4, 5, 6]];
			longMenuQuestion.questionParts = {
				'list': [[[1, 2, 3,4,5,6]]],
				max_input_fields: 5
			};
			longMenuQuestion.protect.redrawAnswerList(0);
			expect($('.modal_answer_options textarea').html()).toEqual("1\n2\n3\n4\n5\n6\n");
		});

		it("should redraw the answer list fast", function () {
			expect($('.answerlist').length ).toEqual(0);
			longMenuQuestion.answers = [[1, 2, 3, 123]];
			longMenuQuestion.questionParts = {
				'list': [[[1, 2, 3]]],
				max_input_fields: 5
			};
			longMenuQuestion.protect.redrawAnswerList(0);
			expect($('.answerlist').length ).toEqual(4);
			longMenuQuestion.protect.redrawAnswerListFast(0, 3, true);
			expect($('.answerlist').length ).toEqual(5);
			longMenuQuestion.protect.redrawAnswerListFast(0, 3, false);
			expect($('.answerlist').length ).toEqual(4);
		});
	});

	describe("buildAnswerOverview", function() {
		beforeEach(function () {
			$('.answer_options').remove();
			longMenuQuestion.answers = [[]];
			longMenuQuestion.questionParts = {'list': [[[1, 2]]]};
		});
		it("should display the add answers link if no answers given", function () {
			var html = longMenuQuestion.protect.buildAnswerOverview(0);
			expect(html).toEqual(' <a data-id=\"0\" class=\"answer_options\">[Add Answers]</a></p>');
		});
		
		it("should display edit buttons if answers given", function () {
			longMenuQuestion.answers = [[1, 2]];
			var html = longMenuQuestion.protect.buildAnswerOverview(0);
			expect(html).toEqual('<p>Answer Options: 2 <a data-id="0" class="answer_options"> [Edit]</a></p><p>Correct Answers: <span data-id="0" class="correct_answers"></span></p>');
		});

		it("should be the same amount of correct answers after an action", function () {
			longMenuQuestion.answers = [[1, 2, 3, 4]];
			longMenuQuestion.questionParts = {'list': [[['1', '2', '3']]]};
			expect(longMenuQuestion.questionParts.list[0][0]).toEqual(['1', '2', '3']);
			longMenuQuestion.protect.redrawAnswerList(0);
			expect(longMenuQuestion.questionParts.list[0][0]).toEqual(['1', '2', '3' ]);
		});
		
	});

	describe("recalculateAnswerListDataIds", function() {
		beforeEach(function () {

		});
		it("should the first element have the id 0", function () {
			$('.test_dummy').append('<div class="answerlist" data-id="5"></div>');
			longMenuQuestion.protect.recalculateAnswerListDataIds();
			expect($('.answerlist').attr('data-id') ).toEqual('0');
		});
		it("should the second element have the id 1", function () {
			$('.test_dummy').append('<div class="answerlist" data-id="5"></div>');
			$('.test_dummy').append('<div class="answerlist" data-id="5"></div>');
			longMenuQuestion.protect.recalculateAnswerListDataIds();
			expect($('.answerlist').eq(0).attr('data-id') ).toEqual('0');
			expect($('.answerlist').eq(1).attr('data-id') ).toEqual('1');
		});
	});
	
	describe("add event listener", function() {
		describe("addEditListeners", function() {
			beforeEach(function () {
				longMenuQuestion.answers = [[1, 2, 3]];
				longMenuQuestion.questionParts = {'list': [[[1, 2]]]};
				var html = longMenuQuestion.protect.buildAnswerOverview(0);
				$('.test_dummy').html(html);
			});

			it("there should be no events before calling the function", function () {
				expect($._data( $(".answer_options")[0], "events")).toBeUndefined();
			});
			it("there should be events after calling the function", function () {
				longMenuQuestion.protect.addEditListeners();
				expect($._data( $(".answer_options")[0], "events")['click'].length).toEqual(1);
			});

		});

		describe("appendModalCloseListener", function() {
			beforeEach(function () {
				$("#ilGapModal").off('hidden');
			});

			it("there should be no events before calling the function", function () {
				expect($._data( $("#ilGapModal")[0], "events")).toBeUndefined();
			});
			it("there should be events after calling the function", function () {
				longMenuQuestion.protect.appendModalCloseListener();
				expect($._data( $("#ilGapModal")[0], "events")['hidden'].length).toEqual(1);
			});
		});

		describe("appendSaveModalButtonEvent", function() {
			beforeEach(function () {
				$('.save-modal').remove();
				$('.test_dummy').append('<div class="save-modal"></div>');
			});

			it("there should be no events before calling the function", function () {
				expect($._data( $(".save-modal")[0], "events")).toBeUndefined();
			});
			it("there should be events after calling the function", function () {
				longMenuQuestion.protect.appendSaveModalButtonEventAnswers();
				expect($._data( $(".save-modal")[0], "events")['click'].length).toEqual(1);
			});
		});

		describe("appendAbstractModalButtonEvent", function() {
			beforeEach(function () {
				$('.testing').remove();
				$('.test_dummy').append('<div class="testing"></div>');
			});

			it("there should be no events before calling the function", function () {
				expect($._data( $(".testing")[0], "events")).toBeUndefined();
			});
			it("there should be events after calling the function", function () {
				longMenuQuestion.protect.appendAbstractModalButtonEvent('.testing', '');
				expect($._data( $(".testing")[0], "events")['click'].length).toEqual(1);
				spyOnEvent($('.testing'), 'click');
				$j('.testing').click();
				expect('click').toHaveBeenTriggeredOn($('.testing'));
			});
		});

		describe("appendCancelModalButtonEvent", function() {
			beforeEach(function () {
				$('.cancel-modal').remove();
				$('.test_dummy').append('<div class="cancel-modal"></div>');
			});

			it("there should be no events before calling the function", function () {
				expect($._data( $(".cancel-modal")[0], "events")).toBeUndefined();
			});

			it("there should be events after calling the function", function () {
				longMenuQuestion.protect.appendCancelModalButtonEvent('.testing', '');
				expect($._data( $(".cancel-modal")[0], "events")['click'].length).toEqual(1);
				spyOnEvent($('.cancel-modal'), 'click');
				$j('.cancel-modal').click();
				expect('click').toHaveBeenTriggeredOn($('.cancel-modal'));
			});
		});

		describe("appendAbstractCloneButtonEvent", function() {
			beforeEach(function () {
				$('.appendAbstractCloneButtonEvent').remove();
				$('.test_dummy').append('<div class="appendAbstractCloneButtonEvent"></div>');
			});

			it("there should be no events before calling the function", function () {
				expect($._data( $(".appendAbstractCloneButtonEvent")[0], "events")).toBeUndefined();
			});

			it("there should be events after calling the function", function () {
				longMenuQuestion.protect.appendAbstractCloneButtonEvent('.appendAbstractCloneButtonEvent', '');
				expect($._data( $(".appendAbstractCloneButtonEvent")[0], "events")['click'].length).toEqual(1);
				spyOnEvent($('.appendAbstractCloneButtonEvent'), 'click');
				$j('.appendAbstractCloneButtonEvent').click();
				expect('click').toHaveBeenTriggeredOn($('.appendAbstractCloneButtonEvent'));
			});
		});

		describe("appendAddButtonEvent", function() {
			beforeEach(function () {
				$('.clone_fields_add').remove();
				$('.test_dummy').append('<div class="clone_fields_add"></div>');
			});

			it("there should be no events before calling the function", function () {
				expect($._data( $(".clone_fields_add")[0], "events")).toBeUndefined();

			});
			it("there should be events after calling the function", function () {
				longMenuQuestion.protect.appendAddButtonEvent();
				expect($._data( $(".clone_fields_add")[0], "events")['click'].length).toEqual(1);
				spyOnEvent($('.test_dummy'), 'click');
				$j('.test_dummy').click();
				expect('click').toHaveBeenTriggeredOn($('.test_dummy'));
			});
		});
		
		describe("appendRemoveButtonEvent", function() {
			beforeEach(function () {
				$('.clone_fields_remove').remove();
				$('.test_dummy').append('<div class="clone_fields_remove"></div>');
			});

			it("there should be no events before calling the function", function () {
				expect($._data( $(".clone_fields_remove")[0], "events")).toBeUndefined();
			});

			it("there should be events after calling the function", function () {
				longMenuQuestion.protect.appendRemoveButtonEvent();
				expect($._data( $(".clone_fields_remove")[0], "events")['click'].length).toEqual(1);
			});
		});

		describe("addEditListeners", function() {
			beforeEach(function () {
				longMenuQuestion.answers = [[1, 2, 3]];
				longMenuQuestion.questionParts = {'list': [[[1, 2]]]};
				var html = longMenuQuestion.protect.buildAnswerOverview(0);
				$('.test_dummy').html(html + '<span id="fileinput"></span>');
			});

			it("there should be a click event catched", function () {
				longMenuQuestion.protect.addEditListeners();
				spyOnEvent($('.answer_options'), 'click');
				$j('.answer_options').click();
				expect('click').toHaveBeenTriggeredOn($('.answer_options'));
			});
		});

		describe("addEditListeners", function() {
			beforeEach(function () {
				longMenuQuestion.answers = [[1, 2, 3]];
				longMenuQuestion.questionParts = {'list': [[[1, 2]]]};
				var html = longMenuQuestion.protect.buildAnswerOverview(0);
				$('.test_dummy').html(html + '<span id="fileinput"></span>');
			});

			it("there should be a click event catched on answer options", function () {
				longMenuQuestion.protect.addEditListeners();
				spyOnEvent($('.answer_options'), 'click');
				$j('.answer_options').click();
				expect('click').toHaveBeenTriggeredOn($('.answer_options'));
			});

			it("there should be a click event catched on correct answer", function () {
				longMenuQuestion.protect.addEditListeners();
				spyOnEvent($('.correct_answers'), 'click');
				$j('.correct_answers').click();
				expect('click').toHaveBeenTriggeredOn($('.correct_answers'));
			});
		});
	});

	describe("event test", function() {
		describe("answerOptionsClickFunction", function() {
			beforeEach(function () {
				longMenuQuestion.answers = [[1, 2, 3]];
				longMenuQuestion.questionParts = {'list': [[[1, 2]]]};
				$('.modal-title').html('');
				$('.test_dummy').append('<div class="answer_options_event" data-id="0"><div id="fileinput"></div></div>');
			});

			it("there should be no content before the action", function () {
				expect($('.modal-title').html()).toEqual('');
			});

			it("there should be content after the action", function () {
				longMenuQuestion.protect.answerOptionsClickFunction($('.answer_options_event'));
				expect($('.modal-title').html()).toEqual('undefined 1 Answer Options: ');
			});
		});

		describe("saveModalEvent", function() {
			beforeEach(function () {
				$('.modal-title').attr('data-id', 0);
				longMenuQuestion.answers = [[0]];
				longMenuQuestion.questionParts = {'list': [[[]]], 'max_input_fields' : 200};
			});

			it("there should at least one element", function () {
				expect(longMenuQuestion.answers[0].length).toEqual(1);
			});

			it("there should be a value change after the save action", function () {
				$('.test_dummy').html('<input class="answerlist" value="abc"/><input class="answerlist" value="adbc"/><input class="answerlist" value="aadbc"/>');
				expect(longMenuQuestion.answers[0].length).toEqual(1);
				longMenuQuestion.protect.saveModalEventAnswers();
				expect(longMenuQuestion.answers[0].length).toEqual(3);
			});
		});
	});
	
	describe("Init tester", function() {
		beforeEach(function () {
		});

		it("should build and init the long menu question", function () {
			longMenuQuestion.Init();
			expect(longMenuQuestion.filereader_usable).toEqual(true);
		});
	});
	
	xdescribe("Utils", function() {
		beforeEach(function () {
		});

		it("should check the dummy benchmark call", function () {
			var t0 = longMenuQuestion.protect.benchmarkCallsDummyNotForUsage('testing');
			expect(t0).not.toEqual(0);
			var t1 = longMenuQuestion.protect.benchmarkCallsDummyNotForUsage('testing');
			expect(t1).toBeGreaterThan(0);
			var t2 = longMenuQuestion.protect.benchmarkCallsDummyNotForUsage('testing', t0);
			expect(t2).not.toEqual(0);
		});
	});
	
	 
});
