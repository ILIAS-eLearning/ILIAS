var longMenuQuestion = (function () {
	var pub = {};
	var new_question_part = { 'correct_answers' : [] };
	pub.questionParts, pub.answers;

	function buildAndInitGapWizard()
	{
		var gap_wizard = GapInsertingWizard;
		gap_wizard.textarea 		= 'longmenu_text';
		gap_wizard.trigger_id		= '#gaptrigger';
		gap_wizard.replacement_word = 'Longmenu';
		gap_wizard.show_end			= false;

		pub.questionParts.replacement_word = gap_wizard.replacement_word;

		gap_wizard.callbackActiveGapChange 	= function (){debugPrinter('clicked ' + gap_wizard.active_gap)};
		gap_wizard.callbackClickedInGap 	= function ()
		{
			var gap = gap_wizard.active_gap - 1;
			$('#title_' + gap)[0].scrollIntoView( true );
		};
		gap_wizard.callbackNewGap = function (gap_id, gap_value)
		{
			sliceInNewQuestionPart(gap_id);
		};
		gap_wizard.callbackCleanGapCode 	= function (){debugPrinter('cleanup done')};
		gap_wizard.Init();
	}

	function appendFormParts()  {
		var footer_class 	= $('.ilFormFooter');
		var new_title 		= $(".gap_title").find('.ilFormHeader').clone().addClass('longmenu_head longmenu');
		var title 			= 0;
		$.each(pub.questionParts.list , function( index, value ) {
			footer_class.parent().append(new_title.clone());
			title = index + 1;
			$(document).find('.longmenu_head').last().find('.ilHeader')
				.attr('id', 'title_' + index)
				.html(pub.questionParts.replacement_word + ' ' + title);
			appendSelectBox(footer_class, index);
			appendAnswersOverview(footer_class, index);
		});
		footer_class.appendTo( '#form_assLongMenu');
		addEditListeners();
	}

	function appendSelectBox(footer_class, index)  {
		var id = 'select_type_' + index;
		footer_class.parent().append($('#layout_dummy_select').clone().attr({'id': id}).addClass('longmenu'));
		var selector = $('#' + id);
		selector.find('label').html(long_menu_language.type);
	}

	function appendAnswersOverview(footer_class, index)  {
		var id = 'answer_overview_' + index;
		footer_class.parent().append($('#layout_dummy_answers').clone().attr('id', id).addClass('longmenu'));
		var selector = $('#' + id);
		selector.find('label').html(long_menu_language.answers);
		var html = buildAnswerOverview(index);
		selector.find('.form-inline').html(html);
		appendPointsField(footer_class, index);
	}

	function appendPointsField(footer_class, index)  {
		var id = 'points_' + index;
		var name = 'points[' + index + ']';
		footer_class.parent().append($('#layout_dummy_points').clone().attr({'id': id, 'name' : name}).addClass('longmenu'));
	}

	function buildAnswerOverview(question_id)  {
		var length 	= pub.answers[question_id].length;
		var html 	= '';
		if( length > 0 )
		{
			html =	'<p>' + long_menu_language.answer_options + pub.answers[question_id].length;
			html += ' <a data-id="' + question_id +
			'" class="answer_options">' +
			long_menu_language.edit + '</a></p>';
			html += '<p>' + long_menu_language.correct_answers;
			var answers = '';
			$.each(pub.questionParts.list[question_id].correct_answers , function( index, value ) {
				answers += value + ', ';
			});
			html += ' ' + answers.substring(0, answers.length - 2) +
			'<a data-id="' + question_id +
			'" class="correct_answers">' + long_menu_language.edit + '</a></p>';
		}
		else
		{
			html = 	' <a data-id="' + question_id +
			'" class="answer_options">' +
			long_menu_language.add_answers + '</a></p>';
		}

		return html;
	}

	function addEditListeners()  {
		$( '.answer_options' ).on( "click", function() {
			var gap_id = $( this ).attr('data-id');
			debugPrinter('answer_options ' + gap_id );
			$('#ilGapModal').modal('show');
			appendModalTitle(long_menu_language.answer_options, gap_id);
			var html = $('#layout_dummy_upload').clone().html();
			html += long_menu_language.manual_editing + '<div class="modal_answer_options"></div>';
			html += $('#layout_dummy_buttons').clone().attr('id', '').html();
			$('.modal-body').html(html);
			$('.modal-body').find('.upload').attr('id', 'fileinput');
			document.getElementById('fileinput').addEventListener('change', readSingleFile, false);
			redrawAnswerList(gap_id);
			appendModalCloseListener();
		});
		$( '.correct_answers' ).on( "click", function() {
			var gap_id = $( this ).attr('data-id');
			debugPrinter('correct_answers ' +  gap_id );
			$('#ilGapModal').modal('show');
			appendModalTitle(long_menu_language.correct_answers, gap_id);
			$('.modal-body').html('');
		});
	}

	function appendModalTitle(text, question_id)
	{
		var modal_title = $('.modal-title');
		var modal_header= $('.modal-header');
		modal_header.find('.help-block').remove();
		modal_title.html(pub.questionParts.replacement_word + ' ' +	text +	question_id);
		modal_title.attr('data-id', question_id);
		modal_header.append($('.layout_dummy_help-block').html());
		modal_header.find('.help-block').html(long_menu_language.info_text_gap);
	}

	function appendModalCloseListener()
	{
		$('#ilGapModal').off('hidden.bs.modal');
		$('#ilGapModal').on('hidden.bs.modal', function () {
			redrawFormParts();
			redrawAnswerList(parseInt($('.modal-title').attr('data-id'), 10));
		});
	}

	function redrawFormParts()
	{
		debugPrinter('redraw form parts');
		$('.longmenu').remove();
		appendFormParts();
	}

	function redrawAnswerList(question_id)
	{
		debugPrinter('redraw answer list');
		checkAnswersArray(question_id);
		var html = '';
		var buttons = $('.layout_dummy_add_remove_buttons').html();
		if(inputFieldsStillPossible(question_id))
		{
			$.each(pub.answers[question_id] , function( index, value ) {
				html += '<input type="text" class="col-sm-10 text-right answerlist" size="5" value="' +
				value + '" data-id="' + index + '">' + buttons;
			});
			if(html == '')
			{
				html += '<input type="text" class="col-sm-10 text-right answerlist" size="5" value="" data-id="0">' + buttons;
			}
			$('.modal_answer_options').html(html);
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
		redrawFormParts();
	}
	function redrawAnswerListFast(question_id, gap_id, addRow)
	{
		debugPrinter('redraw answer list fast');
		debugPrinter('New gap ' + gap_id + ' in quesstion ' + question_id);
		checkAnswersArray(question_id);
		var buttons = $('.layout_dummy_add_remove_buttons').html();
		if(inputFieldsStillPossible(question_id))
		{
			if(addRow)
			{
				$('.answerlist').eq(gap_id).before('<input type="text" class="col-sm-10 text-right answerlist" size="5" value="">' + buttons);
			}
			else
			{
				$('.answerlist').eq(gap_id).next().remove();
				$('.answerlist').eq(gap_id).remove();
			}
			appendAnswerCloneButtonEvents();
			recalculateAnswerlistDataIds();
		}
		redrawFormParts();
	}
	function recalculateAnswerlistDataIds()
	{
		$.each($('.answerlist') , function( index, value ) {
			$(this).attr('data-id', index);
		});
	}

	function appendAnswerCloneButtonEvents()
	{
		var add 	= $( '.clone_fields_add' );
		var remove 	= $( '.clone_fields_remove' );
		var save 	= $( '.save-modal' );
		var cancel 	= $( '.cancel-modal' );

		add.off( "click");
		add.on( "click", function() {
			var gap_id 		= $(this).parent().prev().attr('data-id');
			var question_id = $('.modal-title').attr('data-id');
			pub.answers[question_id].splice(gap_id,0,[]);
			redrawAnswerListFast(question_id,gap_id, true);
			return false;
		});
		remove.off( "click");
		remove.on( "click", function() {
			var gap_id 		= $(this).parent().prev().attr('data-id');
			var question_id = $('.modal-title').attr('data-id');
			if(pub.answers[question_id].length > 1)
			{
				pub.answers[question_id].splice(gap_id,1);
				redrawAnswerListFast(question_id,gap_id, false);
			}
			return false;
		});
		save.off( "click");
		save.on( "click", function() {
			var gap_id 		= $('.modal-title').attr('data-id');
			var answers		= [];
			if(inputFieldsStillPossible(gap_id))
			{
				$.each($('.answerlist') , function() {
					answers.push($(this).attr('value'));
				});
			}
			else
			{
				answers  = $('.input-large').attr('value').split('\n');
			}
			pub.answers[gap_id] = answers;
			$('#ilGapModal').modal('hide');
			return false;
		});
		cancel.off( "click");
		cancel.on( "click", function() {
			$('#ilGapModal').modal('hide');
			return false;
		});

	}

	function inputFieldsStillPossible(gap_id)
	{
		return pub.answers[gap_id].length < pub.questionParts.max_input_fields;
	}

	function syncWithCorrectAnswers(question_id)
	{
		var to_remove = [];
		$.each(pub.questionParts.list[question_id].correct_answers , function( index, value ) {
			if ($.inArray(value, pub.answers[question_id]) == -1 )
			{
				to_remove.push(index);
			}
		});
		to_remove.sort(function(a, b){return b-a});
		$.each(to_remove , function( index, position ) {
			debugPrinter('value on pos ' + position + ' removed because it no part of the answer list anymore.');
			pub.questionParts.list[question_id].correct_answers.splice(position, 1);
		});
	}

	function checkAnswersArray(question_id)
	{
		var result = [];
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
		var removed = pub.answers[question_id].length - result.length;
		pub.answers[question_id] = result;
		debugPrinter(removed + ' duplicate or empty elements where removed.');
		debugPrinter(pub.answers[question_id].length + ' Answer for gap ' + question_id);
		syncWithCorrectAnswers(question_id);
		syncWithHiddenTextField();
	}

	function syncWithHiddenTextField()
	{
		$('#hidden_text_files').attr('value', JSON.stringify(pub.answers));
	}

	function sliceInNewQuestionPart(gap_id)
	{
		pub.questionParts.list.splice(gap_id, 0, new_question_part);
		pub.answers = forceArray(pub.answers);
		pub.answers.splice(gap_id,0,[]);
		redrawFormParts();
	}

	function readSingleFile (evt){
		if ( longMenuQuestion.filereader_usable )
		{
			var file = evt.target.files[0];
			if (file) {
				var reader = new FileReader();
				var textType = /text.*/;
				if (file.type.match(textType)) {
					reader.onload = function(e) {
						var contents 	= e.target.result;
						var question_id	= $('.modal-title').attr('data-id');
						pub.answers[question_id] = contents.split('\n');
						pub.answers[question_id] = pub.answers[question_id].sort();
						redrawAnswerList(question_id);
					};
					reader.readAsText(file);
				}
				else{
					alert('Filetype not supported')
				}
			} else {
				alert("Failed to load file");
			}
		}
		else
		{
			alert('The File APIs are not fully supported by your browser.');
		}
	}

	function forceArray(obj)
	{
		var array = $.map(obj, function(value, index) {
			return [value];
		});
		return array;
	}

	//Public property

	pub.Init = function()
	{
		buildAndInitGapWizard();
		appendFormParts();
		syncWithHiddenTextField();
		if (window.File && window.FileReader && window.FileList && window.Blob)
		{
			longMenuQuestion.filereader_usable = true;
		}
	};

	//Return just the public parts
	return pub;
}());