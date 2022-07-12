il.Form = {

	duration : 150,

	items: {},
	
	escapeSelector: function(str) {
		return str.replace(/([;&,\.\+\*\~':"\!\^#$%@\[\]\(\)=>\|])/g, '\\$1');
	},

	sub_active: [],	// active sub forms for each context

	initItem: function (id, cfg) {
		il.Form.items[id] = cfg;
	},

	//ad
	// General functions
	//

	// init
	init: function () {
		$(() => {
			il.Form.initLinkInput();
			il.Form.registerFileUploadInputEventTrigger();
		});
	},
	
	registerFileUploadInputEventTrigger: function(selectorPrefix = '') {


        /* experimental: bootstrap'ed file upload */

        // see http://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3/

        // trigger event on fileselect
        $(document).on('change', selectorPrefix + '.btn-file :file', function() {
            var input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
            input.trigger('fileselect', [numFiles, label]);
        });

        // display selected file name
		$(selectorPrefix + '.btn-file :file').on('fileselect', function(event, numFiles, label) {
			var input = $(this).parents('.input-group').find(':text');
			if( input.length ) {
				input.val(label);
			}
		});

    },

	// hide sub forms
	hideSubForm: function (id) {
		id = il.Form.escapeSelector(id);
		$("#" + id)./*css('overflow', 'hidden').*/css('height', '0px').css('display', 'none');	
	},

	// show Subform
	showSubForm: function (id, cont_id, cb) {
		var nh, obj, k, m;

		id      = il.Form.escapeSelector(id);
		cont_id = il.Form.escapeSelector(cont_id);

		if (cb == null) {
			il.Form.sub_active[cont_id] = id;
		} else {
			if (cb.checked) {
				il.Form.sub_active[cont_id] = id;
			} else {
				il.Form.sub_active[cont_id] = null;
			}
		}

		var parent_subform = $("#" + cont_id).parents(".ilSubForm")[0];
		
		$("#" + cont_id + " div.ilSubForm[id!='" + id + "']").each(function() {
			
			// #18482 - check if subform is on same level as parent
			if(parent_subform == $(this).parents(".ilSubForm")[0]) {
				
				$(this).animate({
					height: 0
				}, il.Form.duration, function () {		
					$(this).css('display', 'none');

					// activated in the meantime?
					for (m = 0; m < il.Form.sub_active.length; m++) {
						if (il.Form.escapeSelector(this.id) == il.Form.sub_active[m]) {
							$(this).css('display', '');
						}
					}
					$(this).css('height', 'auto');
				});
				
			}
		})

		// activate subform
		obj = $("#" + id).get(0);
		if (obj && obj.style.display == 'none' && (cb == null || cb.checked == true)) {
			obj.style.display = '';
			obj.style.position = 'relative';
			obj.style.left = '-1000px';
			obj.style.display = 'block';
			nh = obj.scrollHeight;
			obj.style.height = '0px';
			obj.style.position = '';
			obj.style.left = '';
			// obj.style.overflow = 'hidden';

			obj.style.display = '';
			$(obj).animate({
				height: nh
			}, il.Form.duration, function () {
				$(this).css('height', 'auto');
			});
					
			// needed for google maps
			$(obj).closest("form").trigger("subform_activated");
		}

		// deactivate subform of checkbox
		if (obj && (cb != null && cb.checked == false)) {
			// obj.style.overflow = 'hidden';

			$(obj).animate({
				height: 0
			}, il.Form.duration, function () {
				$(this).css('display', 'none');
				// activated in the meantime?
				for (k = 0; k < il.Form.sub_active.length; k++) {
					if (il.Form.escapeSelector(this.id) == il.Form.sub_active[k]) {
						$(this).css('display', '');
					}
				}
				$(this).css('height', 'auto');
			});
		}
	},


	//
	// ilLinkInputGUI
	//

	initLinkInput: function () {
		$("a.ilLinkInputRemove").click(function (e) {
			var id = this.parentNode.id;
			id = id.substr(0, id.length - 4);
			$("input[name=" + il.Form.escapeSelector(id) + "_ajax_type]").val('');
			$("input[name=" + il.Form.escapeSelector(id) + "_ajax_id]").val('');
			$("input[name=" + il.Form.escapeSelector(id) + "_ajax_target]").val('');
			$("#" + il.Form.escapeSelector(id) + "_value").html('');
			$(this.parentNode).css('display', 'none');
			console.log(id);
		});
	},
	
	// set internal link in form item
	addInternalLink: function (link, title, input_id, ev, c) {
		var type, id, part, target = "";

		input_id = il.Form.escapeSelector(input_id);

		// #10543 - IE[8]
		var etarget = ev.target || ev.srcElement;
		
		$("#" + input_id + "_value").html($(etarget).html());

		link = link.split(' ');
		part = link[1].split('="');
		type = part[0];
		id = part[1].split('"')[0];
		if (link[2] !== undefined) {
			target = link[2].split('="');
			target = target[1].split('"')[0];
		}
		$("input[name=" + input_id + "_ajax_type]").val(type);
		$("input[name=" + input_id + "_ajax_id]").val(id);
		$("input[name=" + input_id + "_ajax_target]").val(target);
		
		$("#" + input_id + "_rem").css('display', 'block');
	},

	//
	// ilNumberInputGUI
	//

	// initialisation for number fields
	initNumericCheck: function (id, decimals_allowed) {
		var current;
	
		$('#' + il.Form.escapeSelector(id)).keydown(function (event) {
			// #10562
			var kcode = event.which;
			var is_shift = event.shiftKey;
			var is_ctrl = event.ctrlKey;

			if (kcode == 190 || kcode == 188) {
				// decimals are not allowed
				if (decimals_allowed == undefined || decimals_allowed == 0) {
					event.preventDefault();
				} else {
					// decimal point is only allowed once
					current = $('#' + id).val();
					if (
						current.indexOf('.') > -1 ||
						current.indexOf(',') > -1
					) {
						event.preventDefault();
					}
				}
			// Allow: backspace, delete, tab, escape, and enter
			} else if (kcode == 46 || kcode == 8 || kcode == 9 || kcode == 27 || kcode == 13 ||
					 // Allow: Ctrl+A
					(kcode == 65 && is_ctrl === true) ||
					 // Allow: home, end, left, right (up [38] does not matter)
					(kcode >= 35 && kcode <= 39) ||
					 // Allow: negative values (#10652)
					kcode == 173) {
				// let it happen, don't do anything
				return;
			} else {
				// Ensure that it is a number and stop the keypress (2nd block: num pad)
				if (is_shift || (kcode < 48 || kcode > 57) && (kcode < 96 || kcode > 105)) {
					event.preventDefault();
				}
			}
		});
	},
	
	//
	// ilDateDurationInputGUI
	//	
	
	initDateDurationPicker: function (picker_id, picker2_id, toggle_id, subform_id) {		
		var el = $("#"+picker_id);
		var dp = $(el).data("DateTimePicker"); 
		var el2 = $("#"+picker2_id);
		var dp2 = $(el2).data("DateTimePicker"); 	
		var txt = $(el).find("input:text");
		var txt2 = $(el2).find("input:text");
		
		
		// init		

		// set limit by current date of other picker
		/*
		if(dp2.date())
		{		
			dp.maxDate(dp2.date());
		}
		*/
		if(dp.date())
		{		
			dp2.minDate(dp.date()); 

			// store current value for diff magic 
			$(el).data("DateTimePickerOld", dp.date());
		}	


		// onchange		
		
		$(el).on("dp.change", function(e) { 
			
			// limit to value of end picker
			dp2.minDate(e.date); 

			// keep diff the same
			var old = $(this).data("DateTimePickerOld"); 

			if(old && dp2.date() && e.date) { 
				var diff = dp2.date().diff(old); 
				dp2.date(e.date.clone().add(diff)); 
			}

			// keep current date for diff parsing (see above);
			$(this).data("DateTimePickerOld", e.date);	
			
			if(subform_id !== undefined)
			{
				il.Form.handleDateDurationPickerSubForm(txt, txt2, subform_id);
			}
		});
		
		$(el2).on("dp.change", function(e) { 	
			
			/*
			// limit to value of start picker
			dp.maxDate(e.date);												  
			*/
		   
		    if(subform_id !== undefined)
			{
				il.Form.handleDateDurationPickerSubForm(txt, txt2, subform_id);
			}
		});
		
		
		// subform		
		
		if(subform_id !== undefined)
		{			
			$(el).on("dp.hide", function(e) { 
				il.Form.handleDateDurationPickerSubForm(txt, txt2, subform_id);
			});
			
			$(el2).on("dp.hide", function(e) { 
				il.Form.handleDateDurationPickerSubForm(txt, txt2, subform_id);
			});
			
			$(txt).on("input", function(e) {			
				il.Form.handleDateDurationPickerSubForm(txt, txt2, subform_id);
			});		
			
			$(txt2).on("input", function(e) {
				il.Form.handleDateDurationPickerSubForm(txt, txt2, subform_id);
			});
		}
		

		// toggle
		
		if(toggle_id)
		{
			var toggle = $("#"+toggle_id);
			var full_format = dp.format();


			// init

			if($(toggle).prop("checked")) {  
				var format = dp.format();
				dp.format(format.substr(0, 10));
				format = dp2.format();
				dp2.format(format.substr(0, 10));
			}


			// onchange

			$(toggle).change(function(e) { 

				if(!$(this).prop("checked")) {  
					dp.format(full_format); 
					dp2.format(full_format); 				
				} 
				else { 
					var short_format = full_format.substr(0, 10);
					dp.format(short_format); 
					dp2.format(short_format); 				
				}

			});	
		}
	},
	
	handleDateDurationPickerSubForm: function(el, el2, subform_id) {		
		if($(el).val() || $(el2).val())
		{
			$("#" + subform_id).show();			
		}
		else
		{
			$("#" + subform_id).hide();
		}		
	},
	
	initDatePicker: function (picker_id, subform_id) {		
		var el = $("#"+picker_id);
		var dp = $(el).data("DateTimePicker"); 
		var txt = $(el).find("input:text");
		
		// onchange
		$(el).on("dp.change", function(e) { 					
			if(subform_id !== undefined)
			{
				il.Form.handleDatePickerSubForm(txt, subform_id);						
			}		
		});	
	
		// subform		
		if(subform_id !== undefined)
		{			
			$(el).on("dp.hide", function(e) { 
				il.Form.handleDatePickerSubForm(txt, subform_id);
			});
		
			$(txt).on("input", function(e) {			
				il.Form.handleDatePickerSubForm(txt, subform_id);
			});	
		}
	},
			
	handleDatePickerSubForm: function(el, subform_id) {		
		if($(el).val())
		{
			$("#" + subform_id).show();			
		}
		else
		{
			$("#" + subform_id).hide();
		}		
	},

	//Tiny textarea char. counter
	showCharCounterTinymce: function(ed) {
		//var content_raw = ed.getContent({ format: 'raw' }); // whitespaces and br issues. (first whitespace creates br etc.)
		var content_raw = ed.getContent({ format: 'raw' });
		var content = content_raw.replace(/<\/?[^>]+(>|$)/g, "");
		// #20630, #20674
		content = content.replace(/&nbsp;/g, " ");
		content = content.replace(/&lt;/g, "<");
		content = content.replace(/&gt;/g, ">");
		content = content.replace(/&amp;/g, "&");
		console.log(content);
		var text_length = content.length;

		var max_limit = $('#textarea_feedback_'+ed.id).data("maxchars");
		if(max_limit > 0) {
			var text_remaining = max_limit - text_length;
			$('#textarea_feedback_'+ed.id).html(il.Language.txt("form_chars_remaining") + " " + text_remaining);
		}

	},
	//normal textarea char. counter
	showCharCounterTextarea: function(textarea_id, feedback_id, min_limit, max_limit) {
		var text_length = $('#'+textarea_id).val().length;
		if(max_limit > 0)
		{
			var text_remaining = max_limit - text_length;
			$('#'+feedback_id).html(il.Language.txt("form_chars_remaining") +" "+ text_remaining);
			return true;
		}

	},

};

// init forms
il.Util.addOnLoad(il.Form.init);

// see #27281
$(document).on('dp.show', function(event) {
	il.UI.page.fit($('.bootstrap-datetimepicker-widget'));
});