var Gap_inserting_wizard = (function () {
	var pub = {};
	pub.active_gap = -1;
	
	function insertGapCodeAtCaret(object)  {
		return object.each(function(i) {
			var code_start = '[' + pub.replacement_word + ']';
			var code_end = '[/' + pub.replacement_word + ']';
			if (typeof tinyMCE != "undefined" && typeof tinyMCE.get(pub.textarea) != "undefined") {
				var ed =  tinyMCE.get(pub.textarea);
				ed.focus();
				ed.selection.setContent(code_start + ed.selection.getContent() + code_end);

				return;
			}
			if (document.selection) {
				//For browsers like Internet Explorer
				this.focus();
				sel = document.selection.createRange();
				sel.text = code_start + sel.text + code_end;
				this.focus();
			}
			else if (this.selectionStart || this.selectionStart == '0') {
				//For browsers like Firefox and Webkit based
				var startPos = this.selectionStart;
				var endPos = this.selectionEnd;
				var scrollTop = this.scrollTop;
				this.value = this.value.substring(0, startPos)
				+ code_start
				+ this.value.substring(startPos, endPos)
				+ code_end
				+ this.value.substring(endPos, this.value.length);
				this.focus();
				this.scrollTop = scrollTop;
			} else {
				this.value += code_start + code_end;
				this.focus();
			}
		});
	}

	function cleanGapCode()
	{
		var text 		= getTextAreaValue();
		var newText 	= text.replace(new RegExp("\\[" + pub.replacement_word + "[\\s\\S\\d]*?\\]", "g"), '[temp]');
		var gaps_length	= text.split(new RegExp('\\[' + pub.replacement_word + '[\\s\\S\\d]*?\\]')).length;
		newText 		= newText.replace(new RegExp('\\[\\/' + pub.replacement_word + '\\]' ,'g'), '[/temp]');
		for (var i = 0; i < gaps_length; i++) {
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
		setTextAreaValue(newText);
	}

	function getTextAreaValue()
	{
		var text;
		if (typeof(tinymce) != 'undefined') {
			text = tinymce.get(pub.textarea).getContent();
		}
		else {
			var textarea =  $('textarea#' + pub.textarea);
			text = textarea.val();
		}
		return text;
	}

	function setTextAreaValue(text)
	{
		var cursor, inGap;
		if (typeof(tinymce) != 'undefined') {
			//ToDo: Bug in tiny steals focus on setContent (tinymce Bug #6423)
			var inst = tinyMCE.activeEditor;
			cursor = getCursorPositionTiny(inst);
			tinymce.get(pub.textarea).setContent(text);
			inGap = cursorInGap(cursor);
			if(inGap[1] != '-1' )
			{
				pub.active_gap = parseInt(inGap[1], 10);
			}
			setCursorPositionTiny(inst, pub.active_gap);
		}
		else {
			var textarea =  $('textarea#' + pub.textarea);
			cursor = textarea.prop('selectionStart');
			textarea.val(text);
			inGap = cursorInGap(cursor + 1);
			if(inGap != '-1')
			{
				if(pub.active_gap == '-1')
				{
					setCaretPosition(textarea, cursor);
				}
				else
				{
					textarea.prop('selectionStart',ClozeGlobals.active_gap);
					textarea.prop('selectionEnd',ClozeGlobals.active_gap);
				}
				pub.active_gap = parseInt(inGap[1], 10);
			}
		}
	}
	
	function getCursorPositionTiny(editor)
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
	}

	function setCursorPositionTiny(editor, index)
	{
		var content = editor.getContent({format: 'html'});
		if( index == '-1')
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
	}

	function setCaretPosition(element, pos)
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
	}

	function cursorInGap(position)
	{
		var text    = getTextAreaValue();
		var end     = 0;
		var inGap   = -1;
		var gaps_length	= text.split(new RegExp('\\[' + pub.replacement_word + '[\\s\\S\\d]*?\\]')).length;
		var gapNumber;
		var gap_length = pub.replacement_word.length + 2;
		for (var i = 0; i < gaps_length.length; i++) {
			var start = text.indexOf('[' + pub.replacement_word + ' ', end);
			end = text.indexOf('[/' + pub.replacement_word + ']', parseInt(end, 10)) + gap_length;
			if (start < position && end >= position)
			{
				inGap = parseInt(end, 10) + 1;
				var gapSize = parseInt(end, 10) - parseInt(start, 10);
				var gapContent = text.substr(parseInt(start, 10) + gap_length, gapSize);
				gapContent = gapContent.split(']');
				gapNumber = gapContent[0];
			}
		}
		return [gapNumber, inGap];
	}
	//Public property
	pub.textarea  			= '';
	pub.trigger_id			= '';
	pub.replacement_word 	= '';
	pub.show_end			= true;

	pub.Init = function(){
		var selector =  $(pub.trigger_id);
		selector.off('click');
		selector.on('click', function (evt)
		{
			evt.preventDefault();
			insertGapCodeAtCaret($('#' + pub.textarea));
			cleanGapCode();
			return false;
		});
	};
	pub.doSomething = function(){
		
	};

	//Return just the public parts
	return pub;
}());