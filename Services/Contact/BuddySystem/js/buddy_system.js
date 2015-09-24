(function($, $scope) {
	$scope.il.BuddySystem = {
		config: {},

		setConfig: function (config) {
			var bs = $scope.il.BuddySystem;
			bs.config = config;
		}
	};

	$scope.il.BuddySystemButton = {
		config: {},

		setConfig: function (config) {
			var btn = $scope.il.BuddySystemButton;
			btn.config = config;
		},

		init: function () {
			var btn = $scope.il.BuddySystemButton, bs = $scope.il.BuddySystem;

			var trigger_selector = "a[data-target-state], button[data-target-state]";
			$("." + btn.config.bnt_class).on("click", trigger_selector, function (e) {
				var $trigger = $(this);

				if ($trigger.data("submitted") === true) {
					// Prevent concurrent requests
					return;
				}

				e.preventDefault();
				e.stopPropagation();

				var $container = $trigger.closest('.' + btn.config.bnt_class);

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
						$("." + btn.config.bnt_class).filter(function() {
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
								$($scope).trigger("il.bs.stateChange.beforeButtonWidgetReRendered", [$container.data("buddy-id"), response.state, state]);

								$("." + btn.config.bnt_class).filter(function() {
									return $(this).data("buddy-id") == $container.data("buddy-id");
								}).each(function() {
									var container = $(this);
									container.find(".button-container").html(response.state_html);
									container.data("current-state", response.state);
								});

								$($scope).trigger("il.bs.stateChange.afterButtonWidgetReRendered", [$container.data("buddy-id"), response.state, state]);
							}
						}
					}

					$("." + btn.config.bnt_class).filter(function() {
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

					$($scope).trigger("il.bs.stateChange.afterStateChangePerformed", [$container.data("buddy-id"), $container.data("current-state"), state]);
				}).fail(function () {
					$("." + btn.config.bnt_class).filter(function() {
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

	$($scope).on("il.bs.stateChange.afterStateChangePerformed", function(event, usr_id, is_state, was_state) {
		if (
			(was_state == "ilBuddySystemLinkedRelationState" || was_state == "ilBuddySystemRequestedRelationState") && is_state != was_state
		) {
			if (typeof il.Awareness != "undefined") {
				il.Awareness.reload();
			}
		}
		return true;
	});

	$(document).ready(function() {
		$("#awareness_trigger").on("awrn:shown", function(event) {
			$("#awareness-content").find("a[data-target-state]").off("click").on("click", function(e) {
				var bs = $scope.il.BuddySystem,
					$elm = $(this),
					usr_id = $elm.data("buddy-id");

				e.preventDefault();
				e.stopPropagation();

				var values = {};
				values["usr_id"] = usr_id;
				values["action"] = $elm.data("action");
				values["cmd[" + bs.config.transition_state_cmd + "]"] = 1;

				var promise = $.ajax({
					url:        bs.config.http_post_url,
					type:       "POST",
					data:       values,
					dataType:   "json",
					beforeSend: function () {
					}
				});

				promise.done(function (response) {
					var state = $elm.data("current-state");
					if (response.success != undefined) {
						if (response.state != undefined) {
							if (state != response.state) {
								$($scope).trigger("il.bs.stateChange.afterStateChangePerformed", [usr_id, response.state, state]);
							}
						}
					}
				});
			});
		});
	});
})(jQuery, window);