
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

il.Explorer2 = {
	
	configs: {},
	
	init: function (config, js_tree_config) {
console.log(js_tree_config);
		if (config.ajax) {
			js_tree_config.html_data.ajax = {url: config.url + "&exp_cmd=getNodeAsync",
				data: function(n) {
					//console.log(this); // exp_cont missing
					return {node_id: n.attr ? n.attr("id") : "",
						exp_cont: config.container_id
					};
			}};
			
			js_tree_config.html_data.data = $("#" + config.container_id).html();
		}
		il.Explorer2.configs[config.container_id] = config;
		$("#" + config.container_id).bind("loaded.jstree", function (event, data) {
				$("#" + config.container_outer_id).removeClass("ilNoDisplay");
			}).bind("open_node.jstree close_node.jstree", function (event, data) {
				il.Explorer2.toggle(event, data);
			}).jstree(js_tree_config);
	},
	
	toggle: function(event, data) {
		var type = event.type, // "open_node" or "close_node"
			id = data.rslt.obj[0].id, // id of li element
			container_id = event.target.id,
			t = il.Explorer2, url;
			
		// the args[2] parameter is true for the initially
		// opened nodes, but not, if manually opened
		// this is somhow undocumented, but it works
		if (type == "open_node" && data.args[2]) {
			return;
		}
		
		//console.log(event.target.id);
		//console.log(type + ": " + id);
		//console.log(t.configs[container_id].url);
		url = t.configs[container_id].url;
		if (type == "open_node") {
			url = url + "&exp_cmd=openNode";
		} else {
			url = url + "&exp_cmd=closeNode";
		}
		url = url + "&exp_cont=" + container_id + "&node_id=" + id;
		
		il.Util.sendAjaxGetRequestToUrl(url, {}, {}, null);
	}
}
