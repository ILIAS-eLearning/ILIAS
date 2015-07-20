var longMenuQuestion = (function () {
	var pub = {}, protect = {};
	var new_question_part = { 0 : [] };
	var temp_answers = [];
	pub.questionParts, pub.answers;

	function buildAndInitGapWizard()
	{
		var gap_wizard = GapInsertingWizard;
		gap_wizard.textarea 		= 'longmenu_text';
		gap_wizard.trigger_id		= '#gaptrigger';
		gap_wizard.replacement_word = longMenuQuestion.questionParts.gap_placeholder;
		gap_wizard.show_end			= false;

		pub.questionParts.replacement_word = gap_wizard.replacement_word;

		gap_wizard.callbackActiveGapChange 	= function (){debugPrinter('clicked ' + gap_wizard.active_gap)};
		gap_wizard.callbackClickedInGap 	= function ()
		{
			var gap         = gap_wizard.active_gap - 1;
			scrollToPageObject('#title_' + gap);
		};
		gap_wizard.callbackNewGap = function (gap_id, gap_value)
		{
			protect.sliceInNewQuestionPart(gap_id);
		};
		gap_wizard.callbackCleanGapCode 	= function (){debugPrinter('cleanup done')};
		gap_wizard.checkDataConsistencyAfterGapRemoval = function ( existing_gaps )
		{
			protect.checkDataConsistency(existing_gaps);
		};
		
		gap_wizard.Init();
	}

	protect.checkDataConsistency = function (existing_gaps)
	{
		pub.questionParts.list  = $().ensureNoArrayIsAnObjectRecursive(pub.questionParts.list);
		pub.answers             = $().ensureNoArrayIsAnObjectRecursive(pub.answers);
		if(existing_gaps.length == 0 )
		{
			console.log('YEEEEEEEEEEEEEA1')
			pub.answers = [];
			pub.questionParts.list = [];
			debugPrinter('checkDataConsistency removed all gaps.')
		}
		else if(existing_gaps.length > pub.questionParts.list.length )
		{
			console.log('YEEEEEEEEEEEEEA2')
			//Todo: fix this
			debugPrinter(existing_gaps)
			debugPrinter(pub.questionParts.list)
			protect.redrawFormParts();
		}
		else
		{
			var answers = [];
			var list    = [];
			console.log('YEEEEEEEEEEEEEA3')
			var t0 = protect.benchmarkCallsDummyNotForUsage('checkDataConsistency');
			$.each(pub.questionParts.list , function( index, value ) {
				if($.inArray(index + 1, existing_gaps) != -1)
				{
					answers.push(pub.answers[index]);
					list.push(pub.questionParts.list[index]);
				}
			});
			protect.benchmarkCallsDummyNotForUsage('checkDataConsistency', t0);
			pub.answers = answers;
			pub.questionParts.list = list;
		}
		protect.redrawFormParts();
		debugPrinter('consistency check')
	};
	
	protect.appendFormParts = function()  {
		var footer_class 	= $('.ilFormFooter');
		var new_title 		= $(".gap_title").find('.ilFormHeader').clone().addClass('longmenu_head longmenu');
		var title 			= 0;
		var t0 = protect.benchmarkCallsDummyNotForUsage('appendFormParts');
		$.each(pub.questionParts.list , function( index, value ) {
			footer_class.parent().append(new_title.clone());
			title = parseInt(index, 10) + 1;
			$(document).find('.longmenu_head').last().find('.ilHeader')
				.attr('id', 'title_' + index)
				.html(pub.questionParts.replacement_word + ' ' + title);
			protect.appendSelectBox(footer_class, index);
			protect.appendAnswersOverview(footer_class, index);
		});
		protect.benchmarkCallsDummyNotForUsage('appendFormParts', t0);
		footer_class.appendTo( '#form_assLongMenu');
		protect.addEditListeners();
	};

	protect.appendSelectBox = function (footer_class, index)  {
		var id = 'select_type_' + index;
		footer_class.parent().append($('#layout_dummy_select').clone().attr({'id': id}).addClass('longmenu'));
		var selector = $('#' + id);
		selector.find('label').html(long_menu_language.type);
	};

	protect.appendAnswersOverview = function(footer_class, index)  {
		var id = 'answer_overview_' + index;
		footer_class.parent().append($('#layout_dummy_answers').clone().attr({'id': id}).addClass('longmenu'));
		var selector = $('#' + id);
		selector.find('label').html(long_menu_language.answers);
		var html = protect.buildAnswerOverview(index);
		selector.find('.form-inline').html(html);
		protect.appendPointsField(footer_class, index);
	};

	protect.appendPointsField = function (footer_class, index)  {
		var id = 'points_' + index;
		var name = 'points[' + index + ']';
		footer_class.parent().append($('#layout_dummy_points').clone().attr({'id': id}).addClass('longmenu'));
		$('#' + id).find('input').attr('name' , name);
	};

	protect.buildAnswerOverview = function (question_id)  {
		var length 	= pub.answers[question_id].length;
		var html 	= '';
		if( length > 0 )
		{
			html =	'<p>' + long_menu_language.answer_options + pub.answers[question_id].length;
			html += ' <a data-id="' + question_id +
					'" class="answer_options"> ' +
					long_menu_language.edit + '</a></p>';
			html += '<p>' + long_menu_language.correct_answers;
			var answers = '';
			var t0 = protect.benchmarkCallsDummyNotForUsage('buildAnswerOverview');
			$.each(pub.questionParts.list[question_id][0] , function( index, value ) {
				answers += value + ', ';
			});
			html += ' ' + answers.substring(0, answers.length - 2) +
					'<a data-id="' + question_id + '" class="correct_answers"> ' + long_menu_language.edit + '</a></p>';
			protect.benchmarkCallsDummyNotForUsage('buildAnswerOverview', t0);
		}
		else
		{
			html = 	' <a data-id="' + question_id +
					'" class="answer_options">' +
					long_menu_language.add_answers + '</a></p>';
		}

		return html;
	};

	protect.addEditListeners = function()  {
		$( '.answer_options' ).on( "click", function() {
			var gap_id = $( this ).attr('data-id');
			temp_answers = pub.answers[gap_id];
			
			debugPrinter('answer_options ' + gap_id );
			protect.appendModalTitle(long_menu_language.answer_options, gap_id);
			$('.modal-body').html(protect.appendUploadButtons());
			$('.modal-body').find('.upload').attr('id', 'fileinput');
			document.getElementById('fileinput').addEventListener('change', readSingleFile, false);
			protect.redrawAnswerList(gap_id);
			protect.appendModalCloseListener();
			$('#ilGapModal').modal('show');
		});
		$( '.correct_answers' ).on( "click", function() {
			var gap_id = $( this ).attr('data-id');
			debugPrinter('correct_answers ' +  gap_id );
			protect.appendModalTitle(long_menu_language.correct_answers, gap_id);
			$('.modal-body').html('');
			$('#ilGapModal').modal('show');
		});
	};

	protect.appendUploadButtons = function()
	{
		var html = $('#layout_dummy_upload').clone().html();
		html += long_menu_language.manual_editing + '<div class="modal_answer_options"></div>';
		html += $('#layout_dummy_buttons').clone().attr('id', '').html();
		return html;
	};
	
	protect.appendModalTitle = function(text, question_id)
	{
		var modal_title = $('.modal-title');
		var modal_header= $('.modal-header');
		var view_id     = parseInt(question_id,10) + 1;
		modal_header.find('.help-block').remove();
		modal_header.append($('.layout_dummy_help-block').html());
		modal_header.find('.help-block').html(long_menu_language.info_text_gap);
		modal_title.attr('data-id', question_id)
			.html(pub.questionParts.replacement_word + ' ' + view_id + ' ' + text );
	};

	protect.appendModalCloseListener = function()
	{
		var modal_object = $('#ilGapModal');
		modal_object.off('hidden.bs.modal');
		modal_object.on('hidden.bs.modal', function () {
			protect.redrawFormParts();
		});
	};

	protect.redrawFormParts = function()
	{
		debugPrinter('redraw form parts');
		$('.longmenu').remove();
		protect.appendFormParts();
	};

	protect.redrawAnswerList = function(question_id)
	{
		debugPrinter('redraw answer list');
		protect.checkAnswersArray(question_id);
		var buttons = $('.layout_dummy_add_remove_buttons').html();
		var html = '';
		if(protect.inputFieldsStillPossible(question_id))
		{
			var t0 = protect.benchmarkCallsDummyNotForUsage('redrawAnswerList');
			$.each(pub.answers[question_id] , function( index, value ) {
				html += '<input type="text" class="col-sm-10 text-right answerlist" size="5" value="' +
						value + '" data-id="' + index + '">' + buttons;
			});
			if(html == '')
			{
				html += '<input type="text" class="col-sm-10 text-right answerlist" size="5" value="" data-id="0">' + buttons;
			}
			protect.benchmarkCallsDummyNotForUsage('redrawAnswerList only html build', t0);
			$('.modal_answer_options').html(html);
			protect.benchmarkCallsDummyNotForUsage('redrawAnswerList', t0);
		}
		else
		{
			html += '<textarea rows="30" cols="80" class="input-large">';
			$.each(pub.answers[question_id] , function( index, value ) {
				html += value + '\n';
			});
			html += '</textarea>';
			$('.modal_answer_options').html(html);
		}
		appendAnswerCloneButtonEvents();
		protect.redrawFormParts();
	};
	
	protect.redrawAnswerListFast = function(gap_id, answer_id, addRow)
	{
		debugPrinter('redraw answer list fast');
		var answerList_object = $('.answerlist');
		protect.checkAnswersArray(gap_id);
		if(protect.inputFieldsStillPossible(gap_id))
		{
			if(addRow)
			{
				debugPrinter('Added answer ' + answer_id + ' in gap ' + gap_id);
				var buttons = $('.layout_dummy_add_remove_buttons').html();
				var input_string = 'type="text" class="col-sm-10 text-right answerlist" size="5" value=""';
				answerList_object.eq(answer_id).before('<input ' + input_string + '>' + buttons);
			}
			else
			{
				debugPrinter('Removed answer ' + answer_id + ' in gap ' + gap_id);
				answerList_object.eq(answer_id).next().remove();
				answerList_object.eq(answer_id).remove();
			}
			appendAnswerCloneButtonEvents();
			protect.recalculateAnswerListDataIds();
		}
		protect.redrawFormParts();
	};
	
	protect.recalculateAnswerListDataIds = function()
	{
		var t0 = protect.benchmarkCallsDummyNotForUsage('recalculateAnswerListDataIds');
		$.each($('.answerlist') , function( index, value ) {
			$(this).attr('data-id', index);
		});
		protect.benchmarkCallsDummyNotForUsage('recalculateAnswerListDataIds', t0);
	};

	function appendAnswerCloneButtonEvents()
	{
		var t0 = protect.benchmarkCallsDummyNotForUsage('appendAnswerCloneButtonEvents');
		protect.appendAddButtonEvent();
		protect.appendRemoveButtonEvent();
		protect.appendSaveModalButtonEvent();
		protect.appendCancelModalButtonEvent();
		protect.benchmarkCallsDummyNotForUsage('appendAnswerCloneButtonEvents', t0);
	}

	protect.appendAddButtonEvent = function()
	{
		protect.appendAbstractCloneButtonEvent( '.clone_fields_add' , function (gap_id, question_id )
		{
			temp_answers.splice(gap_id,0,[]);
			protect.redrawAnswerListFast(question_id, gap_id, true);
		});
	};

	protect.appendRemoveButtonEvent = function()
	{
		protect.appendAbstractCloneButtonEvent( '.clone_fields_remove' , function (gap_id, question_id )
		{
			if(temp_answers.length > 1)
			{
				temp_answers.splice(gap_id,1);
				protect.redrawAnswerListFast(question_id, gap_id, false);
			}
		});
	};
	protect.appendAbstractCloneButtonEvent = function(classElement, eventCallback)
	{
		var button 	= $(classElement);
		button.off( "click");
		button.on( "click", function() {
			var gap_id 		= $(this).parent().prev().attr('data-id');
			var question_id = $('.modal-title').attr('data-id');
			if (typeof eventCallback === 'function') {
				eventCallback(gap_id, question_id);
			}
			return false;
		});
	};

	protect.appendSaveModalButtonEvent = function()
	{
		protect.appendAbstractModalButtonEvent( '.save-modal' , function (){
			var gap_id 		= $('.modal-title').attr('data-id');
			var answers		= [];
			if(protect.inputFieldsStillPossible(gap_id))
			{
				var t0 = protect.benchmarkCallsDummyNotForUsage('protect.appendSaveModalButtonEvent');
				$.each($('.answerlist') , function() {
					answers.push($(this).attr('value'));
				});
				protect.benchmarkCallsDummyNotForUsage('protect.appendSaveModalButtonEvent', t0);
			}
			else
			{
				answers  = $('.input-large').attr('value').split('\n');
			}
			pub.answers[gap_id] = answers;
			protect.checkAnswersArray(gap_id);
		});
	};

	protect.appendCancelModalButtonEvent = function()
	{
		protect.appendAbstractModalButtonEvent( '.cancel-modal' , function (){});
	};

	protect.appendAbstractModalButtonEvent = function(classElement, eventCallback)
	{
		var button 	= $(classElement);
		button.off( "click");
		button.on( "click", function() {
			if (typeof eventCallback === 'function') {
				eventCallback();
			}
			$('#ilGapModal').modal('hide');
			return false;
		});
	};

	protect.inputFieldsStillPossible = function(gap_id)
	{
		return pub.answers[gap_id].length < pub.questionParts.max_input_fields;
	};

	protect.syncWithCorrectAnswers = function (question_id)
	{
		var to_remove = [];
		var t0 = protect.benchmarkCallsDummyNotForUsage('syncWithCorrectAnswers');
		$.each(pub.questionParts.list[question_id][0] , function( index, value ) {
			if ($.inArray(value, pub.answers[question_id]) == -1 )
			{
				to_remove.push(index);
			}
		});
		protect.removeNonExistingCorrectAnswersByKey(question_id, to_remove);
		protect.benchmarkCallsDummyNotForUsage('syncWithCorrectAnswers', t0);
	};

	protect.removeNonExistingCorrectAnswersByKey = function(question_id, to_remove)
	{
		to_remove.sort(function(a, b){ return b - a } );
		$.each(to_remove , function( index, position ) {
			debugPrinter('value on pos ' + position + ' removed because it is no part of the answer list anymore.');
			pub.questionParts.list[question_id][0].splice(position, 1);
		});
	};
	
	protect.checkAnswersArray = function (question_id)
	{
		var result = [];
		var t0 = protect.benchmarkCallsDummyNotForUsage('checkAnswersArray');
		$.each(pub.answers[question_id], function(index, value) {
			value = value.toString().replace(/"/g,'');
			if ($.inArray(value, result) == -1 )
			{
				if( value != '' )
				{
					result.push(value);
				}
			}
		});
		protect.benchmarkCallsDummyNotForUsage('checkAnswersArray', t0);
		var removed = pub.answers[question_id].length - result.length;
		pub.answers[question_id] = result.sort();
		debugPrinter(removed + ' duplicate or empty elements where removed.');
		debugPrinter(pub.answers[question_id].length + ' Answers for gap ' + question_id);
		protect.syncWithCorrectAnswers(question_id);
		protect.syncWithHiddenTextField();
	};

	protect.syncWithHiddenTextField = function() 
	{
		$('#hidden_text_files').attr('value', JSON.stringify(pub.answers));
	};

	protect.sliceInNewQuestionPart = function (gap_id)
	{
		pub.questionParts.list.splice(gap_id, 0, new_question_part);
		pub.answers.splice(gap_id,0,[]);
		protect.redrawFormParts();
	};

	function readSingleFile (evt){
		if ( longMenuQuestion.filereader_usable )
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
						var question_id	= $('.modal-title').attr('data-id');
						pub.answers[question_id] = contents.split('\n');
						pub.answers[question_id] = pub.answers[question_id].sort();
						protect.redrawAnswerList(question_id);
					};
					reader.readAsText(file);
				}
				else
				{
					alert('Filetype not supported')
				}
			} 
			else 
			{
				alert("Failed to load file");
			}
		}
		else
		{
			alert('The File APIs are not fully supported by your browser.');
		}
	}
	
	function scrollToPageObject(object)
	{
		var headerSize  = parseInt($('#ilTopBar').height(), 10) + parseInt($('.ilMainHeader').height(), 10);
		$('html, body').animate(
			{ 
				scrollTop: $(object).offset().top - headerSize	
			}, 200);
	}
	
	protect.benchmarkCallsDummyNotForUsage = function(function_caller, t0)
	{
		if(t0 == null)
		{
			return performance.now();
		}
		else
		{
			var t1 = performance.now();
			debugPrinter("Call to " + function_caller + " took " + (t1 - t0) + " milliseconds.")
		}
	}
	
	//Public property

	pub.Init = function()
	{
		buildAndInitGapWizard();
		protect.appendFormParts();
		protect.syncWithHiddenTextField();
		pub.questionParts.list = $().ensureNoArrayIsAnObjectRecursive(pub.questionParts.list);
		pub.answers = $().ensureNoArrayIsAnObjectRecursive(pub.answers);
		if (window.File && window.FileReader && window.FileList && window.Blob)
		{
			longMenuQuestion.filereader_usable = true;
		}
		else
		{
			//Todo: implement workaround
			alert('FileReader not usable, implement workaround.')
		}
	};

	//Return just the public parts
	pub.protected = protect;
	return pub;
}());

(function ( $ ) {
	$.fn.ensureNoArrayIsAnObjectRecursive = function( obj ) {
		if ($.type(obj) === 'object' || $.type(obj) === 'array'){
			Object.keys(obj).forEach(function(key) { obj[key] = jQuery().ensureNoArrayIsAnObjectRecursive(obj[key]); });
			obj = $.map(obj, function(value) { return [value];	});
		}
		return obj;
	};}( jQuery ));