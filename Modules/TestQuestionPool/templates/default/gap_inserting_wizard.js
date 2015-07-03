var Gap_inserting_wizard = (function () {
	var pub = {};

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
		if (typeof(tinymce) != 'undefined') {
			tinymce.get(pub.textarea).setContent(text);
		}
		else {
			var textarea =  $('textarea#' + pub.textarea);
			textarea.val(text);
		}
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
	

	//Return just the public parts
	return pub;
}());