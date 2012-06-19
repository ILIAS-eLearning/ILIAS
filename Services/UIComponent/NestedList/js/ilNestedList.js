
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */
if (typeof il == 'undefined') var il = {};
il.NestedList =
{
	lists: {},

	addList: function (id, cfg)
	{
		this.lists[id] = {
			cfg: cfg,
			nodes: {},
			childs: {}
			};
	},

	addNode: function (list_id, parent_id, node_id, content, expanded)
	{
		this.lists[list_id].nodes[node_id] = {
			parent_id: parent_id,
			content: content,
			expanded: expanded
		};
//console.log("adding: " + parent_id + ", " + node_id );
		if (typeof this.lists[list_id].childs[parent_id] == 'undefined')
		{
			this.lists[list_id].childs[parent_id] = {};
		}
		this.lists[list_id].childs[parent_id][node_id] = node_id;
	},

	draw: function (list_id, parent_id, container_id)
	{
		var k;
//console.log(this.lists[list_id]);
		
		if (typeof this.lists[list_id].childs[parent_id] != 'undefined')
		{
			var c = this.lists[list_id].childs[parent_id];
			
			var ul_class_str = "";
			if (typeof this.lists[list_id].cfg['ul_class'] != "undefined" &&
				this.lists[list_id].cfg['ul_class'] != "")
			{
				ul_class_str = " class='" + this.lists[list_id].cfg['ul_class'] + "' "
			}

			var li_class_str = "";
			if (typeof this.lists[list_id].cfg['li_class'] != "undefined" &&
				this.lists[list_id].cfg['li_class'] != "")
			{
				li_class_str = " class='" + this.lists[list_id].cfg['li_class'] + "' "
			}

			// is this list hidden, since parent is not expanded?
			var ul_style_str = "";
			if (typeof this.lists[list_id].cfg['exp_class'] != "undefined" &&
				typeof this.lists[list_id].nodes[parent_id] != "undefined" &&
				!this.lists[list_id].nodes[parent_id].expanded)
			{
				ul_style_str = " style='display:none;' ";
			}
			
			var childs_id = "list_" + list_id + "_" + parent_id + "_ul";
			var cont = $("#" + container_id);
			cont.append("<ul id='" + childs_id + "' " + ul_class_str + ul_style_str + "></ul>");
			var childs_ul = $("#" + childs_id);
			for (k in this.lists[list_id].childs[parent_id])
			{
//console.log(parent_id);
//console.log(k);
//console.log(this.lists[list_id].nodes[k]);
				
				// check if childrens for the node exist
				var expand_link = "";
				if (typeof this.lists[list_id].childs[k] != 'undefined' &&
					typeof this.lists[list_id].cfg['exp_class'] != "undefined")
				{
					var eclass;
					if (this.lists[list_id].nodes[k].expanded)
					{
						eclass = this.lists[list_id].cfg['exp_class'];
					}
					else
					{
						eclass = this.lists[list_id].cfg['col_class'];
					}
					expand_link = "<a onclick='il.NestedList.toggle(this," +
						'"' + list_id + '","' + k + '"' +
						"); return false;' href='#' class='" + eclass + "'> </a>";
				}

				// output node
				var li_id = "list_" + list_id + "_" + k + "_li";
				childs_ul.append("<li id='" + li_id + "' " + li_class_str + "><div>" +
					expand_link +
					this.lists[list_id].nodes[k].content
					+ "</div></li>");
				
				// draw children
				this.draw(list_id, k, li_id);
			}
		}
	},
	
	toggle: function (toggle_link, list_id, node_id)
	{
		if (this.lists[list_id].nodes[node_id].expanded)
		{
			$('#list_' + list_id + '_' + node_id + '_ul').css("display", "none");
			$(toggle_link).attr("class", this.lists[list_id].cfg['col_class']);
			this.lists[list_id].nodes[node_id].expanded = false;
		}
		else
		{
			$('#list_' + list_id + '_' + node_id + '_ul').css("display", "");
			$(toggle_link).attr("class", this.lists[list_id].cfg['exp_class']);
			this.lists[list_id].nodes[node_id].expanded = true;
		}
	},
	
	expandAll: function(list_id)
	{
		for (k in this.lists[list_id].nodes)
		{
			$('#list_' + list_id + '_' + k + '_ul').css("display", "");
			$('#list_' + list_id + '_' + k + '_li a.' + this.lists[list_id].cfg['col_class']).
				attr("class", this.lists[list_id].cfg['exp_class']);
			this.lists[list_id].nodes[k].expanded = true;
		}
	},
	
	collapseAll:  function(list_id)
	{
		for (k in this.lists[list_id].nodes)
		{
			$('#list_' + list_id + '_' + k + '_ul').css("display", "none");
			$('#list_' + list_id + '_' + k + '_li a.' + this.lists[list_id].cfg['exp_class']).
				attr("class", this.lists[list_id].cfg['col_class']);
			this.lists[list_id].nodes[k].expanded = false;
		}
	}
};
