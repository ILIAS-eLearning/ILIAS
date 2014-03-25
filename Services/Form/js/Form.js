il.Form = {

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
		il.Form.initLinkInput();
	},

	// hide sub forms
	hideSubForm: function (id) {
		id = il.Form.escapeSelector(id);
		$("#" + id).css('overflow', 'hidden').css('height', '0px').css('display', 'none');
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

		$("#" + cont_id + " div.ilSubForm[id!='" + id + "']").animate({
			height: 0
		}, 400, function () {
			$(this).css('display', 'none');

			// activated in the meantime?
			for (m = 0; m < il.Form.sub_active.length; m++) {
				if (il.Form.escapeSelector(this.id) == il.Form.sub_active[m]) {
					$(this).css('display', '');
				}
			}
			$(this).css('height', 'auto');
		});

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
			obj.style.overflow = 'hidden';

			obj.style.display = '';
			$(obj).animate({
				height: nh
			}, 400, function () {
				$(this).css('height', 'auto');
			});
		}

		// deactivate subform of checkbox
		if (obj && (cb != null && cb.checked == false)) {
			obj.style.overflow = 'hidden';

			$(obj).animate({
				height: 0
			}, 400, function () {
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
			$("input[name=" + Form.escapeSelector(id) + "_ajax_type]").val('');
			$("input[name=" + Form.escapeSelector(id) + "_ajax_id]").val('');
			$("input[name=" + Form.escapeSelector(id) + "_ajax_target]").val('');
			$("#" + il.Form.escapeSelector(id) + "_value").html('');
			$(this.parentNode).css('display', 'none');
			console.log(id);
		});
	},
	
	// set internal link in form item
	addInternalLink: function (link, title, input_id, ev) {
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
			
			if (kcode == 190) {
				// decimals are not allowed
				if (decimals_allowed == undefined || decimals_allowed == 0) {
					event.preventDefault();
				} else {
					// decimal point is only allowed once
					current = $('#' + id).val();
					if (current.indexOf('.') > -1) {
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
	}
	
};

// init forms
il.Util.addOnLoad(il.Form.init);
