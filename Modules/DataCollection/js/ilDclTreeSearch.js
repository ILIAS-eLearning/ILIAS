/**
 * DataCollection JS object
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

(function ($, window, document, undefined) {

	var pluginName = "ilDclTreeSearch",
		defaults = {
			ajaxLink: "",
			fieldId: ""
		};

	function Plugin(element, options) {
		this.element = element;
		this.options = $.extend({}, defaults, options);
		this._defaults = defaults;
		this._name = pluginName;
		this.init();
	}

	Plugin.prototype = {
		init: function () {
			this.form = $('form[id^="form_dcl"]');
			this.main_field = this.form.find('#' + this.options.fieldId);
			this.remove_icon = this.form.find('#remove_' + this.options.fieldId);
			this.display_field = this.form.find('#display_' + this.options.fieldId);
			this.loader = this.form.find('#dcl_loader_' + this.options.fieldId);
			this.search_button = this.form.find('#search_button_' + this.options.fieldId);
			this.search_field = this.form.find('#search_' + this.options.fieldId);
			this.search_output = this.form.find('#search_output_' + this.options.fieldId);
			this.search_result = this.form.find('.dcl_result_' + this.options.fieldId);
			this.loader.hide();
			var $obj = this;

			if (this.display_field.val().length == 0) {
				this.toggleDisplayField('off');
			} else {
				this.toggleDisplayField('on');
			}

			this.search_button.click(function () {
				$obj.doSearch($obj);
			});

			this.search_field.keydown(function (event) {
				if (event.keyCode == 13) {
					event.preventDefault();
					$obj.doSearch($obj);
				}
			});

			$(document).on('click', 'form[id^="form_dcl"] .dcl_result_' + this.options.fieldId, function () {
				var set_id = this.id;
				var title = this.text;
				$obj.main_field.val(set_id);
				$obj.display_field.val(title);
				$obj.toggleDisplayField('on');
				$('form[id^="form_dcl"] .dcl_result_' + $obj.options.fieldId).remove();
			});


			this.remove_icon.click(function () {
				$(this).hide();
				$obj.display_field.val('').hide();
				$obj.main_field.val('');
				$obj.display_field.parent('div').attr('style', '');
			});


		},

		toggleDisplayField: function (mode) {
			if (mode == 'on') {
				this.display_field.show().parent('div').attr('style', 'display:inline-block');
				this.remove_icon.show();
			} else {
				this.display_field.hide().parent('div').attr('style', '');
				this.remove_icon.hide();
			}
		},

		doSearch: function (self) {
			var value = self.search_field.val();
			var link = self.options.ajaxLink;
			var replacer = new RegExp('amp;', 'g');
			link = link.replace(replacer, '');
			self.loader.show();
			self.search_output.load(link, {search_for: value, dest: self.options.fieldId}, function () {
				self.loader.hide();
			});
		}
	};

	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, "plugin_" + pluginName)) {
				$.data(this, "plugin_" + pluginName,
					new Plugin(this, options));
			}
		});
	};

})(jQuery, window, document);