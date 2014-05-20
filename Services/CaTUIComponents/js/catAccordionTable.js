$(document).ready(function() {
	var funs = {};
	
	var expand = (function (funs) { return function(ev) {
		var tgt = $(ev.target);
		tgt.addClass("cat_accordion_button_deexp")
			.removeClass("cat_accordion_button_exp")
			.off("click")
			.on("click", funs.deexpand);
		
		var tr = tgt.parents("tr");
		tr.next().css("display", "table-row");
	}})(funs);
	
	var deexpand = (function (funs) { return function(ev) {
		var tgt = $(ev.target);
		tgt.addClass("cat_accordion_button_exp")
			.removeClass("cat_accordion_button_deexp")
			.off("click")
			.on("click", funs.expand);
		
		var tr = tgt.parents("tr");
		tr.next().css("display", "none");
	}})(funs);

	funs.expand = expand;
	funs.deexpand = deexpand;

	$(".cat_accordion_button_exp").on("click", expand);
	$(".cat_accordion_button_deexp").on("click", deexpand);
});