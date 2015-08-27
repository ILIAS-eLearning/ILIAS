var path = '';
var moz_workaround = false;
if (typeof window.__karma__ !== 'undefined') {
	path += 'base/';
}
else
{
	var $j = $;
}
if (navigator.userAgent.indexOf('Firefox') !== -1) {
	moz_workaround = true;
}

describe("GapInsertingWizard", function() {
	beforeEach(function () {
		jasmine.getFixtures().fixturesPath = path + 'spec/javascripts/fixtures';
		loadFixtures('gapInsertingWizard.html');
		GapInsertingWizard.textarea 		= 'gap_wizard_test';
		GapInsertingWizard.trigger_id 		= '#gap_trigger';
		GapInsertingWizard.replacement_word = 't';
		GapInsertingWizard.show_end 		= true;
		GapInsertingWizard.active_gap 		= -1;
		GapInsertingWizard.Init();
	});

	describe("Init", function() {
		it("the spy should capture a click event from gap_trigger", function () {
			var obj = $j('#gap_trigger');
			spyOnEvent(obj, 'click');
			obj.click();
			expect('click').toHaveBeenTriggeredOn(obj);
		});
		it("the spy should capture a click event from gap_wizard_test", function () {
			var obj = $j('#gap_wizard_test');
			spyOnEvent(obj, 'click');
			obj.click();
			expect('click').toHaveBeenTriggeredOn(obj);
		});
	});

	describe("cleanGapCode", function() {

		it("gaps in textarea should be the same after calling", function () {
			$('#gap_wizard_test').val('[t 1]1[/t] bla [t 2]2[/t]');
			GapInsertingWizard.protect.cleanGapCode();
			var text = GapInsertingWizard.getTextAreaValue();
			expect(text).toEqual('[t 1]1[/t] bla [t 2]2[/t]');
		});

		it("gaps in textarea should be the renumbered same after calling", function () {
			$('#gap_wizard_test').val('[t 4]1[/t] bla [t 12]2[/t]');
			GapInsertingWizard.protect.cleanGapCode();
			var text = GapInsertingWizard.getTextAreaValue();
			expect(text).toEqual('[t 1]1[/t] bla [t 2]2[/t]');
		});
	});

	describe("checking callbacks", function() {

		it("is callbackCleanGapCode be called", function () {
			var testing_call = false;
			GapInsertingWizard.callbackCleanGapCode = function(){testing_call =  true;};
			expect(testing_call).toEqual(false);
			GapInsertingWizard.protect.cleanGapCode();
			expect(testing_call).toEqual(true);
		});

		it("is callbackNewGap be called", function () {
			var testing_call = false;
			GapInsertingWizard.callbackNewGap = function(){testing_call =  true;};
			expect(testing_call).toEqual(false);
			$('#gap_trigger').click();
			expect(testing_call).toEqual(true);
		});

		it("is callbackClickedInGap be called", function () {
			var testing_call = false;
			GapInsertingWizard.callbackClickedInGap = function(){testing_call =  true;};
			expect(testing_call).toEqual(false);
			GapInsertingWizard.protect.clickedInGap(1);
			expect(testing_call).toEqual(true);
		});

		it("is callbackActiveGapChange be called", function () {
			var testing_call = false;
			GapInsertingWizard.callbackActiveGapChange = function(){testing_call =  true;};
			expect(testing_call).toEqual(false);
			GapInsertingWizard.protect.activeGapChanged(1);
			expect(testing_call).toEqual(true);
		});

		it("is checkDataConsistencyAfterGapRemoval be called", function () {
			var testing_call = false;
			GapInsertingWizard.checkDataConsistencyAfterGapRemoval = function(){testing_call =  true;};
			expect(testing_call).toEqual(false);
			GapInsertingWizard.protect.checkDataConsitencyCallback();
			expect(testing_call).toEqual(true);
		});

	});
	describe("testing callbacks", function() {
		it("should return the existing gaps", function () {
			var existing = [];
			$('#gap_wizard_test').val('[t 1]1[/t] bla [t 2]2[/t]');
			GapInsertingWizard.checkDataConsistencyAfterGapRemoval = function(existing_gaps){existing =  existing_gaps;};
			expect(existing).toEqual([]);
			GapInsertingWizard.protect.checkDataConsitencyCallback();
			expect(existing).toEqual([1,2]);
		});
	});
});

