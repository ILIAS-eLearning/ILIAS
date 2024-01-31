var drilldown = function(model, mapping, persistence, dd) {
	var 
	model = model,
	mapping = mapping,
	persistence = persistence,
	dd = dd,
	instances = {},

	init = function(id, back_signal, persistence_id) {
		instances[id] = new dd(model(), mapping(), persistence(persistence_id));
		instances[id].init(id, back_signal);
	},
	
	public_interface = {
		init: init,
		instances: instances
    };
    return public_interface;
};

export default drilldown;