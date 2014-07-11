$(document).ready( function () {
	var expand =  function(ev) {
		if (ev.which != 1 && ev.which != 0)
			return;

		ev.preventDefault();

		var target = $(ev.currentTarget);
		var tep_event = target.parent().parent();

		//Cache height and width to restore it later
		tep_event.attr("originalWidth", tep_event.width());
		tep_event.attr("originalHeight", tep_event.height());

		if (tep_event.width() < 150)
			tep_event.width(150);
		if(tep_event.height() < 200)
			tep_event.height(200);

		tep_event.css("z-index", 100);

		target.parent().children(".il_tep_expand").css("visibility", "hidden");
		target.parent().children(".il_tep_fold").css("visibility", "visible");
	}

	var fold = function(ev) {
		if (ev.which != 1 && ev.which != 0)
			return;

		ev.preventDefault();

		var target = $(ev.currentTarget);
		var tep_event = target.parent().parent();

		tep_event.width(tep_event.attr("originalWidth"));
		tep_event.height(tep_event.attr("originalHeight"));

		tep_event.css("z-index", 1);

		target.parent().children(".il_tep_expand").css("visibility", "visible");
		target.parent().children(".il_tep_fold").css("visibility", "hidden");
	}

	$(".il_tep_expander .il_tep_expand").on("click", expand);
	$(".il_tep_expander .il_tep_fold").on("click", fold);

})