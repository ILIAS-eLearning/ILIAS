var dd = function(model, mapping, persistence) {

	var 
	model = model,
	mapping = mapping,
	persistence = persistence,

	init = function(id, back_signal) {
		$(document).on(back_signal, upLevel);
		var list = mapping.parse(id);
		mapping.parseLevel(list, model.actions.addLevel, engageLevel);

		var level = persistence.read();
		if(!level) {
			level = 0;
		}

		engageLevel(level);
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
		var current = model.actions.getCurrent();
		mapping.setEngaged(current.id);
		persistence.store(current.id);
		mapping.setHeaderTitle(current.label);
		mapping.setHeaderBacknav(current.parent != null);
	},

	public_interface = {
		init: init,
		engage: engageLevel
    };
    return public_interface;
};

export default dd;
