/**
 * ilDataCollection JS
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

$(document).ready(function () {
	$(".dcl_reference_record").hover(
		function () {
			var ref = $(this).attr("rec_id");
			$(".dcl_reference_hover[rec_id=" + ref + "]").fadeIn(0);
		},
		function () {
			var ref = $(this).attr("rec_id");
			$(".dcl_reference_hover[rec_id=" + ref + "]").fadeOut(0);
		});


	/**
	 * Increment comments count after saving comment with ajax
	 * @param o Object
	 */
	if (typeof ilNotes != 'undefined') {
		ilNotes.callbackSuccess = function (o) {
			if (o && o.argument.mode == 'cmd') {
				var $elem = $('tr.dcl_comments_active .dcl_comment').find('.ilHActProp');
				var count = parseInt($elem.text());
				$elem.html(++count);
			}
		}
	}


	var dcl = {};

	dcl.removeHighlightedRows = function () {
		$('.dcl_comments_active').removeClass('dcl_comments_active');
	};

	/**
	 * Formula-Fields can't be unique or required
	 */
	dcl.checkFormulaFieldsAttribute = function () {
		return $('#datatype_11').attr('checked') == 'checked';
	}

	dcl.changeStateForFormulaFields = function () {
        var state = dcl.checkFormulaFieldsAttribute();
        var required = $('#required');
        required.prop('disabled', state);
        var unique = $('#unique');
        unique.prop('disabled', state);
	}

	dcl.changeStateForFormulaFields();

	$('#datatype').change(dcl.changeStateForFormulaFields);

	/**
	 * @var $tr tr object to highlight
	 */
	dcl.highlightRow = function ($tr) {
		this.removeHighlightedRows();
		$tr.addClass('dcl_comments_active');
	};

	$('a.dcl_comment').click(function () {
		$tr = $(this).parents('tr');
		dcl.highlightRow($tr);
	});

	$('.dcl_actions a[id$="comment"]').click(function () {
		$tr = $(this).parents('td.dcl_actions').parent('tr');
		dcl.highlightRow($tr);
	});

	$('#fixed_content').click(function () {
		dcl.removeHighlightedRows();
	});

	/**
	 * Formula fields
	 */
	$('a.dclPropExpressionField').click(function () {
		var placeholder = '[[' + $(this).attr('data-placeholder') + ']]';
		var $expression = $('#prop_12');
		var caretPos = document.getElementById('prop_12').selectionStart;
		var expression = $expression.val();
		$expression.val(expression.substring(0, caretPos) + placeholder + expression.substring(caretPos));
	});

	/**
	 * Ajax record form submit in Overlay
	 */
	$(document).on('submit', 'form[id^="form_dclajax"]', function (event) {
		event.preventDefault();
		var data = $(this).serialize();
		ilDataCollection.saveRecordData(data);
		return false;
	});

	$('form[id^="form_dcl"] select[data-ref=1]').parent('div').append(
		$('<a></a>')
			.attr('href', '#')
			.addClass('ilDclReferenceAddValue xsmall')
			.text('[+] ' + ilDataCollection.strings.add_value)
	);


	$('form[id^="form_dcl"] div[class="input"]').parent('div').append(
		$('<a></a>')
			.attr('href', '#')
			.addClass('ilDclReferenceAddValueMS xsmall')
			.text('[+] ' + ilDataCollection.strings.add_value)
	);


	$('.ilDclReferenceAddValue').on('click', function () {
		var $elem = $(this);
		var $select = $elem.prev('select');
		var table_id = $select.attr('data-ref-table-id');
		var field_id = $select.attr('data-ref-field-id');
		var after_save = function (o) {
			var $input = $('form[id^="form_dclajax"] #record_id');
			if ($input.length) {
				var record_id = $input.val();
				ilDataCollection.getRecordData(record_id, function (o) {
					var record_data = $.parseJSON(o.responseText);
					var new_value = record_data[field_id];
					// Append to select and select new value
					$select.append($('<option>', {
						value: record_id,
						text: new_value
					}));
					$select.find('option[value=' + record_id + ']').attr('selected', 'selected');
				});
			}
		};
		ilDataCollection.showCreateRecordOverlay(table_id, after_save);
	});

	$('.ilDclReferenceAddValueMS').on('click', function () {
		var $elem = $(this);
		var $div = $elem.prev('div');
		var table_id = $div.attr('data-ref-table-id');
		var field_id = $div.attr('data-ref-field-id');
		var current_id = $div.find('input').attr("name");

		var after_save = function (o) {
			var $input = $('form[id^="form_dclajax"] #record_id');
			if ($input.length) {
				var record_id = $input.val();
				ilDataCollection.getRecordData(record_id, function (o) {
					var record_data = $.parseJSON(o.responseText);
					var new_value = record_data[field_id];
					// Append to select and select new value
					var new_input = '<div style="white-space:nowrap">' +
						'<input type="checkbox" name="' + current_id + '" id="' + current_id + '_' + record_id + '" value="' + record_id + '" checked="checked"/>' +
						'<label for="' + current_id + '_' + record_id + '">' + new_value + '</label></div>';


					$div.prepend(new_input);
					$div.find('option[value=' + record_id + ']').attr('selected', 'selected');
				});
			}
		};
		ilDataCollection.showCreateRecordOverlay(table_id, after_save);
	});

	$('[data-toggle="datacollection-tooltip"]').tooltip({container: 'body'});
});