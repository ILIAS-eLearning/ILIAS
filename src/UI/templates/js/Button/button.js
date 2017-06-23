var il = il || {};
il.UI = il.UI || {};

(function($, il) {

	$(function() {
		$(".il-btn-month").each(function () {
			var id = this.getAttribute("id");
			$(this).find(".inline-picker").
			datetimepicker({
				inline: true,
				sideBySide: true,
				viewMode: 'months',
				format: 'MM/YYYY'
			}).on('dp.change', function (ev) {
				var i, d , months = [];
				var d = new Date(ev.date);
				var m = d.getMonth() + 1;
				m = ("00" + m).substring(m.toString().length);

				for (i = 1; i<=12; i++) {
					months.push(il.Language.txt("month_" + (("00" + i).substring(i.toString().length)) + "_long"));
				}

				$("#" + id + " span.il-current-month").html(months[d.getMonth()] + " " + d.getFullYear());
				$("#" + id).trigger("il.ui.button.month.changed", [ id,  m + "-" + d.getFullYear()] );
			});
		});
	});

})($, il);