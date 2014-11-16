$.fn.ilHorizontalOrderingQuestion = function(method) {
	var internals = {
		storeResult: function() {
			var data = this.data('ilHorizontalOrderingQuestion'), result = this.find(data.properties.result_value_selector).map(function() {
				return $(this).text();
			}).get();
			$(data._result_elm).val(result.join(data.properties.result_separator));
		}
	};

	var methods = {
		init: function(params) {
			return this.each(function () {
				var $this = $(this);

				// prevent double initialization
				if ($this.data('ilHorizontalOrderingQuestion')) {
					return;
				}

				var data = {
					properties: $.extend(
						true, {}, {
							result_value_selector  : '.ilOrderingValue',
							result_separator       : '{::}'
						}, params
					)
				};

				$this.data('ilHorizontalOrderingQuestion', $.extend(data, {
					// Maybe pass this element as a parameter to make it robust against changes in the html code
					_result_elm: $this.next('input[type=hidden]')
				}));

				internals.storeResult.call($this);

				$this.sortable({
					opacity: 0.6,
					revert: false,
					cursor: "move",
					axis: "x",
					stop: function() {
						internals.storeResult.call($this);
					}
				}).disableSelection();
			});
		}
	};

	if (methods[method]) {
		return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
	} else if (typeof method === "object" || !method) {
		return methods.init.apply(this, arguments);
	} else {
		$.error("Method " + method + " does not exist on jQuery.ilHorizontalOrderingQuestion");
	}
};