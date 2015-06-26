(function ($) {

	window.il.BuddySystemButton = {
		config: {},

		setConfig: function (config) {
			var bs = il.BuddySystemButton;
			bs.config = config;
		},

		init: function () {
			var bs = il.BuddySystemButton;

			var trigger_selector = "a[data-target-state], button[data-target-state]";
			$("." + bs.config.bnt_class).on("click", trigger_selector, function (e) {
				var $trigger = $(this);

				if ($trigger.data("submitted") === true) {
					// Prevent concurrent requests
					return;
				}

				e.preventDefault();
				e.stopPropagation();

				var $container = $trigger.closest('.' + bs.config.bnt_class);

				var values = {};
				values["usr_id"] = $container.data("buddy-id");
				values["action"] = $trigger.data("action");
				values["cmd[" + bs.config.transition_state_cmd + "]"] = 1;

				var promise = $.ajax({
					url:        bs.config.http_post_url,
					type:       "POST",
					data:       values,
					dataType:   "json",
					beforeSend: function () {
						$("." + bs.config.bnt_class).filter(function() {
							return $(this).data("buddy-id") == $container.data("buddy-id");
						}).each(function() {
							var container = $(this);
							container.find(trigger_selector)
								.data("submitted", true)
								.attr("disabled", true);
						});
					}
				});

				promise.done(function (response) {
					var state = $container.data("current-state");

					if (response.success != undefined) {
						if (response.state != undefined && response.state_html != undefined) {
							if (state != response.state) {
								$(window).trigger("il.bs.stateChange.beforeWidgetReRendered", [$container.data("buddy-id"), response.state, state]);

								$("." + bs.config.bnt_class).filter(function() {
									return $(this).data("buddy-id") == $container.data("buddy-id");
								}).each(function() {
									var container = $(this);
									container.find(".button-container").html(response.state_html);
									container.data("current-state", response.state);
								});

								$(window).trigger("il.bs.stateChange.afterWidgetReRendered", [$container.data("buddy-id"), response.state, state]);
							}
						}
					}

					$("." + bs.config.bnt_class).filter(function() {
						return $(this).data("buddy-id") == $container.data("buddy-id");
					}).each(function() {
						var container = $(this);
						container.find(trigger_selector)
							.data("submitted", false)
							.attr("disabled", false);
					});

					if (response.message != undefined) {
						$container.find("button").popover({
							container: "body",
							content:   response.message,
							placement: "auto",
							trigger:   "focus"
						}).popover("show");
						$container.find("button").focus().on("hidden.bs.popover", function () {
							$(this).popover("destroy");
						});
					}

					$(window).trigger("il.bs.stateChange.afterStateChangePerformed", [$container.data("buddy-id"), $container.data("current-state"), state]);
				}).fail(function () {
					$("." + bs.config.bnt_class).filter(function() {
						return $(this).data("buddy-id") == $container.data("buddy-id");
					}).each(function() {
						var container = $(this);
						container.find(trigger_selector)
							.data("submitted", false)
							.attr("disabled", false);
					});
				});
			});
		}
	};

	$.fn["ilBuddySystemList"] = function (method) {
		var pluginId = "ilBuddySystemList";

		var methods = {
			init:       function (params) {
				var $this = $(this);

				if ($this.size() > 1) {
					throw new Error(pluginId + " can only be used for an selector matching exactly one DOM element!");
				}

				// prevent double initialization
				if ($this.data(pluginId)) {
					return;
				}

				var data = $.extend(true, {}, {
					_items:     {},
					_num_items: 0
					},
					params
				);
				$this.data(pluginId, data);
			},
			add:        function (buddy, fn) {
				var $this = $(this);

				if ($this.data(pluginId)._items[buddy.usr_id]) {
					return $this;
				}

				var $row = fn(buddy);

				$row.data(pluginId, buddy);
				$this.data(pluginId)._items[buddy.usr_id] = $row;
				$this.data(pluginId)._num_items++;
				$this.append($row);


				return $this[pluginId]('sort');
			},
			sort:       function () {
				var $this = $(this), tmp = [];

				$.each($this.data(pluginId)._items, function (i) {
					tmp.push({usr_id: i, data: this});
				});

				tmp.sort(function (a, b) {
					return (a.data.data(pluginId).ts < b.data.data(pluginId).ts) ? 1 : -1;
				});

				for (var i = 0; i < tmp.length; ++i) {
					$this.append(tmp[i].data);
				}

				return $this;
			},
			removeById: function (id) {
				var $this = $(this);

				var $row = $this.data(pluginId)._items[id];
				if ($row) {
					$row.remove();
					delete $this.data(pluginId)._items[id];
					$this.data(pluginId)._num_items--;
					return true;
				}
				return false;
			},
			getById:    function (id) {
				var $this = $(this);

				var $row = $this.data(pluginId)._items[id];
				if ($row) {
					return $row;
				}
				return null;
			},
			num:        function () {
				return $(this).data(pluginId)._num_items;
			}
		};

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.' + pluginId);
		}
	};


	$.fn["ilBuddySystemNumberOfRequestsRenderer"] = function (method) {
		var pluginId = "ilBuddySystemNumberOfRequestsRenderer";

		var methods = {
			init: function (params) {
				return this.each(function() {
					var $this = $(this);

					var data = $.extend(true, {}, {
							num: 0
						},
						params
					);
					$this.data(pluginId, data);
				});
			},
			setValue: function(value) {
				return this.each(function() {
					var $this = $(this), data = $this.data(pluginId);
					data.num = value; 
					$this.data(pluginId, data);
				});
			},
			render: function() {
				return this.each(function() {
					var $this = $(this), data = $this.data(pluginId);

					$this.html(function(num_requests) {
						if (0 == num_requests) {
							return "";
						}

						return num_requests > 9 ? "9+" : num_requests;
					}(data.num));
				});
			}
		};

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.' + pluginId);
		}
	};
})(jQuery);