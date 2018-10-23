il = il || {};
il.UI = il.UI || {};
il.UI.button = il.UI.button || {};
(function($, il) {
	il.UI.button = (function($) {

		/* month button */
		var initMonth = function (id) {
			$("#" + id).find(".inline-picker").each(function(o) {
				$(this).datetimepicker({
					inline: true,
					sideBySide: true,
					viewMode: "months",
					format: "MM/YYYY",
					defaultDate: $(this).parent().data("defaultDate"),
					locale: $(this).parent().data("lang")
				}).on("dp.change", function (ev) {
					var i, d, months = [];
					var d = new Date(ev.date);
					var m = d.getMonth() + 1;
					m = ("00" + m).substring(m.toString().length);

					for (i = 1; i <= 12; i++) {
						months.push(il.Language.txt("month_" + (("00" + i).substring(i.toString().length)) + "_short"));
					}

					$("#" + id + " span.il-current-month").html(months[d.getMonth()] + " " + d.getFullYear());
					$("#" + id).trigger("il.ui.button.month.changed", [id, m + "-" + d.getFullYear()]);
				});
			});
		};

		/* toggle button */
		var handleToggleClick = function (event, id, on_url, off_url, signals) {
			var b = $("#" + id);
			var pressed = b.attr("aria-pressed");
			for (var i = 0; i < signals.length; i++) {
				var s = signals[i];
				if (s.event === "click" ||
					(pressed === "true" && s.event === "toggle_on") ||
					(pressed !== "true" && s.event === "toggle_off")
				) {
					$(b).trigger(s.signal_id, {
						'id' : s.signal_id,
						'event' : s.event,
						'triggerer' : b,
						'options' : s.options});
				}
			}

			if (pressed === "true" && on_url !== '') {
				window.location = on_url;
			}

			if (pressed !== "true" && off_url !== '') {
				window.location = off_url;
			}

			//console.log('handleToggelClick: ' + id);
			return false;
		};

		return {
			initMonth: initMonth,
			handleToggleClick: handleToggleClick
		};
	})($);
})($, il);

// toggle init
$(document).ready(function() {
	$('.il-toggle-button.on').attr("aria-pressed", "true");

    $('.il-toggle-button').click(function() {
        $(this).toggleClass('.il-toggle-button on').toggleClass('.il-toggle-button');

        if ($(this).attr("aria-pressed") == "false") {
            $(this).attr("aria-pressed", "true");
        } else {
            $(this).attr("aria-pressed", "false");
        }
    });
});