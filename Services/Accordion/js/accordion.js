function ilInitAccordion(id, toggle_class, toggle_active_class, content_class, width, height, direction)
{
	var horizontalAccordion = new accordion(id, {
		classNames : {
			toggle : toggle_class,
			toggleActive : toggle_active_class,
			content : content_class
		},
		defaultSize : {
			width : width,
			height : height
		},
		direction : direction
	});
}

/*
function initAllAcc()
{
	var verticalAccordions = $$('.il_VAccordionToggleDef');
	verticalAccordions.each(function(accordion) {
		$(accordion.next(0)).setStyle({
			height: '0px'
		});
	});
}

ilAddOnLoad(initAllAcc);
*/
