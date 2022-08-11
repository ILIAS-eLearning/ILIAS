/*globals  tinyMCE, tinymce, prompt, ilTinyMceInitCallbackRegistry */
var longMenuQuestionGapBuilder = (() => {
	'use strict';
	
	let pub = {};
	let last_cursor_position = 0;
	let needs_update = false;

	let isTinyActive = () =>
	{
		return 	(typeof tinyMCE !== 'undefined');
	};

	let isTinyActiveInTextArea = () =>
	{
		return 	(isTinyActive() && typeof tinyMCE.get(pub.textarea) !== 'undefined' && tinyMCE.get(pub.textarea) !== null );
	};
	
	let bindTextareaHandlerVanilla = () =>
	{
		let cloze_text_area = document.getElementById(pub.textarea);

		cloze_text_area.onclick =  moveCurserToEndOfGapVanilla;
		cloze_text_area.oninput = inputfunction;
		cloze_text_area.onkeyup = keyupfunction;
		cloze_text_area.oncut = cutfunction;
		cloze_text_area.onpaste = (e) =>
		{
			e.preventDefault();
			if (e.clipboardData.getData('text').search(new RegExp(pub.gap_regexp)) !== -1) {
				let new_clipboard = e.clipboardData.getData('text').replace(
						new RegExp(pub.gap_regexp, 'g'), '[' +  pub.replacement_word  + ']');
				document.execCommand('insertText', false, new_clipboard);
				createNewGapCode();
				checkDataConsistencyCallback();
				moveCurserToEndOfGapVanilla();
			}
		};
	};
	
	let bindTextareaHandlerTiny = (ed) =>
	{
		if (ed.id !== pub.textarea) {
			return;
		}
		ed.on('click', clickfunction);
		ed.on('input', inputfunction);
		ed.on('cut', cutfunction);
		ed.on('keyup', keyupfunction);
		ed.on('PastePreProcess', (e) => {
			e.content = e.content.replace(new RegExp(pub.gap_regexp, 'g'), '[' +  pub.replacement_word  + ']');
		});
		ed.on('paste', () => {
			if (pub.getTextAreaValue().indexOf('[' +  pub.replacement_word  + ']') !== -1) {
				createNewGapCode();
			}
		});
	};
	
	let clickfunction = () =>
	{
		if (isTinyActiveInTextArea() && moveCursorToEndOfGapTiny()) {
			clickedInGapCallbackCall();
			return;
		}
		if (!isTinyActiveInTextArea() && moveCurserToEndOfGapVanilla()) {
			clickedInGapCallbackCall();
			return;
		}
	}
	
	let inputfunction = () =>
	{
		if (needs_update) {
			checkDataConsistencyCallback();
			needs_update = false;
		}
	};
	
	let keyupfunction = (e) => {	
		if (e.key === "Backspace" || e.key === "Delete")
		{
			checkDataConsistencyCallback();
			return;
		}
		if (e.key === "]" && pub.getTextAreaValue().indexOf('[' +  pub.replacement_word  + ']') !== -1) {
			createNewGapCode();
			return;
		}
		if ((e.key === 'z' || e.key === 'Z' || e.key === 'y') && (e.ctrlKey || e.metaKey)) {
			revertConsistencyChangesAfterUndoOrRedo();
			return;
		}
		if ((e.key === 'ArrowRight') || (e.key === 'ArrowDown') || (e.key === 'ArrowLeft') || (e.key === 'ArrowUp')) {
			if (isTinyActiveInTextArea()) {
				moveCursorToEndOfGapTiny();
			} else {
				moveCurserToEndOfGapVanilla();
			}
		}
	};
	
	let cutfunction = () =>
	{
		needs_update = true;
	};
	
	let appendGapTrigger = () =>
	{
		let selector =  document.getElementById(pub.trigger);
		selector.onclick = (e) =>
		{
			e.preventDefault();
			insertGapCodeAtCaret(document.getElementById(pub.textarea));
			createNewGapCode();
			return false;
		};
	};
	
	let insertGapCodeAtCaret = (o) =>
	{
		let code = '[' + pub.replacement_word + ']';
		if (isTinyActiveInTextArea()) {
			let ed =  tinyMCE.get(pub.textarea);
			ed.focus();
			ed.selection.setContent(code);
			return;
		}
		if (o.selectionStart || o.selectionStart === '0') {
			//For browsers like Firefox and Webkit based
			let startPos = o.selectionStart;
			let scrollTop = o.scrollTop;
			o.value = o.value.substring(0, startPos) 	+ code + o.value.substring(startPos, o.value.length);
			o.focus();
			o.scrollTop = scrollTop;
			return;
		}
		o.value += code;
		o.focus();
	};

	let createNewGapCode = () =>
	{
		let newText = pub.getTextAreaValue();
		let iterator = newText.match(new RegExp(pub.gap_regexp, 'g'));		
		let last = 0;
		for (let i = 0; i < iterator.length; i++ ) {
			last = i;
			if (iterator[i].match(new RegExp('\\[' + pub.replacement_word + '\\]'))) {
				let gap_id =  i + 1;
				newText = newText.replace('[' + pub.replacement_word +']', '['  + pub.replacement_word + ' ' + gap_id + ']');
				if (typeof pub.callbackNewGap === 'function') {
					pub.callbackNewGap(last);
				}
			}
		}
		pub.setTextAreaValue(newText);
		if (isTinyActiveInTextArea()) {
			moveCursorToEndOfGapTiny();
		}
		cleanGapCode();
	};	
	
	let cleanGapCode = () =>
	{
		let text 		= pub.getTextAreaValue();
		let newText 	= text.replace(new RegExp(pub.gap_regexp, 'g'), '[temp]');
		let gaps_length	= text.split(new RegExp(pub.gap_regexp)).length;
		
		for (let i = 0; i < gaps_length; i++) {
			let gap_id =  i + 1;
			newText = newText.replace(/\[temp]/, '[' + pub.replacement_word + ' ' + gap_id + ']');
		}
		
		pub.setTextAreaValue(newText);
		if (typeof pub.callbackCleanGapCode === 'function') {
			pub.callbackCleanGapCode();
		}
	};

	let getCursorPositionTiny = (ed) =>
	{
		let bm = ed.selection.getBookmark(0);
		let selector = '[data-mce-type=bookmark]';
		let bmElements = ed.dom.select(selector);
		ed.selection.select(bmElements[0]);
		ed.selection.collapse();
		let elementID = '######cursor######';
		let positionString = '<span id="' + elementID + '"></span>';
		ed.selection.setContent(positionString);
		let content = ed.getContent({format: 'html'});
		let index = content.indexOf(positionString);
		ed.dom.remove(elementID, false);
		ed.selection.moveToBookmark(bm);
		return index;
	};
	
	let moveCursorToEndOfGapTiny = () =>
	{
		let inst = tinyMCE.activeEditor;
		last_cursor_position = getCursorPositionTiny(inst);
		let pos = cursorInGap(last_cursor_position);
		if (pos[1] !== -1) {
			setCursorPositionTiny(inst,pos[1]);
			return true;
		}
		return false;
	}
	
	let moveCurserToEndOfGapVanilla = () =>
		{
			let cloze_text_area = document.getElementById(pub.textarea);
			let cursorPosition =cloze_text_area.selectionStart;
			let pos = cursorInGap(cursorPosition);
			last_cursor_position = cursorPosition;
			if (pos[1] !== -1) {
				setCaretPosition(cloze_text_area, pos[1]);
				return true;
			}
			return false;
		}

	let setCursorPositionTiny = (ed, ind) =>
	{
		let content = ed.getContent({format: 'html'});
		if (ind == '-1') {
			ind = 0;
		}
		let part1 = content.substr(0, ind);
		let part2 = content.substr(ind);
		let bookmark = ed.selection.getBookmark(0);
		let positionString = '<span id="' + bookmark.id + '_start" data-mce-type="bookmark" data-mce-style="overflow:hidden;line-height:0px"></span>';
		let contentWithString = part1 + positionString + part2;
		ed.setContent(contentWithString, ({format: 'raw'}));
		ed.selection.moveToBookmark(bookmark);
		return bookmark;
	};

	let setCaretPosition = (elem, pos) =>
	{
		if (elem.setSelectionRange) {
			elem.focus();
			elem.setSelectionRange(pos, pos);
		}
		else if (elem.createTextRange) {
			let range = elem.createTextRange();
			range.collapse(true);
			range.moveEnd('character', pos);
			range.moveStart('character', pos);
			range.select();
		}
	};

	let cursorInGap = (pos) =>
	{
		let text                = pub.getTextAreaValue();
		let gap_end_position    = -1;
		let offset 				= 0;
		let gaps_length	        = text.split(new RegExp(pub.gap_regexp)).length;

		let gap_number, start;
		for (let i = 0; i < gaps_length; i = i + 1) {
			let gap_length = pub.replacement_word.length + i.toString().length + 3;
			start = text.search(new RegExp(pub.gap_regexp));
			let end = start + gap_length;
			if ( start !== -1 && offset + start < pos && offset + end > pos)
			{
				gap_end_position = parseInt(offset + end, 10);
				let gap_number = text.substr(parseInt(start, 10) + pub.replacement_word.length + 2, i.toString().length);
				clickedInGap(gap_number);
				break;
			}
			offset += end;
			text = text.substring(parseInt(end, 10));
		}
		return [gap_number, gap_end_position];
	};

	let clickedInGap = (gapNr) =>
	{
		let gap = parseInt(gapNr, 10);
		activeGapChanged(gap);
		clickedInGapCallbackCall();
	};

	let clickedInGapCallbackCall = () =>
	{
		if (typeof pub.callbackClickedInGap === 'function') {
			pub.callbackClickedInGap();
		}
	};

	let activeGapChanged = (gap) =>
	{
		if( pub.active_gap !== gap )
		{
			pub.active_gap = gap;
			if (typeof pub.callbackActiveGapChange === 'function') {
				pub.callbackActiveGapChange();
			}
		}
	};

	let checkDataConsistencyCallback = () =>
	{
		let gaps = pub.getTextAreaValue().match(new RegExp(pub.gap_regexp, 'g'));
		let front = new RegExp('\\[' + pub.replacement_word + '\\s');
		let end   = new RegExp('\\]');
		let existing_gaps = [];
		let gap = '';
		if( gaps !== null )
		{
			gaps.forEach((value ) => {
				gap = parseInt(value.replace(front,'').replace(end,''), 10);
				existing_gaps.push(gap);
			});
		}
		
		let question_list_before = longMenuQuestion.questionParts.list;
		let answers_before = longMenuQuestion.answers;
		if (typeof pub.checkDataConsistencyAfterGapRemoval === 'function') {
			pub.checkDataConsistencyAfterGapRemoval(existing_gaps);
		}
		
		if (question_list_before.toString() !== longMenuQuestion.questionParts.list.toString() || 
			answers_before.toString() !== longMenuQuestion.answers.toString()) {
			cleanGapCode();
		}
	};

	let revertConsistencyChangesAfterUndoOrRedo = () =>
	{
		let content = pub.getTextAreaValue();
		if (content.indexOf('[' +  pub.replacement_word  + ']') !== -1) {
			let text_elements = tiny_content.split('[' +  pub.replacement_word  + ']');
			text_elements.forEach((text, offset) => {
				if (offset === 0) {
					return;
				}
				let gaps = text.match(new RegExp(pub.gap_regexp, 'g'));
				if (gaps === null) {
					return;
				}
				gaps.forEach((gap_text) => {
					let gap_id = gap_text.match(new RegExp('[\\d]+'));
					let new_gap_id = parseInt(gap_id[0], 10) + offset;
					text = text.replace(gap_text, '[' + pub.replacement_word + ' ' + new_gap_id + ']');
				});
				text_elements[offset] = text; 
			});
			pub.setTextAreaValue(text_elements.join(''));
			checkDataConsistencyCallback();
			return;
		}
		let matches = content.match(new RegExp(pub.gap_regexp, 'g'));
		let length = 0;
		if (matches) {
			length = matches.length;
		} 
		if (length > longMenuQuestion.questionParts.list.length) {
			let starting_length = content.length;
			let additionnal_gaps = length - longMenuQuestion.questionParts.list.length;
			let i = 0;
			while (i < additionnal_gaps) {
				let gap_id = length - i;
				content = content.replace('[' + pub.replacement_word + ' ' + gap_id + ']', '[' + pub.replacement_word + ']');
				i++
			}
			if (content.length !== starting_length) {
				let inst = tinymce.get(pub.textarea);
				setCursorPositionTiny(inst , getCursorPositionTiny(inst) - (starting_length - content.length));
			}
			pub.setTextAreaValue(content);
			createNewGapCode();
			return;
		}
		if (length < longMenuQuestion.questionParts.list.length) {
			checkDataConsistencyCallback();
		}
	};
	
	//Public property
	pub.textarea  			= '';
	pub.trigger				= '';
	pub.replacement_word 	= '';
	pub.gap_regexp			= '';
	pub.active_gap          = -1;
	pub.callbackActiveGapChange = {};
	pub.callbackClickedInGap = {};
	pub.callbackCleanGapCode = {};
	pub.callbackNewGap = {};
	pub.checkDataConsistencyAfterGapRemoval = {};

	pub.Init = () =>
	{
		appendGapTrigger();
		if (!isTinyActive()) {
			bindTextareaHandlerVanilla();
			return;
		}

		if (isTinyActiveInTextArea()) {
			bindTextareaHandlerTiny(tinyMCE.get(pub.textarea));
			return;
		}
		
		let tinyMutationObserver = new MutationObserver(() => {
			if (isTinyActiveInTextArea()) {
				bindTextareaHandlerTiny(tinyMCE.get(pub.textarea));
				tinyMutationObserver.disconnect();
			}
		});
		
		tinyMutationObserver.observe(document.getElementById(pub.textarea), {attributes: true});
	};
	pub.getTextAreaValue = () =>
	{
		let text;
		if (isTinyActiveInTextArea())
		{
			text = tinymce.get(pub.textarea).getContent();
		}
		else {
			let textarea =  document.getElementById(pub.textarea);
			text = textarea.value;
		}
		return text;
	};

	pub.setTextAreaValue = (text) =>
	{
		let cursor, inGap, inst;
		if (isTinyActiveInTextArea())
		{
			if (navigator.userAgent.indexOf('Firefox') !== -1)
			{
				text = text.replace(new RegExp('(<p>(&nbsp;)*<\/p>(\n)*)' , 'g'), '')
			}
			//ToDo: Bug in tiny steals focus on setContent (tinymce Bug #6423)
			inst = tinyMCE.activeEditor;
			cursor = getCursorPositionTiny(inst);
			inst.setContent(text);
			inGap = cursorInGap(cursor);
			
			if(inGap[1] !== -1 )
			{
				pub.active_gap = parseInt(inGap[0], 10);
			}
			
			setCursorPositionTiny(inst, parseInt(cursor, 10));
		}
		else
		{
			let textarea =  document.getElementById(pub.textarea);
			cursor = textarea.selectionStart;
			textarea.value = text;
			inGap = cursorInGap(cursor + 1);
			if(inGap[1] !== -1)
			{
				while (textarea.value[cursor - 1] !== ']') {
					cursor++;
				}
				if(pub.active_gap === -1)
				{
					setCaretPosition( document.getElementById(pub.textarea), cursor);
				}
				else
				{
					textarea.selectionStart = pub.active_gap;
					textarea.selectionEnd = pub.active_gap;
				}
				pub.active_gap = parseInt(inGap[0], 10);
			}
			setCaretPosition(document.getElementById(pub.textarea), parseInt(cursor, 10));
		}
	};
	
	//Return just the public parts
	return pub;
})();