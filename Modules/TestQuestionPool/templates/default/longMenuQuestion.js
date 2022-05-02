/*globals debugPrinter, long_menu_language, GapInsertingWizard, alert, performance, JSON */
var longMenuQuestion = (function () {
	'use strict';
	
	var pub = {},
	    pro = {},
	    pri = {};
	var temp_answers = [];
	pub.questionParts = []; 
	pub.answers = [];
	pri.ignoreCallbackItemOnRedraw = true;
	pri.gapTypeText = 1;

	pro.buildAndInitGapWizard = function()
	{
		var gap_wizard = GapInsertingWizard;
		gap_wizard.textarea 		= 'longmenu_text';
		gap_wizard.trigger_id		= '#gaptrigger';
		gap_wizard.replacement_word = pub.questionParts.gap_placeholder;
		gap_wizard.show_end			= false;

		pub.questionParts.replacement_word = gap_wizard.replacement_word;

		gap_wizard.callbackActiveGapChange 	= function (){debugPrinter('clicked ' + gap_wizard.active_gap);};
		gap_wizard.callbackClickedInGap 	= function ()
		{
			var gap         = gap_wizard.active_gap - 1;
			pro.scrollToPageObject('#title_' + gap);
		};
		gap_wizard.callbackNewGap = function (gap_id)
		{
			pro.sliceInNewQuestionPart(gap_id);
		};
		gap_wizard.callbackCleanGapCode 	= function (){debugPrinter('cleanup done');};
		gap_wizard.checkDataConsistencyAfterGapRemoval = function ( existing_gaps )
		{
			pro.checkDataConsistency(existing_gaps);
		};
		
		gap_wizard.Init();
	};

	pro.checkDataConsistency = function (existing_gaps)
	{
		pub.questionParts.list  = $().ensureNoArrayIsAnObjectRecursive(pub.questionParts.list);
		pub.answers             = $().ensureNoArrayIsAnObjectRecursive(pub.answers);
		if(existing_gaps.length === 0 )
		{
			pub.answers = [];
			pub.questionParts.list = [];
			debugPrinter('checkDataConsistency removed all gaps.');
		}
		/*else if(existing_gaps.length > pub.questionParts.list.length )
		{
			debugPrinter(existing_gaps);
			debugPrinter(pub.questionParts.list);
			pro.redrawFormParts();
		}*/
		else
		{
			var answers = [];
			var list    = [];
			var t0 = pro.benchmarkCallsDummyNotForUsage('checkDataConsistency');
			$.each(pub.questionParts.list , function( index ) {
				if($.inArray(index + 1, existing_gaps) !== -1)
				{
					answers.push(pub.answers[index]);
					list.push(pub.questionParts.list[index]);
				}
			});
			pro.benchmarkCallsDummyNotForUsage('checkDataConsistency', t0);
			pub.answers = answers;
			pub.questionParts.list = list;
		}
		pro.redrawFormParts();
		pro.syncWithHiddenTextField();
		debugPrinter('consistency check');
	};
	
	pro.appendFormParts = function()  {
		var footer_class 	= $('.ilFormFooter').last();
		var new_title 		= $('.gap_title').find('.ilFormHeader').clone().addClass('longmenu_head longmenu');
		var title 			= 0;
		var t0 = pro.benchmarkCallsDummyNotForUsage('appendFormParts');
		$.each(pub.questionParts.list , function( index ) {
			footer_class.parent().append(new_title.clone());
			title = parseInt(index, 10) + 1;
			console.log(index, title)
			$(document).find('.longmenu_head').last().find('.ilHeader')
				.attr('id', 'title_' + index)
				.html(pub.questionParts.replacement_word + ' ' + title);
			pro.appendSelectBox(footer_class, index);
			pro.appendAnswersOverview(footer_class, index);
			pro.buildCorrectAnswersFormInPage(index);
		});
		pro.benchmarkCallsDummyNotForUsage('appendFormParts', t0);
		footer_class.appendTo( '#form_assLongMenu');
		pro.addEditListeners();
		pro.addPointsListener();
		pro.addSelectsListener();
		pro.addAutocompleteListener();
	};

	pro.appendSelectBox = function (footer_class, index)  {
		var id = 'select_type_' + index;
		footer_class.parent().append($('#layout_dummy_select').clone().attr({'id': id}).addClass('longmenu'));
		var selector = $('#' + id);
		selector.find('label').html(long_menu_language.type);
		selector.find('.form-control').attr('name', 'long_menu_type[]').attr({'data-id': index}).val(pub.questionParts.list[index][2]);
	};

	pro.appendAnswersOverview = function(footer_class, index)  {
		var id = 'answer_overview_' + index;
		footer_class.parent().append($('#layout_dummy_answers').clone().attr({'id': id}).addClass('longmenu'));
		var selector = $('#' + id);
		selector.find('label').html(long_menu_language.answers + ' <span class="asterisk">*</span>');
		var html = pro.buildAnswerOverview(index);
		selector.find('.form-inline').html(html);
		pro.appendPointsField(footer_class, index);
	};

	pro.appendPointsField = function (footer_class, index)  {
		var id = 'points_' + index;
		var name = 'points[' + index + ']';
		footer_class.parent().append($('#layout_dummy_points').clone().attr({'id': id}).addClass('longmenu'));
		$('#' + id).find('input').attr({'name' : name, 'data-id' : index}).addClass('points').val(pub.questionParts.list[index][1]);
		pro.appendErrorHandlerHtml(footer_class, index);
	};

	pro.buildAnswerOverview = function (question_id)  {
		var length 	= pub.answers[question_id].length;
		var html 	= '';
		if( length > 0 )
		{
			html =	'<p>' + long_menu_language.answer_options + ' ' + pub.answers[question_id].length;
			html += ' <a data-id="' + question_id +
					'" class="answer_options"> ' +
					long_menu_language.edit + '</a></p>';
			html += '<p>' + long_menu_language.correct_answers;
			html += '<span data-id="' + question_id + '" class="correct_answers"></span></p>';
		}
		else
		{
			html = 	' <a data-id="' + question_id +
					'" class="answer_options">' +
					long_menu_language.add_answers + '</a></p>';
		}
		return html;
	};

	pro.addPointsListener = function()  {
		$( '.points' ).on( 'blur', function() {
			var question_id = parseInt($(this).attr('data-id'), 10);
			pub.questionParts.list[question_id][1] = $(this).val();
			pro.syncWithHiddenTextField();
			pro.displayErrors(question_id);
		});
	};

	pro.addEditListeners = function()  {
		$( '.answer_options' ).on( 'click', function() {
			pro.answerOptionsClickFunction($(this));
		});
	};

	pro.addSelectsListener = function()  {
		$( '.type_selection' ).on( 'change', function() {
			pro.selectChangeFunction($(this));
			pro.ensureAutoCompleteIsPossibleWithTextInput();
		});
	};

	pro.addAutocompleteListener = function()  {
		$( '#min_auto_complete' ).on( 'blur', function() 
		{
			pro.ensureAutoCompleteIsPossibleWithTextInput();
		});
	};

	pro.ensureAutoCompleteIsPossibleWithTextInput = function()
	{
		$('.autocomplete_error').addClass('prototype_long_menu');
		$.each(pub.questionParts.list , function( index ) {
			if(pub.questionParts.list[index][2] == pri.gapTypeText)
			{
				pro.ensureAutoCompleteIsPossibleWithAllValues(index);
			}
		});
	};

	pro.ensureAutoCompleteIsPossibleWithAllValues = function(gap_index)
	{
		var min_length_autocomplete = $('#min_auto_complete').val();
		var constraint_violation = false;

		$.each(pub.answers[gap_index], function(index, value) {
			if(value.length < min_length_autocomplete)
			{
				constraint_violation = true;
				return false;
			}
		});

		if(constraint_violation)
		{
			$('#' +'error_answer_' + gap_index).find('.autocomplete_error').removeClass('prototype_long_menu');
		}
	};
	
	pro.selectChangeFunction = function (that)
	{
		var question_id = parseInt(that.attr('data-id'), 10);
		pub.questionParts.list[question_id][2] = that.val();
	};
	
	pro.answerOptionsClickFunction = function (that)
	{
		var gap_id = that.attr('data-id');
		var modal_body = $('#ilGapModal .modal-body');
		temp_answers = pub.answers[gap_id];
		debugPrinter('answer_options ' + gap_id );
		pro.appendModalTitle(long_menu_language.answer_options, gap_id);
		modal_body.html(pro.appendUploadButtons());
		modal_body.find('.upload').attr('id', 'fileinput');
		document.getElementById('fileinput').addEventListener('change', pro.readSingleFile, false);
		pro.savePossibleChangedPoints();
		pro.redrawAnswerList(gap_id);
		pro.appendModalCloseListener();
		$('#ilGapModal').modal('show');
	};

	pro.savePossibleChangedPoints = function ()
	{
		$.each($('.points') , function( index) {
			pub.questionParts.list[index][1] = $(this).val();
		});
	};
	pro.buildCorrectAnswersFormInPage = function()
	{
		$('.correct_answers').each(function() {
			var question_id = $(this).attr('data-id');
			var dom_object = $('#taggable').clone().attr({
															'id' : 'tagsinput_' + question_id,
															'class' : 'correct_answers',
															'data-id' : question_id
															});
			$(this).parent().html(dom_object);
			$('#' +'tagsinput_' + question_id).parent().prepend(long_menu_language.correct_answers);
			ilBootstrapTaggingOnLoad.appendId('#tagsinput_' + question_id);
			ilBootstrapTaggingOnLoad.appendTerms(question_id, pub.answers[question_id]);
			pri.ignoreCallbackItemOnRedraw = true;
			ilBootstrapTaggingOnLoad.callbackItemAdded = function ()
			{
				pri.saveTagInputsToHiddenFieldsOnCallback();
			};
			ilBootstrapTaggingOnLoad.callbackItemRemoved = function ()
			{
				pri.saveTagInputsToHiddenFieldsOnCallback();
			};
			ilBootstrapTaggingOnLoad.Init();
			$.each(pub.questionParts.list[question_id][0], function (index) {
				$('#tagsinput_' + question_id).tagsinput('add', pub.questionParts.list[question_id][0][index]);
			});
			pri.ignoreCallbackItemOnRedraw = false;
		});
	};
	
	pro.appendErrorHandlerHtml = function(footer_class, index)
	{
		footer_class.parent().append($('#error_answer').clone().attr({'id': 'error_answer_' + index}).addClass('longmenu'));
		pro.displayErrors(index);
	};
	
	pro.displayErrors = function(index)
	{
		var value_error = false;
		if(parseFloat(pub.questionParts.list[index][1]) <= 0)
		{
			$('#' +'error_answer_' + index).find('.points_error').removeClass('prototype_long_menu');
		}
		else
		{
			$('#' +'error_answer_' + index).find('.points_error').addClass('prototype_long_menu');
		}
		if(pub.questionParts.list[index][0].length === 0)
		{
			$('#' +'error_answer_' + index).find('.value_error').removeClass('prototype_long_menu');
			value_error = true;
		}
		else
		{
			$('#' +'error_answer_' + index).find('.value_error').addClass('prototype_long_menu');
		}
		if( pub.answers[index].length === 0)
		{
			$('#' +'error_answer_' + index).find('.value_error').removeClass('prototype_long_menu');
		}
		else if(!value_error)
		{
			$('#' +'error_answer_' + index).find('.value_error').addClass('prototype_long_menu');
		}
	};
	
	pri.saveTagInputsToHiddenFieldsOnCallback = function()
	{
		if(pri.ignoreCallbackItemOnRedraw === false)
		{
			$('.correct_answers').each(function() {
				var question_id = parseInt($(this).attr('id').split('_')[1], 10);
				pro.saveCorrectAnswersToHiddenField(question_id);
				pro.displayErrors(question_id);
			});
		}
	};
	
	pro.saveCorrectAnswersToHiddenField = function(question_id)
	{
		var elements    =   $('#tagsinput_' + question_id).tagsinput('items');
		if(elements === null)
		{
			elements = [];
		}
		pub.questionParts.list[question_id][0] = elements;
		pro.syncWithCorrectAnswers(question_id);
		pro.syncWithHiddenTextField();
	};
	
	pro.appendUploadButtons = function()
	{
		var html = $('#layout_dummy_upload').clone().html();
		html += long_menu_language.manual_editing + '<div class="modal_answer_options"></div>';
		html += $('#layout_dummy_buttons').clone().attr('id', '').html();
		return html;
	};
	
	pro.appendModalTitle = function(text, question_id)
	{
		var modal_title = $('#ilGapModal .modal-title');
		var modal_header= $('#ilGapModal .modal-header');
		var view_id     = parseInt(question_id,10) + 1;
		modal_header.find('.help-block').remove();
		modal_header.append($('.layout_dummy_help-block').html());
		modal_header.find('.help-block').html(long_menu_language.info_text_gap);
		modal_title.attr('data-id', question_id)
			.html(pub.questionParts.replacement_word + ' ' + view_id + ' ' + text );
	};

	pro.appendModalCloseListener = function()
	{
		var modal_object = $('#ilGapModal');
		modal_object.off('hidden.bs.modal');
		modal_object.on('hidden.bs.modal', pro.redrawFormParts);
	};

	pro.redrawFormParts = function()
	{
		debugPrinter('redraw form parts');
		$('.longmenu').remove();
		pro.appendFormParts();
		pro.ensureAutoCompleteIsPossibleWithTextInput();
	};

	pro.redrawAnswerList = function(question_id)
	{
		debugPrinter('redraw answer list');
		pro.checkAnswersArray(question_id);
		var buttons = $('.layout_dummy_add_remove_buttons').html();
		var html = '';
		if(pro.inputFieldsStillPossible(question_id))
		{
			var t0 = pro.benchmarkCallsDummyNotForUsage('redrawAnswerList');
			$.each(pub.answers[question_id] , function( index, value ) {
				html += '<input type="text" class="col-sm-10 answerlist" size="5" value="' +
						value + '" data-id="' + index + '">' + buttons;
			});
			if(html === '')
			{
				html += '<input type="text" class="col-sm-10 answerlist" size="5" value="" data-id="0">' + buttons;
			}
			pro.benchmarkCallsDummyNotForUsage('redrawAnswerList only html build', t0);
			$('#ilGapModal .modal_answer_options').html(html);
			pro.benchmarkCallsDummyNotForUsage('redrawAnswerList', t0);
		}
		else
		{
			html += '<textarea rows="25" cols="70" class="input-large">';
			$.each(pub.answers[question_id] , function( index, value ) {
				html += value + '\n';
			});
			html += '</textarea>';
			$('#ilGapModal .modal_answer_options').html(html);
		}
		pro.appendAnswerCloneButtonEvents();
		pro.redrawFormParts();
	};
	
	pro.redrawAnswerListFast = function(gap_id, answer_id, addRow)
	{
		debugPrinter('redraw answer list fast');
		var answerList_object = $('.answerlist');
		pro.checkAnswersArray(gap_id);
		if(pro.inputFieldsStillPossible(gap_id))
		{
			if(addRow)
			{
				debugPrinter('Added answer ' + answer_id + ' in gap ' + gap_id);
				var buttons = $('.layout_dummy_add_remove_buttons').html();
				var input_string = 'type="text" class="col-sm-10 answerlist" size="5" value=""';
				answerList_object.eq(answer_id).next().after('<input ' + input_string + '>' + buttons);
			}
			else
			{
				debugPrinter('Removed answer ' + answer_id + ' in gap ' + gap_id);
				answerList_object.eq(answer_id).next().remove();
				answerList_object.eq(answer_id).remove();
			}
			pro.appendAnswerCloneButtonEvents();
			pro.recalculateAnswerListDataIds();
		}
		pro.redrawFormParts();
	};
	
	pro.recalculateAnswerListDataIds = function()
	{
		var t0 = pro.benchmarkCallsDummyNotForUsage('recalculateAnswerListDataIds');
		$.each($('.answerlist') , function( index) {
			$(this).attr('data-id', index);
		});
		pro.benchmarkCallsDummyNotForUsage('recalculateAnswerListDataIds', t0);
	};

	pro.appendAnswerCloneButtonEvents = function()
	{
		var t0 = pro.benchmarkCallsDummyNotForUsage('appendAnswerCloneButtonEvents');
		pro.appendAddButtonEvent();
		pro.appendRemoveButtonEvent();
		pro.appendSaveModalButtonEventAnswers();
		pro.appendCancelModalButtonEvent();
		pro.benchmarkCallsDummyNotForUsage('appendAnswerCloneButtonEvents', t0);
	};

	pro.appendAddButtonEvent = function()
	{
		pro.appendAbstractCloneButtonEvent( '.clone_fields_add' , function (gap_id, question_id )
		{
			temp_answers.splice(gap_id,0,[]);
			pro.redrawAnswerListFast(question_id, gap_id, true);
		});
	};

	pro.appendRemoveButtonEvent = function()
	{
		pro.appendAbstractCloneButtonEvent( '.clone_fields_remove' , function (gap_id, question_id )
		{
			if(temp_answers.length > 1)
			{
				temp_answers.splice(gap_id,1);
				pro.redrawAnswerListFast(question_id, gap_id, false);
			}
		});
	};
	pro.appendAbstractCloneButtonEvent = function(classElement, eventCallback)
	{
		var button 	= $(classElement);
		button.off( 'click');
		button.on( 'click', function() {
			var gap_id 		= $(this).parent().prev().attr('data-id');
			var question_id = $('#ilGapModal .modal-title').attr('data-id');
			if (typeof eventCallback === 'function') {
				eventCallback(gap_id, question_id);
			}
			return false;
		});
	};

	pro.appendSaveModalButtonEventAnswers = function()
	{
		pro.appendAbstractModalButtonEvent( '.save-modal' , pro.saveModalEventAnswers);
	};

	pro.saveModalEventAnswers = function()
	{
		var gap_id 		= $('#ilGapModal .modal-title').attr('data-id');
		var answers		= [];
		if(pro.inputFieldsStillPossible(gap_id))
		{
			var t0 = pro.benchmarkCallsDummyNotForUsage('protect.appendSaveModalButtonEventAnswers');
			$.each($('#ilGapModal .answerlist') , function() {
				answers.push($(this).val());
			});
			pro.benchmarkCallsDummyNotForUsage('protect.appendSaveModalButtonEventAnswers', t0);
		}
		else
		{
			answers  = $('.input-large').val().split('\n');
		}
		pub.answers[gap_id] = answers;
		pro.checkAnswersArray(gap_id);
	};
	
	pro.appendCancelModalButtonEvent = function()
	{
		pro.appendAbstractModalButtonEvent( '.cancel-modal' , function (){});
	};

	pro.appendAbstractModalButtonEvent = function(classElement, eventCallback)
	{
		var button 	= $(classElement);
		button.off( 'click');
		button.on( 'click', function() {
			if (typeof eventCallback === 'function') {
				eventCallback();
			}
			$('#ilGapModal').modal('hide');
			return false;
		});
	};

	pro.inputFieldsStillPossible = function(gap_id)
	{
		return pub.answers[gap_id].length < pub.questionParts.max_input_fields;
	};

	pro.syncWithCorrectAnswers = function (question_id)
	{
		var to_remove = [];
		var t0 = pro.benchmarkCallsDummyNotForUsage('syncWithCorrectAnswers');
		if(longMenuQuestion.questionParts.list[question_id][0].length > 0)
		{
			$.each(pub.questionParts.list[question_id][0] , function( index, value ) {
				if ($.inArray(value, pub.answers[question_id]) === -1 )
				{
					to_remove.push(index);
				}
			});
		}
		pro.removeNonExistingCorrectAnswersByKey(question_id, to_remove);
		pro.benchmarkCallsDummyNotForUsage('syncWithCorrectAnswers', t0);
	};

	pro.removeNonExistingCorrectAnswersByKey = function(question_id, to_remove)
	{
		to_remove.sort(function(a, b){ return b - a; } );
		$.each(to_remove , function( index, position ) {
			debugPrinter('value on pos ' + position + ' removed because it is no part of the answer list anymore.');
			pub.questionParts.list[question_id][0].splice(position, 1);
		});
	};
	
	pro.checkAnswersArray = function (question_id)
	{
		var result = [];
		var t0 = pro.benchmarkCallsDummyNotForUsage('checkAnswersArray');
		$.each(pub.answers[question_id], function(index, value) {
			value = value.toString().replace(/"/g,'');
			if ($.inArray(value, result) === -1 )
			{
				if( value !== '' )
				{
					result.push(value);
				}
			}
		});
		pro.benchmarkCallsDummyNotForUsage('checkAnswersArray', t0);
		var removed = pub.answers[question_id].length - result.length;
		pub.answers[question_id] = result.sort();
		debugPrinter(removed + ' duplicate or empty elements where removed.');
		debugPrinter(pub.answers[question_id].length + ' Answers for gap ' + question_id);
		pro.syncWithCorrectAnswers(question_id);
		pro.syncWithHiddenTextField();
	};

	pro.syncWithHiddenTextField = function() 
	{
		$('#hidden_text_files').val(JSON.stringify(pub.answers));
		$('#hidden_correct_answers').val(JSON.stringify(pub.questionParts.list));
	};

	pro.sliceInNewQuestionPart = function (gap_id)
	{
		pub.questionParts.list.splice(gap_id, 0,  [[], '0', '1']);
		pub.answers.splice(gap_id,0,[]);
		pro.redrawFormParts();
		pro.syncWithHiddenTextField();
	};

	pro.readSingleFile = function (evt){
		if ( pub.filereader_usable )
		{
			var file = evt.target.files[0];
			if (file) 
			{
				var reader = new FileReader();
				var textType = /text.*/;
				if (file.type.match(textType)) 
				{
					reader.onload = function(e) 
					{
						var contents 	= e.target.result;
						var gap_id	= $('#ilGapModal .modal-title').attr('data-id');
						pub.answers[gap_id] = contents.split('\n');
						pub.answers[gap_id] = pub.answers[gap_id].sort();
						pro.redrawAnswerList(gap_id);
					};
					reader.readAsText(file);
				}
				else
				{
					alert('Filetype not supported');
				}
			} 
			else 
			{
				alert('Failed to load file');
			}
		}
		else
		{
			alert('The File APIs are not fully supported by your browser.');
		}
	};
	
	pro.scrollToPageObject = function(object)
	{
		var headerSize  = parseInt($('#ilTopBar').height(), 10) + parseInt($('.ilMainHeader').height(), 10);
		$('html, body').animate(
			{ 
				scrollTop: $(object).offset().top - headerSize	
			}, 200);
	};
	
	pro.benchmarkCallsDummyNotForUsage = function(function_caller, t0)
	{
		if(t0 === undefined)
		{
			return performance.now();
		}
		else
		{
			var t1 = performance.now();
			debugPrinter('Call to ' + function_caller + ' took ' + (t1 - t0) + ' milliseconds.');
		}
	};

	pro.ensureCorrectAnswersArrayExistAndIsEmpty = function(gap_id)
	{
		if(pub.questionParts.list === undefined)
		{
			pub.questionParts.list = [];
		}
		pub.questionParts.list[gap_id] = [[], '0', '1'];
	};
	
	//Public property

	pub.Init = function()
	{
		pro.buildAndInitGapWizard();
		pro.appendFormParts();
		pro.syncWithHiddenTextField();
		pub.questionParts.list = $().ensureNoArrayIsAnObjectRecursive(pub.questionParts.list);
		pub.answers = $().ensureNoArrayIsAnObjectRecursive(pub.answers);
		if (window.File && window.FileReader && window.FileList && window.Blob)
		{
			pub.filereader_usable = true;
		}
		else
		{
			//Todo: implement workaround
			alert('FileReader not usable, implement workaround.');
		}
		pro.ensureAutoCompleteIsPossibleWithTextInput();
	};
	
	//Return just the public parts
	pub.protect = pro;
	return pub;
}());

(function ( $ ) {
	'use strict';
	$.fn.ensureNoArrayIsAnObjectRecursive = function( obj ) {
		if ($.type(obj) === 'object' || $.type(obj) === 'array'){
			Object.keys(obj).forEach(function(key) { obj[key] = jQuery().ensureNoArrayIsAnObjectRecursive(obj[key]); });
			obj = $.map(obj, function(value) { return [value];	});
		}
		return obj;
	};}( jQuery ));
