
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

ilNestedList =
{
	lists: {},

	addList: function (id, cfg)
	{
		this.lists[id] = {
			cfg: cfg,
			nodes: {},
			childs: {},
			};
	},

	addNode: function (list_id, parent_id, node_id, content)
	{
		this.lists[list_id].nodes[node_id] = {
			parent_id: parent_id,
			content: content};
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
			
			console.log('draw:' + container_id);
			var childs_id = "list_" + list_id + "_" + parent_id + "_ul";
			var cont = $("#" + container_id);
			cont.append("<ul id='" + childs_id + "'></ul>");
			var childs_ul = $("#" + childs_id);
			for (k in this.lists[list_id].childs[parent_id])
			{
//console.log(parent_id);
//console.log(k);
//console.log(this.lists[list_id].nodes[k]);
				var li_id = "list_" + list_id + "_" + k + "_li";
				childs_ul.append("<li id='" + li_id + "'>" +
					this.lists[list_id].nodes[k].content
					+ "</li>");
				this.draw(list_id, k, li_id);
			}
		}
	}
};