describe("GapInsertingWizard textarea", function() {
	beforeEach(function () {
		loadFixtures('gapInsertingWizard.html');
		GapInsertingWizard.textarea  			= 'gap_wizard_test';
		GapInsertingWizard.trigger_id			= '#gap_trigger';
		GapInsertingWizard.replacement_word 	= 't';
		GapInsertingWizard.show_end			    = true;
		GapInsertingWizard.active_gap          = -1;
		GapInsertingWizard.Init();
	});

	describe("TextArea Manipulation", function() {

		describe("getTextAreaValue", function() {
			it("the value should be empty", function () {
				$('#gap_wizard_test').val('');
				var text = GapInsertingWizard.getTextAreaValue();
				expect(text).toEqual('');
			});

			it("the value should equal ABC", function () {
				$('#gap_wizard_test').val('ABC');
				var text = GapInsertingWizard.getTextAreaValue();
				expect(text).toEqual('ABC');
			});
		});

		describe("setTextAreaValue", function() {
			it("the value should be empty", function () {
				GapInsertingWizard.setTextAreaValue('');
				expect($('#gap_wizard_test').val()).toEqual('');
			});

			it("the value should equal ABC", function () {
				GapInsertingWizard.setTextAreaValue('ABC');
				expect($('#gap_wizard_test').val()).toEqual('ABC');
			});
		});

		describe("Gap Insertion", function() {
			beforeEach(function () {
				$('#gap_wizard_test').val('');
			});
			it("single gap with end tag should exist in textarea", function () {
				$('#gap_trigger').click();
				var text = GapInsertingWizard.getTextAreaValue();
				expect(text).toEqual('[t 1][/t]');
			});
			it("single gap without end tag should exist in textarea", function () {
				GapInsertingWizard.show_end	= false;
				$('#gap_trigger').click();
				var text = GapInsertingWizard.getTextAreaValue();
				expect(text).toEqual('[t 1]');
			});
		});

		describe("cursorInGap", function() {
			it("cursor not in gap", function () {
				var value = GapInsertingWizard.protect.cursorInGap(3);
				expect(value).toEqual([ undefined, -1 ] );
			});

			it("cursor is in gap", function () {
				GapInsertingWizard.setTextAreaValue('[t 1]Blubb[/t]');
				var value = GapInsertingWizard.protect.cursorInGap(2);
				expect(value).toEqual([ '1', 14 ]);
			});
		});

		describe("bindTextAreaHandler", function() {
			beforeEach(function () {

			});
			it("cursor not in gap", function () {
				GapInsertingWizard.protect.last_cursor_position = 40;
				expect(GapInsertingWizard.protect.last_cursor_position).toEqual(40);
				GapInsertingWizard.protect.bindTextAreaHandler();
				$('#gap_wizard_test').click();
				expect(GapInsertingWizard.protect.last_cursor_position).toEqual(0);
			});

		});

		it("tiny should not exist", function (done) {
			expect(GapInsertingWizard.protect.isTinyActive()).toEqual(true);
			expect(GapInsertingWizard.protect.isTinyActive()).not.toEqual(false);
			expect(GapInsertingWizard.protect.isTinyActiveInTextArea()).toEqual(false);
			expect(GapInsertingWizard.protect.isTinyActiveInTextArea()).not.toEqual(true);
			done();
		});

	});

});
describe("GapInsertingWizard tinyMce", function() {
	beforeEach(function () {
		loadFixtures('gapInsertingWizard.html');
		GapInsertingWizard.textarea  			= 'gap_wizard_test';
		GapInsertingWizard.trigger_id			= '#gap_trigger';
		GapInsertingWizard.replacement_word 	= 't';
		GapInsertingWizard.show_end			    = true;
		GapInsertingWizard.active_gap          = -1;

	});

	describe("Event Handlers", function() {
		beforeEach(function (done) {
			GapInsertingWizard.textarea = 'dummy_text_2';
			$('#dynamic').append('<div id="3remove"><textarea id="dummy_text"></textarea><textarea id="dummy_text_2"></textarea></div>');
			tinyMCE.init({
				selector : "#dummy_text"
			});
			tinyMCE.init({
				selector : "#dummy_text_2",
				setup : function(ed) {
					ed.onInit.add(function(ed) {
						setTimeout(function(){
							GapInsertingWizard.protect.bindTextareaHandlerTiny();
							done();
						}, 100);
					});
				}
			});
		});
		afterEach(function (done) {
			$('#3remove').remove();
			done();
		});
		it("events should exists", function (done) {
			expect($._data( $('.mceIframeContainer iframe').eq(1).contents().find('body')[0], "events")['click'].length).toEqual(1);
			expect($._data( $('.mceIframeContainer iframe').eq(1).contents().find('body')[0], "events")['keyup'].length).toEqual(1);
			expect($._data( $('.mceIframeContainer iframe').eq(1).contents().find('body')[0], "events")['blur'].length).toEqual(1);
			expect($._data( $('.mceIframeContainer iframe').eq(1).contents().find('body')[0], "events")['paste'].length).toEqual(1);
			done();

		});
	});

	describe("Cursor Function", function() {
		beforeEach(function (done) {
			GapInsertingWizard.textarea = 'dummy_text_4';
			$('#dynamic').append('<div id="4remove"><textarea id="dummy_text_3"></textarea><textarea id="dummy_text_4"></textarea></div>');
			tinyMCE.init({
				selector : "#dummy_text_3"
			});
			tinyMCE.init({
				selector : "#dummy_text_4",
				setup : function(ed) {
					ed.onInit.add(function(ed) {
						setTimeout(function(){
							GapInsertingWizard.protect.bindTextareaHandlerTiny();
							done();
						}, 100);
					});
				}
			});
			$('.mceIframeContainer iframe').eq(1).contents().find('#tinymce').click();
			tinyMCE.activeEditor.setContent('test string');
		});
		afterEach(function (done) {
			$('#4remove').remove();
			done();
		});
		it("cursor position in tiny should be three", function (done) {
			var cur = GapInsertingWizard.protect.getCursorPositionTiny(tinyMCE.activeEditor);
			expect(cur).toEqual(3);
			GapInsertingWizard.protect.setCursorPositionTiny(tinyMCE.activeEditor, 4);
			var cur = GapInsertingWizard.protect.getCursorPositionTiny(tinyMCE.activeEditor);
			if(moz_workaround)
			{
				expect(cur).toEqual(3);

			}
			else
			{
				expect(cur).toEqual(4);
			}
			GapInsertingWizard.protect.setCursorPositionTiny(tinyMCE.activeEditor, -1);
			var cur = GapInsertingWizard.protect.getCursorPositionTiny(tinyMCE.activeEditor);
			expect(cur).toEqual(3);
			done();
		});
	});

	describe("Setting and Getting Text", function() {
		beforeEach(function (done) {
			GapInsertingWizard.textarea = 'dummy_text_6';
			$('#dynamic').append('<div id="5remove"><textarea id="dummy_text_5"></textarea><textarea id="dummy_text_6"></textarea></div>');
			tinyMCE.init({
				selector : "#dummy_text_5"
			});
			tinyMCE.init({
				selector : "#dummy_text_6",
				setup : function(ed) {
					ed.onInit.add(function(ed) {
						setTimeout(function(){
							GapInsertingWizard.protect.bindTextareaHandlerTiny();
							done();
						}, 100);
					});
				}
			});
			$('.mceIframeContainer iframe').eq(1).contents().find('#tinymce').click();
			tinyMCE.activeEditor.setContent('test string');
		});
		afterEach(function (done) {
			$('#5remove').remove();
			done();
		});

		it("the value should be empty", function () {
			GapInsertingWizard.setTextAreaValue('');
			expect(GapInsertingWizard.getTextAreaValue()).toEqual('');
		});

		it("the value should equal ABC", function () {
			GapInsertingWizard.setTextAreaValue('ABC');
			if(moz_workaround)
			{
				expect(GapInsertingWizard.getTextAreaValue()).toEqual('\n<p>ABC</p>');
			}
			else
			{
				expect(GapInsertingWizard.getTextAreaValue()).toEqual('<p>ABC</p>');
			}
		});
	});

	describe("Gap inserting tiny", function() {
		beforeEach(function (done) {
			GapInsertingWizard.textarea = 'dummy_text_8';
			$('#dynamic').append('<div id="6remove"><textarea id="dummy_text_7"></textarea><textarea id="dummy_text_8"></textarea></div>');
			tinyMCE.init({
				selector : "#dummy_text_7"
			});
			tinyMCE.init({
				selector : "#dummy_text_8",
				setup : function(ed) {
					ed.onInit.add(function(ed) {
						setTimeout(function(){
							GapInsertingWizard.protect.bindTextareaHandlerTiny();
							done();
						}, 100);
					});
				}
			});
			$('.mceIframeContainer iframe').eq(1).contents().find('#tinymce').click();
			tinyMCE.activeEditor.setContent('test string');
			GapInsertingWizard.Init();
		});
		afterEach(function (done) {
			$('#6remove').remove();
			done();
		});

		it("inserting single gap with gap_trigger", function () {

			GapInsertingWizard.protect.setCursorPositionTiny(tinyMCE.activeEditor, 4);
			$('#gap_trigger').click();
			expect(GapInsertingWizard.getTextAreaValue()).toEqual('<p>t[t 1][/t]est string</p>');
		});
		it("inserting nultiple gaps with gap_trigger", function () {

			GapInsertingWizard.protect.setCursorPositionTiny(tinyMCE.activeEditor, 4);
			$('#gap_trigger').click();
			GapInsertingWizard.protect.setCursorPositionTiny(tinyMCE.activeEditor, 0);
			$('#gap_trigger').click();
			expect(GapInsertingWizard.getTextAreaValue()).toEqual('<p>[t 1][/t]</p>\n<p>t[t 2][/t]est string</p>');
		});
	});

	describe("TinyMCE Detection", function() {
		beforeEach(function (done) {
			GapInsertingWizard.textarea = 'dummy_text_7';
			$('#dynamic').append('<div id="6remove"><textarea id="dummy_text"></textarea><textarea id="dummy_text_7"></textarea></div>');
			tinyMCE.init({
				selector : "#dummy_text_7",
				setup : function(ed) {
					ed.onInit.add(function(ed) {
						setTimeout(function(){
							GapInsertingWizard.protect.bindTextareaHandlerTiny();
							done();
						}, 100);
					});
				}
			});
		});
		afterEach(function (done) {
			$('#6remove').remove();
			done();
		});
		it("tiny should exist", function (done) {
			expect(GapInsertingWizard.protect.isTinyActive()).toEqual(true);
			expect(GapInsertingWizard.protect.isTinyActiveInTextArea()).toEqual(true);
			expect(GapInsertingWizard.protect.isTinyActive()).not.toEqual(false);
			expect(GapInsertingWizard.protect.isTinyActiveInTextArea()).not.toEqual(false);
			done();
		});
	});
});
