var drilldown = function(model, mapping) {

	var 
	model = model,
	mapping = mapping,
	init = function(id, back_signal) {
		$(document).on(back_signal, upLevel);
		var list = mapping.parse(id);
		mapping.parseLevel(list, model.actions.addLevel, engageLevel);
		engageLevel(0);
	},
	engageLevel = function(id) {
		model.actions.engageLevel(id);
		apply();
	},
	upLevel = function() {
		model.actions.upLevel();
		apply();
	},

	apply = function() {
		var current = model.actions.getCurrent(),
			idx;
		for(idx in model.data) {
			mapping.unsetEngaged(model.data[idx].id);
		}
		mapping.setEngaged(current.id);
		mapping.setHeaderTitle(current.label);
		mapping.setHeaderBacknav(current.parent != null);
	},

	public_interface = {
		init: init
    };
    return public_interface;
};

export default drilldown;
