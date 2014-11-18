
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

il.Explorer2 = {
	
	selects: {},
	
	configs: {},
	
	init: function (config, js_tree_config) {
//console.log(js_tree_config);
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
				var i;
				$("#" + config.container_outer_id).removeClass("ilNoDisplay");
				for (i = 0; i < config.second_hnodes.length; i++) {
					$("#" + config.second_hnodes[i]).addClass("ilExplSecHighlight");
				}
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
		if (url == '') {
			return;
		}
		if (type == "open_node") {
			url = url + "&exp_cmd=openNode";
		} else {
			url = url + "&exp_cmd=closeNode";
		}
		url = url + "&exp_cont=" + container_id + "&node_id=" + id;
		
		il.Util.sendAjaxGetRequestToUrl(url, {}, {}, null);
	},
	
	//
	// ExplorerSelectInputGUI related functions
	//
	
	// init select input
	initSelect: function(id) {
		$("#" + id + "_select").bind("click", function (ev) {
			il.UICore.showRightPanel();
			il.UICore.loadWrapperToRightPanel(id + "_expl_wrapper");
			return false;
		});
		$("#" + id + "_reset").bind("click", function (ev) {
			$("#" + id + "_hid").empty();
			$("#" + id + "_cont_txt").empty();
			$('#' + id + '_expl_content input[type="checkbox"]').each(function() {
				this.checked = false;
			});

			return false;
		});
		$("#" + id + "_expl_content a.ilExplSelectInputButS").bind("click", function (ev) {
			var t = sep = "";
			// create hidden inputs with values
			$("#" + id + "_hid").empty();
			$("#" + id + "_cont_txt").empty();
			$('#' + id + '_expl_content input[type="checkbox"]').each(function() {
				var n = this.name.substr(0, this.name.length - 6) + "[]",
					ni = "<input type='hidden' name='" + n + "' value='" + this.value + "' />";
				if (this.checked) {
					t = t + sep + $(this).parent().find("span.ilExp2NodeContent").html();
					sep = ", ";
					$("#" + id + "_hid").append(ni);
				}
			});
			$('#' + id + '_expl_content input[type="radio"]').each(function() {
				var n = this.name.substr(0, this.name.length - 4),
					ni = "<input type='hidden' name='" + n + "' value='" + this.value + "' />";
				if (this.checked) {
					t = t + sep + $(this).parent().find("span.ilExp2NodeContent").html();
					sep = ", ";
					$("#" + id + "_hid").append(ni);
				}
			});
			$("#" + id + "_cont_txt").html(t);
			il.UICore.hideRightPanel();
			
			return false;
		});		
		$("#" + id + "_expl_content a.ilExplSelectInputButC").bind("click", function (ev) {
			il.UICore.hideRightPanel();
			return false;
		});		
	},
	
	selectOnClick: function (node_id) {
		$('#' + node_id + ' input[type="checkbox"]:first').each(function() {
			this.checked = !this.checked;
		});
		$('#' + node_id + ' input[type="radio"]:first').each(function() {
			this.checked = true;
		});
		return false;
	}
}
