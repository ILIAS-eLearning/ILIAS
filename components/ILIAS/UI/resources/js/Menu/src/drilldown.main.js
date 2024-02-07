var dd = function(model, mapping, persistence) {

	var
	model = model,
	mapping = mapping,
	persistence = persistence,

	init = function(id, back_signal) {
		$(document).on(back_signal, upLevel);
		var list = mapping.parse(id);
		mapping.parseLevel(list, model.actions.addLevel, engageLevel, filter);

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
  filter = function(e) {
    model.actions.filter(e);
    mapping.setFiltered(model.actions.getFiltered());
    e.target.focus();
  },
	upLevel = function() {
		model.actions.upLevel();
		apply();
	},
	apply = function() {
		let
    current = model.actions.getCurrent(),
    parent = model.actions.getParent(),
    level = 2;
    if (current.parent === null) {
      level = 0;
    } else if (current.parent === '0') {
      level = 1;
    }
		mapping.setEngaged(current.id);
		persistence.store(current.id);
		mapping.setHeader(current.headerDisplayElement, parent.headerDisplayElement);
		mapping.setHeaderBacknav(level);
    mapping.correctRightColumnPosition(current.id);
	},

	public_interface = {
		init: init,
		engage: engageLevel
    };
    return public_interface;
};

export default dd;
