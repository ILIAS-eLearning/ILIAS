var ilTinyMceInitCallbackRegistry = {addCallback : function(){}};
var ClozeSettings = {
	gaps_php : [[]],
	gaps_combination : []
};
describe("cloze_gap_builder", function() {
	beforeEach(function () {
	});

	describe("checkJSONArraysOnEntry", function() {
		beforeEach(function () {
			ClozeSettings.gaps_php = null;
			ClozeSettings.gaps_combination  = null;
		});
		it("if settings are not initialised the should become empty arrays", function () {
			ClozeGapBuilder.protect.checkJSONArraysOnEntry();
			expect(ClozeSettings.gaps_php).toEqual([]);
			expect(ClozeSettings.gaps_combination).toEqual([]);
		});
	});

	describe("moveDeleteGapButton", function() {
		beforeEach(function () {
			$('body').append('<div id="2remove"><div class="remove_gap"><input type="button" value="Remove Gap" id="remove_gap_1"></div><div id="gap_error_1"><div class="col-sm-9"></div></div></div>')
		});
		afterEach(function () {
			$('#2remove').remove();
		});
		it("before the move operation there should be only one div ", function () {
			expect($('#gap_error_1').html()).toEqual('<div class="col-sm-9"></div>');
		});
		it("after moving the remove_gap button should be in the error div", function () {
			ClozeGapBuilder.protect.moveDeleteGapButton(1);
			expect($('#gap_error_1').html()).toEqual('<div class="col-sm-9"><div class="remove_gap"><input type="button" value="Remove Gap" id="remove_gap_1"></div></div>');
		});
	});

	describe("addModalFakeFooter", function() {
		beforeEach(function () {
			$('body').append('<div id="2remove"><div class="modal-content"></div></div>')
		});
		afterEach(function () {
			$('#2remove').remove();
		});
		it("before the call div should be empty", function () {
			expect($('.modal-content').html()).toEqual('');
		});
		it("after the call div should be empty fake footer should be in place", function () {
			ClozeGapBuilder.protect.addModalFakeFooter();
			expect($('.modal-content').html()).toEqual('<div class="modal-fake-footer"><input type="button" id="modal_ok_button" class="btn btn-default btn-sm btn-dummy" value="undefined"> <input type="button" id="modal_cancel_button" class="btn btn-default btn-sm btn-dummy" value="undefined"></div>');
		});
	});

	xdescribe("getTextAreaValue", function() {
		beforeEach(function () {
			$('body').append('<div id="2remove"><textarea id="cloze_text" val=""></textarea></div>')
		});
		afterEach(function () {
			$('#2remove').remove();
		});
		it("the value should be empty", function () {
			$('#cloze_text').val('');
			var text = ClozeGapBuilder.protect.getTextAreaValue();
			expect(text).toEqual('');
		});

		it("the value should equal ABC", function () {
			$('#cloze_text').val('ABC');
			var text = ClozeGapBuilder.protect.getTextAreaValue();
			expect(text).toEqual('ABC');
		});
	});


	describe("insertGapToJson", function() {
		beforeEach(function () {
			ClozeSettings.gaps_php = [[]];
		});
		afterEach(function () {

		});
		it("the value should be empty", function () {
			expect(ClozeSettings.gaps_php[0].length).toEqual(0);
		});
		it("insert single value", function () {
			ClozeGapBuilder.protect.insertGapToJson(0, 'hello');
			expect(ClozeSettings.gaps_php[0].length).toEqual(1);
		});
		it("insert multiple single values", function () {
			ClozeGapBuilder.protect.insertGapToJson(0, 'hello');
			ClozeGapBuilder.protect.insertGapToJson(0, 'hello');
			expect(ClozeSettings.gaps_php[0].length).toEqual(2);
			expect(ClozeSettings.gaps_php[0][0]['values'][0]['answer']).toEqual('hello');
		});
		it("insert multiple values", function () {
			ClozeGapBuilder.protect.insertGapToJson(0, '1,2');
			ClozeGapBuilder.protect.insertGapToJson(0, '3,4');
			expect(ClozeSettings.gaps_php[0].length).toEqual(2);
			expect(ClozeSettings.gaps_php[0][0]['values'][0]['answer']).toEqual('3');
			expect(ClozeSettings.gaps_php[0][0]['values'][1]['answer']).toEqual('4');
		});


	});

});
