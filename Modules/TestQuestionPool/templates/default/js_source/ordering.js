$.fn.ilOrderingQuestion = function(method) {
	var internals = {
		storeResult: function() {
			var data = this.data('ilOrderingQuestion'), result = this.find(data.properties.result_value_selector).map(function() {
				return $(this).attr('id');
			}).get();
			$(data._result_elm).val(result.join(data.properties.result_separator));
			if (data.properties.debug) {
				console.log("Result stored: " + $(data._result_elm).val());
			}
		},

		buildFromArray: function(order) {
			var data = this.data('ilOrderingQuestion');
			for (var i = order.length - 1; i > 0; i--) {
				$("#" + order[i]).before($("#" + order[i- 1]));
			}
			if (data.properties.debug) {
				console.log("Built order by array: " + order.join(','));
			}

			internals.storeResult.call(this);
		}
	};

	var methods = {
		init: function(params) {
			return this.each(function() {
				var $this = $(this);

				// prevent double initialization
				if ($this.data('ilOrderingQuestion')) {
					return;
				}

				var data = {
					properties: $.extend(
						true, {}, {
							debug                      : false,
							sortable_selector          : 'ul.vertical',
							result_value_selector      : '.ilOrderingValue',
							result_separator           : '',
							build_result_on_form_submit: false,
							reset_selector             : '',
							initial_order              : []
						}, params
					)
				};

				if (data.properties.debug) {
					console.log("Current element: ", this);
				}

				$this.data('ilOrderingQuestion', $.extend(data, {
					// Maybe pass this element as a parameter to make it robust against changes in the html code
					_result_elm: $this.find('input[type=hidden]')
				}));

				if (data.properties.build_result_on_form_submit) {
					$this.closest('form').submit(function () {
						if (data.properties.debug) {
							console.log("Submitted form");
						}
						internals.storeResult.call($this);
					});
				}

				if (!data.properties.initial_order.length) {
					$this.find(data.properties.result_value_selector).each(function() {
						data.properties.initial_order.push($(this).attr('id'));
					});
				}

				internals.buildFromArray.call($this, data.properties.initial_order);

				$this.find(data.properties.reset_selector).on('click', function(e) {
					if (data.properties.debug) {
						console.log("Clicked reset button");
					}
					internals.buildFromArray.call($this, data.properties.initial_order);

					e.preventDefault();
					e.stopPropagation();
				});

				$this.find(data.properties.sortable_selector).sortable({
					opacity: 0.6,
					revert: true,
					cursor: "move",
					axis: "y",
					stop: function() {
						internals.storeResult.call($this);
					}
				}).disableSelection();
			});
		},
		saveOrder: function() {
			return this.each(function() {
				if ($(this).data('ilOrderingQuestion').properties.debug) {
					console.log("Storing result, delegated by global scope");
				}
				internals.storeResult.call($(this));
			});
		}
	};

	if (methods[method]) {
		return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
	} else if (typeof method === "object" || !method) {
		return methods.init.apply(this, arguments);
	} else {
		$.error("Method " + method + " does not exist on jQuery.ilOrderingQuestion");
	}
};