(function (root, scope, factory) {
	scope.Modal = factory(root.jQuery);
}(window, il, function init($) {

	var templates = {
		modal:       '<div class="modal fade" tabindex="-1" role="dialog">' +
					 '<div class="modal-dialog" role="document">' +
					 '<div class="modal-content">' +
					 '<div class="modal-body"></div>' +
					 '</div>' +
					 '</div>' +
					 '</div>',
		header:      '<div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h3 class="ilHeader modal-title"></h3></div>',
		footer:      '<div class="modal-footer"></div>',
		buttons:     {
			button: '<button type="button"></button>',
			link:   "<a></a>"
		}
	};

	var defaults = {
		header:        null,
		body:          null,
		backdrop:      true,
		closeOnEscape: true,
		cleanupFooterOnclose: true,
		show:          true,
		onShow:        function () {
		},
		onHide:        function () {
		},
		onShown:        function () {
		}
	};

	function each(collection, iterator) {
		var index = 0;
		$.each(collection, function (key, value) {
			iterator(key, value, index++);
		});
	}

	var methods = {};

	methods.dialogue = function (options) {
		if ($.fn.modal === undefined) {
			throw new Error(
				"$.fn.modal is not defined; please double check you have included " +
				"the Bootstrap JavaScript library. See http://getbootstrap.com/javascript/ " +
				"for more details."
			);
		}

		var props = $.extend({}, defaults, options), $modal = (function () {
			var $elm;
			if (options.id !== undefined) {
				$elm = $("#" + options.id);
				if ($elm.length !== 1) {

					// alex change start
					$elm = $(templates.modal);
					$elm.attr("id", options.id);
					$("body").append($elm);
					//throw new Error(
					//	"Please pass a modal id which matches exactly one DOM element."
					//);
					// alex change end
				}
			} else {
				$elm = $(templates.modal);
				$elm.attr("id", String.fromCharCode(65 + Math.floor(Math.random() * 26)) + Date.now());
				$("body").append($elm);
			}
			return $elm;
		}()), buttons = props.buttons;

		if (props.header !== null) {
			if (0 === $modal.find("." + $(templates.header).attr("class")).length) {
				$modal.find(".modal-content").prepend($(templates.header));
			}

			$modal.find(".modal-header .modal-title").html(props.header);
		}

		if (props.body !== null) {
			$modal.find(".modal-body").html(props.body);
		}

		var number_of_buttons = $.map(buttons, function (n, i) {
			return i;
		}).length;

		if (number_of_buttons > 0) {
			if (0 === $modal.find("." + $(templates.footer).attr("class")).length) {
				$modal.find(".modal-content").append($(templates.footer));
			}

			var $modal_footer = $modal.find('.' + $(templates.footer).attr("class"));

			each(buttons, function (key, button, index) {
				var $button;

				if (
					(!button.type || !templates.buttons[button.type]) &&
					(!button.id)
				) {
					throw new Error(
						"Please define a valid button type or specify an existing button by passing an id."
					);
				}

				if (button.id) {
					$button = $('#' + button.id);
					if ($button.length !== 1) {
						throw new Error(
							"Please define a valid button id."
						);
					}
				} else {
					if (!button.className) {
						if (number_of_buttons <= 2 && index === 0) {
							button.className = "btn btn-primary";
						} else {
							button.className = "btn btn-default";
						}
					}

					if (!button.label) {
						button.label = key;
					}

					$button = $(templates.buttons[button.type]);
					$button.text(button.label);
					$button.addClass(button.className);
				}

				if ($.isFunction(button.callback)) {
					$button.on("click", function (e) {
						button.callback.call($button, e, $modal);
					});
				}

				if (!button.id) {
					$modal_footer.append($button);
				}
			});
		}

		$modal.on("show.bs.modal", function (e) {
			if ($.isFunction(props.onShow)) {
				props.onShow.call(this, e, $modal);
			}
		});
		$modal.on("shown.bs.modal", function (e) {
			if ($.isFunction(props.onShow)) {
				props.onShown.call(this, e, $modal);
			}
		});
		$modal.on("hide.bs.modal", function (e) {
			if ($.isFunction(props.onHide)) {
				props.onHide.call(this, e, $modal);
			}
			// alex change: added if
			if (props.cleanupFooterOnclose && $modal_footer) {
				$modal_footer.html("");
			}
		});

		$modal.modal({
			keyboard: props.closeOnEscape,
			backdrop: props.backdrop,
			show:     false
		});

		if (props.show) {
			$modal.modal("show");
		}

		return {
			show: function() {
				$modal.modal("show");
			},
			hide: function() {
				$modal.modal("hide");
			},
			modal: $modal
		};
	};

	return methods;
}));