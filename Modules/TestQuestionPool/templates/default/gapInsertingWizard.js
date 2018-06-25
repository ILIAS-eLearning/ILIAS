/*globals  tinyMCE, tinymce, prompt, ilTinyMceInitCallbackRegistry */
var GapInsertingWizard = (function () {
	'use strict';
	var pub = {}, pro = { 'last_cursor_position' : 0 };

	pro.insertGapCodeAtCaret = function(object)  {
		return object.each(function() {
			var code_start = '[' + pub.replacement_word + ']';
			var code_end = '[/' + pub.replacement_word + ']';
			if (pro.isTinyActiveInTextArea()) {
				var ed =  tinyMCE.get(pub.textarea);
				ed.focus();
				ed.selection.setContent(code_start + ed.selection.getContent() + code_end);
				return;
			}
			if (document.selection) {
				//For browsers like Internet Explorer
				this.focus();
				var sel = document.selection.createRange();
				sel.text = code_start + sel.text + code_end;
				this.focus();
			}
			else if (this.selectionStart || this.selectionStart === '0') {
				//For browsers like Firefox and Webkit based
				var startPos = this.selectionStart;
				var endPos = this.selectionEnd;
				var scrollTop = this.scrollTop;
				this.value = this.value.substring(0, startPos) 	+ code_start + this.value.substring(startPos, endPos) + code_end + this.value.substring(endPos, this.value.length);
				this.focus();
				this.scrollTop = scrollTop;
			} else {
				this.value += code_start + code_end;
				this.focus();
			}
		});
	};

	pro.isTinyActive = function()
	{
		return 	(typeof tinyMCE !== 'undefined');
	};

	pro.isTinyActiveInTextArea = function()
	{
		return 	(typeof tinyMCE !== 'undefined' && typeof tinyMCE.get(pub.textarea) !== 'undefined' && tinyMCE.get(pub.textarea) !== null );
	};

	pro.cleanGapCode = function()
	{
		var text 		= pub.getTextAreaValue();
		var newText 	= text.replace(new RegExp('\\[' + pub.replacement_word + '[\\s\\S\\d]*?\\]', 'g'), '[temp]');
		var gaps_length	= text.split(new RegExp('\\[' + pub.replacement_word + '[\\s\\S\\d]*?\\]')).length;
		newText 		= newText.replace(new RegExp('\\[\\/' + pub.replacement_word + '\\]' ,'g'), '[/temp]');
		for (var i = 0; i < gaps_length; i = i + 1) {
			var gap_id =  parseInt(i, 10) + 1;
			newText = newText.replace(/\[temp]/, '[' + pub.replacement_word + ' ' + gap_id + ']');
			if(pub.show_end)
			{
				newText = newText.replace(/\[\/temp]/, '[/' + pub.replacement_word +']');
			}
			else
			{
				newText = newText.replace(/\[\/temp]/, '');
			}
		}
		pub.setTextAreaValue(newText);
		if (typeof pub.callbackCleanGapCode === 'function') {
			pub.callbackCleanGapCode();
		}
	};

	pro.createNewGapCode = function()
	{
		var newText = pub.getTextAreaValue();
		var iterator;
		if(pub.show_end)
		{
			iterator = newText.match(new RegExp('\\[' + pub.replacement_word + '[\\s\\S\\d]*?\\](.*?)\\[\\/' + pub.replacement_word + '\\]', 'g'));
		}
		else
		{
			iterator = newText.match(new RegExp('\\[' + pub.replacement_word + '[\\s\\S\\d]*?\\]', 'g'));
		}
		var last = 0;
		for (var i = 0; i < iterator.length; i = i + 1 ) {
			last = i;
			if (iterator[i].match(new RegExp('\\[' + pub.replacement_word + '\\]'))) {
				var values = iterator[i].replace('[' + pub.replacement_word +']', '');
				values = values.replace('[/' + pub.replacement_word +']', '');
				var gap_id =  parseInt(i, 10) + 1;
				newText = newText.replace('[' + pub.replacement_word +']', '['  + pub.replacement_word +' ' + gap_id + ']');
				if (typeof pub.callbackNewGap === 'function') {
					pub.callbackNewGap(last, values);
				}
			}
		}
		pub.setTextAreaValue(newText);
		pro.cleanGapCode();
	};

	pro.getCursorPositionTiny = function(editor)
	{
		var bm = editor.selection.getBookmark(0);
		var selector = '[data-mce-type=bookmark]';
		var bmElements = editor.dom.select(selector);
		editor.selection.select(bmElements[0]);
		editor.selection.collapse();
		var elementID = '######cursor######';
		var positionString = '<span id="' + elementID + '"></span>';
		editor.selection.setContent(positionString);
		var content = editor.getContent({format: 'html'});
		var index = content.indexOf(positionString);
		editor.dom.remove(elementID, false);
		editor.selection.moveToBookmark(bm);
		return index;
	};

	pro.setCursorPositionTiny = function(editor, index)
	{
		var content = editor.getContent({format: 'html'});
		if( index === -1)
		{
			index = 0;
		}
		var part1 = content.substr(0, index);
		var part2 = content.substr(index);
		var bookmark = editor.selection.getBookmark(0);
		var positionString = '<span id="' + bookmark.id + '_start" data-mce-type="bookmark" data-mce-style="overflow:hidden;line-height:0px"></span>';
		var contentWithString = part1 + positionString + part2;
		editor.setContent(contentWithString, ({format: 'raw'}));
		editor.selection.moveToBookmark(bookmark);
		return bookmark;
	};

	pro.setCaretPosition = function(element, pos)
	{
		if (element.setSelectionRange) {
			element.focus();
			element.setSelectionRange(pos, pos);
		}
		else if (element.createTextRange) {
			var range = element.createTextRange();
			range.collapse(true);
			range.moveEnd('character', pos);
			range.moveStart('character', pos);
			range.select();
		}
	};

	pro.cursorInGap = function(position)
	{
		var text                = pub.getTextAreaValue();
		var end                 = 0;
		var gap_end_position    = -1;
		var gaps_length	        = text.split(new RegExp('\\[' + pub.replacement_word + '[\\s\\S\\d]*?\\]')).length;
		var gapNumber, start;
		var gap_length = pub.replacement_word.length + 2;
		for (var i = 0; i < gaps_length; i = i + 1) {

			if( pub.show_end )
			{
				start = text.indexOf('[' + pub.replacement_word + ' ', end);
				end = text.indexOf('[/' + pub.replacement_word + ']', parseInt(end, 10)) + gap_length;
			}
			else
			{
				start = text.indexOf('[' + pub.replacement_word + ' ', end);
				end = start + gap_length + 1;
			}
			if ( start !== -1 && start < position && end >= position)
			{
				gap_end_position = parseInt(end, 10) + 1;
				var gapSize = parseInt(end, 10) - parseInt(start, 10);
				var gapContent = text.substr(parseInt(start, 10) + gap_length, gapSize);
				gapContent = gapContent.split(']');
				gapNumber = gapContent[0];
				pro.clickedInGap(gapNumber);
			}
		}
		return [gapNumber, gap_end_position];
	};

	pro.clickedInGap = function(gapNumber)
	{
		var gap = parseInt(gapNumber, 10);
		pro.activeGapChanged(gap);
		pro.clickedInGapCallbackCall();
	};

	pro.clickedInGapCallbackCall = function()
	{
		if (typeof pub.callbackClickedInGap === 'function') {
			pub.callbackClickedInGap();
		}
	};

	pro.activeGapChanged = function(gap)
	{
		if( pub.active_gap !== gap )
		{
			pub.active_gap = gap;
			if (typeof pub.callbackActiveGapChange === 'function') {
				pub.callbackActiveGapChange();
			}
		}
	};

	pro.bindTextareaHandlerTiny = function()
	{
		var tinymce_iframe_selector =   $('.mceIframeContainer iframe').eq(1).contents().find('body');
		tinymce_iframe_selector.on('click', function () {
			var inst = tinyMCE.activeEditor;
			pro.last_cursor_position = pro.getCursorPositionTiny(inst, false);
			var pos = pro.cursorInGap(pro.last_cursor_position);
			if (pos[1] !== -1) {
				pro.setCursorPositionTiny(inst,pos[1]);
				pro.clickedInGapCallbackCall();
			}
		});

		tinymce_iframe_selector.keydown(function () {
			/*var inst = tinyMCE.activeEditor;
			 var cursorPosition = pro.getCursorPositionTiny(inst);
			 var pos = pro.cursorInGap(cursorPosition);
			 pro.last_cursor_position = cursorPosition;
			 if (pos[1] !== -1) 
			 {
			 pro.setCursorPositionTiny(inst,pos[1]);
			 pro.clickedInGapCallbackCall();
			 }*/
		});
		tinymce_iframe_selector.keyup(function(e){
			if(e.keyCode === 8 || e.keyCode === 46)
			{
				pro.checkDataConsitencyCallback();
			}
		});

		tinymce_iframe_selector.blur(function () {
			//This won't work this way
			//pro.checkDataConsitencyCallback();
		});
		tinymce_iframe_selector.bind('paste', function (event){
			event.preventDefault();
			var clipboard_text = (event.originalEvent || event).clipboardData.getData('text/plain') || prompt('Paste something..');
			clipboard_text = clipboard_text.replace(new RegExp('\\[' + pub.replacement_word + '[\\s\\S\\d]*?\\]', 'g'), '[' +  pub.replacement_word  + ']');
			var text = pub.getTextAreaValue();
			var textBefore = text.substring(0,  pro.last_cursor_position );
			var textAfter  = text.substring(pro.last_cursor_position, text.length );
			pub.setTextAreaValue(textBefore + clipboard_text + textAfter);
			pro.createNewGapCode();
			pro.cleanGapCode();
		});
	};

	pro.checkDataConsitencyCallback = function()
	{
		var gaps = pub.getTextAreaValue().match(new RegExp('\\[' + pub.replacement_word + '[\\s\\S\\d]*?\\]', 'g'));
		var front = new RegExp('\\[' + pub.replacement_word + '\\s');
		var end   = new RegExp('\\]');
		var existing_gaps = [];
		var gap = '';
		if( gaps !== null )
		{
			$.each(gaps , function( index, value ) {
				gap = parseInt(value.replace(front,'').replace(end,''), 10);
				existing_gaps.push(gap);
			});
		}
		if (typeof pub.checkDataConsistencyAfterGapRemoval === 'function') {
			pub.checkDataConsistencyAfterGapRemoval(existing_gaps);
		}
		pro.cleanGapCode();
	};

	pro.bindTextAreaHandler = function()
	{
		var cloze_text_selector= $('#' + pub.textarea);

		cloze_text_selector.click(function () {
			var cursorPosition = $('#' + pub.textarea).prop('selectionStart');
			var pos = pro.cursorInGap(cursorPosition);
			pro.last_cursor_position = cursorPosition;
			if (pos[1] !== -1) {
				pro.setCaretPosition(document.getElementById(pub.textarea), pos[1]);
			}
			return false;
		});
		cloze_text_selector.keyup(function(e){
			if(e.keyCode == 8 || e.keyCode == 46)
			{
				pro.checkDataConsitencyCallback();
			}
			else if ( (e.metaKey || e.ctrlKey) && ( String.fromCharCode(e.which) === 'x' || String.fromCharCode(e.which) === 'X' ) ) {
				pro.checkDataConsitencyCallback();
			}
		});
	};

	pro.appendGapTrigger = function ()
	{
		var selector =  $(pub.trigger_id);
		selector.off('click');
		selector.on('click', function (evt)
		{
			evt.preventDefault();
			pro.insertGapCodeAtCaret($('#' + pub.textarea));
			pro.createNewGapCode();
			return false;
		});
	};
	//Public property
	pub.textarea  			= '';
	pub.trigger_id			= '';
	pub.replacement_word 	= '';
	pub.show_end			= true;
	pub.active_gap          = -1;
	pub.callbackActiveGapChange = {};
	pub.callbackClickedInGap = {};
	pub.callbackCleanGapCode = {};
	pub.callbackNewGap = {};
	pub.checkDataConsistencyAfterGapRemoval = {};

	pub.Init = function()
	{
		pro.appendGapTrigger();
		$( document ).ready(function() {
			if (pro.isTinyActive()) {
				if (tinyMCE.activeEditor === null || tinyMCE.activeEditor.isHidden() !== false) {
					ilTinyMceInitCallbackRegistry.addCallback(pro.bindTextareaHandlerTiny);
				}
				else if (tinyMCE.editors.length > 0) {
					pro.bindTextareaHandlerTiny();
				}
				else{
					pro.bindTextAreaHandler();
				}
			}
			else
			{
				pro.bindTextAreaHandler();
			}
		})
	};
	pub.getTextAreaValue = function()
	{
		var text;
		if (pro.isTinyActiveInTextArea())
		{
			text = tinymce.get(pub.textarea).getContent();
		}
		else {
			var textarea =  $('textarea#' + pub.textarea);
			text = textarea.val();
		}
		return text;
	};

	pub.setTextAreaValue = function(text)
	{
		var cursor, inGap;
		if (pro.isTinyActiveInTextArea())
		{
			if (navigator.userAgent.indexOf('Firefox') !== -1)
			{
				text = text.replace(new RegExp('(<p>(&nbsp;)*<\/p>(\n)*)' , 'g'), '')
			}
			//ToDo: Bug in tiny steals focus on setContent (tinymce Bug #6423)
			var inst = tinyMCE.activeEditor;
			cursor = pro.getCursorPositionTiny(inst);
			tinymce.get(pub.textarea).setContent(text);
			inGap = pro.cursorInGap(cursor);
			if(inGap[1] !== -1 )
			{
				pub.active_gap = parseInt(inGap[0], 10);
			}
			pro.setCursorPositionTiny(inst, parseInt(cursor, 10));
		}
		else {
			var textarea =  $('textarea#' + pub.textarea);
			cursor = textarea.prop('selectionStart');
			textarea.val(text);
			inGap = pro.cursorInGap(cursor + 1);
			if(inGap !== -1)
			{
				if(pub.active_gap === -1)
				{
					pro.setCaretPosition( document.getElementById(pub.textarea), cursor);
				}
				else
				{
					textarea.prop('selectionStart',pub.active_gap);
					textarea.prop('selectionEnd',pub.active_gap);
				}
				pub.active_gap = parseInt(inGap[0], 10);
			}
			pro.setCaretPosition(document.getElementById(pub.textarea), parseInt(cursor, 10));
		}
	};
	pub.protect = pro;
	//Return just the public parts
	return pub;
}());