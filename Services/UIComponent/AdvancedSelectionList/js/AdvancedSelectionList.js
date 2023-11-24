
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

il.AdvancedSelectionList =
{
	lists: {},
	items: {},
	init: {},
	
	// add new selection list
	add: function (id, cfg)
	{
		this.lists[id] = cfg;
		this.items[id] = {};
		// this.showAnchor(cfg.anchor_id);

	    // hide all overlays on trigger
	   	$("#ilAdvSelListAnchorText_" + id).click(function(e) {			
			if (typeof il.Overlay != "undefined") {
				//il.Overlay.hideAllOverlays(e, true);
			}
		});
	},
			
	itemOn: function (obj)
	{
		obj.className = "il_adv_sel_act";
	},
	
	itemOff: function (obj)
	{
		obj.className = "il_adv_sel";
	},
	
	showAnchor: function(id)
	{
		$("#" + id).removeClass("ilNoDisplay");
	},
	
	submitForm: function (id, hid_name, hid_val, form_id, cmd)
	{
		this.setHiddenInput(id, hid_name, hid_val);
		form_el = document.getElementById(form_id);
		hidden_cmd_el = document.getElementById("ilAdvSelListHiddenCmd_" + id);
		hidden_cmd_el.name = 'cmd[' + cmd + ']';
		hidden_cmd_el.value = '1';
		form_el.submit();
		
		return false;
	},
	
	selectForm: function (id, hid_name, hid_val, title)
	{
		this.setHiddenInput(id, hid_name, hid_val);
		anchor_text = document.getElementById("ilAdvSelListAnchorText_" + id);
		anchor_text.innerHTML = title + ' <span class="caret"></span>';
		if (this.lists[id]['select_callback'] != null)
		{
			eval(this.lists[id]['select_callback'] + '(this.items[id][hid_val]);');
		}
		
		return false;
	},

	clickNop: function (id, hid_name, hid_val, title)
	{
		if (this.lists[id]['select_callback'] != null)
		{
			eval(this.lists[id]['select_callback'] + '(this.items[id][hid_val]);');
		}
	},
	
	setHiddenInput: function (id, hid_name, hid_val)
	{
		hidden_el = document.getElementById("ilAdvSelListHidden_" + id);
//console.trace();
		if (hidden_el) {
			hidden_el.name = hid_name;
			hidden_el.value = hid_val;
		}
	},

	getHiddenInput: function (id)
	{
		hidden_el = document.getElementById("ilAdvSelListHidden_" + id);
		return hidden_el.value;
	},

	addItem: function (id, hid_name, hid_val, title)
	{
		this.items[id][hid_val] = {hid_name: hid_name, hid_val: hid_val, title: title};
	},

	selectItem: function (id, value)
	{
		if (typeof this.items[id] != 'undefined' &&
			this.items[id][value] != null)
		{
			this.selectForm(id, this.items[id][value]["hid_name"], value, this.items[id][value]["title"]);
		}
	},
	
	openTarget: function (t, f)
	{
		if (f == '')
		{
			location = t;
		}
		else if (f == '_top')
		{
			parent.location = t;
		}
		else if (f == '_blank')
		{
			var w = window.open(t);
			w.focus();
		}
		else
		{
			if (typeof top.frames[f] != "undefined")
			{
				top.frames[f].location.href = t;
			}
			else
			{
				location = t;
			}
		}
		
		return false;
	}

};