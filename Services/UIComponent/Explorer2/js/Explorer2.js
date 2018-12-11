
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

il.Explorer2 = {

	current_search_term: '',

	selects: {},
	
	configs: {},
	
	init: function (config, js_tree_config) {
		if (config.ajax) {
			js_tree_config.core.data = {url: config.url + "&exp_cmd=getNodeAsync",
				data: function(n) {
					var id = n.id;
					if (n.id === "#") {
						id = "";
					}
					return {node_id: id,
						exp_cont: config.container_id,
						searchterm: il.Explorer2.current_search_term
					};
			}};
		}
		config.js_tree_config = js_tree_config;
		console.log(js_tree_config);
		il.Explorer2.configs[config.container_id] = config;
		$("#" + config.container_id).on("loaded.jstree", function (event, data) {
				var i;
				$("#" + config.container_outer_id).removeClass("ilNoDisplay");
				for (i = 0; i < config.second_hnodes.length; i++) {
					$("#" + config.second_hnodes[i]).addClass("ilExplSecHighlight");
				}
				console.log("loaded jstree");

		}).on("open_node.jstree close_node.jstree", function (event, data) {
				il.Explorer2.toggle(event, data);
			}).jstree(js_tree_config).bind("select_node.jstree", function (e, data) {
			// not working, if node is disabled by data attribute us on click above
			//var href = data.node.a_attr.href;
			//document.location.href = href;
		}).on('ready.jstree', function (e, data) {

			il.Explorer2.setEvents("#" + config.container_id, config.container_id);

		}).on('after_open.jstree', function (e, data) {
			var cid = data.node.id, p;
			if (cid !== "#") {
				p = "#" + cid;
				setTimeout(function() {
					il.Explorer2.setEvents(p, config.container_id);
				}, 500);
			}
		});
	},

	setEvents: function(p, cid) {
		$(p).find("a").on("click", function (e) {
			var href = $(this).attr("href");
			var target = $(this).attr("target");
			if (href != "#" && href != "") {
				if (target == "_blank") {
					window.open(href, '_blank');
				} else {
					document.location.href = href;
				}
			}
		});
		$(p + " .ilExpSearchInput").parent("a").replaceWith(function() { return $('input:first', this); });

		$(p + " .ilExpSearchInput").on("keydown", function(e) {
			if(e.keyCode === 13) {
				e.stopPropagation();
				e.preventDefault();
				var pid = $(e.target).parents("li").parents("li").attr("id");
				il.Explorer2.current_search_term = $(e.target).val();
				$("#" + cid).jstree('refresh_node', pid);
			}
		});

	},

	toggle: function(event, data) {

		var type = event.type, // "open_node" or "close_node"
			id = data.node.id, // id of li element
			container_id = event.target.id,
			t = il.Explorer2, url;
			
		// the args[2] parameter is true for the initially
		// opened nodes, but not, if manually opened
		// this is somhow undocumented, but it works
		if (type == "open_node" &&
			typeof t.configs[container_id].js_tree_config.core.initially_open[id] !== 'undefined') {
			return;
		}
		
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
		$("#" + id + "_select").on("click", function (ev) {
			il.UICore.unloadWrapperFromRightPanel();
			il.UICore.showRightPanel();
			il.UICore.loadWrapperToRightPanel(id + "_expl_wrapper");
			return false;
		});
		$("#" + id + "_reset").on("click", function (ev) {
			$("#" + id + "_hid").empty();
			$("#" + id + "_cont_txt").empty();
			$('#' + id + '_expl_content input[type="checkbox"]').each(function() {
				this.checked = false;
			});

			return false;
		});
		$("#" + id + "_expl_content a.ilExplSelectInputButS").on("click", function (ev) {
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
		$("#" + id + "_expl_content a.ilExplSelectInputButC").on("click", function (ev) {
			il.UICore.hideRightPanel();
			return false;
		});		
	},
	
	selectOnClick: function (e, node_id) {
		var el;
		$('#' + node_id + ' input[type="checkbox"]:first').each(function() {
			el = this;
			setTimeout(function() {
				el.checked = !el.checked;
			}, 10);
		});
		$('#' + node_id + ' input[type="radio"]:first').each(function() {
			el = this;
			setTimeout(function() {
				el.checked = true;
			}, 10);
		});

		return false;
	}
}
