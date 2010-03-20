var ilAdvancedSelectionListFunc = function() {
};
ilAdvancedSelectionListFunc.prototype =
{
	lists: [],
	toggle: [],
	widthFixed: {},
	openedMenu: '',
	oldOpenedMenu: '',
	ctoggle_el: '',				// currently additionally toggled element
	ctoggle_el_class: null,		// ... its origianl style class
	menuBlocked: false,
	closeCnt: -1,
	waitMouseOut: 10,
	waitAfterClicked: 20,

	
	// add new selection list
	add: function (id)
	{
		this.lists.push(id);
	},
	
	// init all lists
	init: function ()
	{
		for (var i = 0; i < this.lists.length; ++i)
		{
			id = this.lists[i];
	
			// hide non-js section
			obj = document.getElementById('ilAdvSelListNoJS_' + id);
			if (obj)
				obj.style.display='none';
			
			// show js section
			obj = document.getElementById('ilAdvSelListJS_' + id);
			if (obj)
				obj.style.display='block';
		
			// show placeholder
			obj = document.getElementById('ilAdvSelListPH_' + id);
			if (obj)
				obj.style.display='block';
		}
	},

	nextMenuClick: function ()
	{
		this.menuBlocked = false;
	},
	
	// toggle a list
	toggle: function (id, opt)
	{
		if (this.openedMenu == id)
		{
			this.hide(id);
		}
		else
		{
			this.closeCnt = this.waitAfterClicked;
			this.listOn(id, true, opt);
		}
	},

	// show selection list
	listOn: function (id, force, opt)
	{
//console.log("listOn");
		this.closeCnt = -1;
	
		if (this.openedMenu == id && !force)
		{
			return;
		}
		if (this.menuBlocked)
		{
			return;
		}
		this.menuBlocked = true;
		setTimeout("ilAdvancedSelectionList.nextMenuClick()",100);
		
		var nextMenu = id;
		
		if (this.openedMenu != "" || this.openedMenu == nextMenu) 
		{
			this.hide(this.openedMenu);
			this.oldOpenedMenu = this.openedMenu;
			this.openedMenu = "";
		}
		else
		{
			this.oldOpenedMenu = "";
		}
		
		if (this.openedMenu == "" && nextMenu != this.oldOpenedMenu)
		{
			this.openedMenu = nextMenu;
			this.show(this.openedMenu, opt);
		}
		this.closeCnt = this.waitAfterClicked;
		this.doCloseAdvContextMenu();
	},

	doCloseAdvContextMenu: function () 
	{
//console.log(this.closeCnt);
		if (this.closeCnt > -1) 
		{
			this.closeCnt--;
			if (this.closeCnt == 0) 
			{
				if(this.openedMenu!="") 
				{
					this.hide(this.openedMenu);
					this.openedMenu = "";
					this.oldOpenedMenu = "";
				}
				this.closeCnt=-1;
			}
		}
		if (this.closeCnt > -1)
		{
			setTimeout("ilAdvancedSelectionList.doCloseAdvContextMenu()", 200);
		}
	},

	getOption: function (opt, name)
	{
		if (opt == null || typeof(opt) == 'undefined')
		{
			return null;
		}
		if (typeof(opt[name]) == 'undefined')
		{
			return null;
		}
		return opt[name];
	},

	show: function (id, opt)
	{
		var toggle_el = this.getOption(opt, 'toggle_el');
		var toggle_class_on = this.getOption(opt, 'toggle_class_on');
		if (toggle_el != null && toggle_class_on != null)
		{
			toggle_obj = document.getElementById(toggle_el);
			if (toggle_obj)
			{
				this.toggle[toggle_el] = toggle_obj.className;
				toggle_obj.className = toggle_class_on;
				this.ctoggle_el = toggle_el;
			}
		}
		
		this.fixPosition(id);
		
		// get content asynchronously
		if (this.getOption(opt, 'asynch'))
		{
			this.loadAsynch(id, this.getOption(opt, 'asynch_url'));
		}
	},
	
	// fix position
	fixPosition: function(id)
	{	
		var el = document.getElementById('ilAdvSelListTable_' + id);
//console.log("fixPosition");
		el.style.display='';
		el.style.overflow = '';
		
		var cl_reg = YAHOO.util.Dom.getClientRegion();
		var anchor = document.getElementById('ilAdvSelListAnchorElement_' + id);
		var anchor_reg = YAHOO.util.Region.getRegion(anchor);
	
		YAHOO.util.Dom.setX(el, anchor_reg.x);
		YAHOO.util.Dom.setY(el, anchor_reg.y + anchor_reg.height);
		var el_reg = YAHOO.util.Region.getRegion(el);
		
		// make it smaller, if window height is not sufficient
		if (cl_reg.height < el_reg.height + 20)
		{
			var newHeight = cl_reg.height - 20;
			if (newHeight < 150)
			{
				newHeight = 150;
			}
			el.style.height = newHeight + "px";
			
			if (!this.widthFixed[id])
			{
				el.style.width = el_reg.width + 20 + "px";
				this.widthFixed[id] = true;
			}
			el_reg = YAHOO.util.Region.getRegion(el);
		}
		
		// if too low: show it higher
		if (cl_reg.bottom < el_reg.bottom)
		{
			YAHOO.util.Dom.setY(el, el_reg.y - (el_reg.bottom - cl_reg.bottom));
			el_reg = YAHOO.util.Region.getRegion(el);
		}
	
		// if too far on the right: show it more left
		if (cl_reg.right < el_reg.right)
		{
			YAHOO.util.Dom.setX(el, el_reg.x - (el_reg.right - cl_reg.right));
		}
	
		el.style.overflow = 'auto';
		
	},
	
	loadAsynch: function (list_id, sUrl)
	{
		var cb =
		{
			success: this.asynchSuccess,
			failure: this.asynchFailure,
			argument: { list_id: list_id}
		};
	
		var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, cb);
		
		return false;
	},
	
	// handle asynchronous request (success)
	asynchSuccess: function(o)
	{
		// parse headers function
		function parseHeaders()
		{
			var allHeaders = headerStr.split("\n");
			var headers;
			for(var i=0; i < headers.length; i++)
			{
				var delimitPos = header[i].indexOf(':');
				if(delimitPos != -1)
				{
					headers[i] = "<p>" +
					headers[i].substring(0,delimitPos) + ":"+
					headers[i].substring(delimitPos+1) + "</p>";
				}
			return headers;
			}
		}
	
		// perform modification
		if(typeof o.responseText != "undefined")
		{
			// this a little bit complex procedure fixes innerHTML with forms in IE
			var newdiv = document.createElement("div");
			newdiv.innerHTML = o.responseText;
			var list_div = document.getElementById("ilAdvSelListTable_" + o.argument.list_id);
			if (!list_div)
			{
				return;
			}
			list_div.innerHTML = '';
			list_div.appendChild(newdiv);
			
			// for safari: eval all javascript nodes
			if (YAHOO.env.ua.webkit != "0" && YAHOO.env.ua.webkit != "1")
			{
				//alert("webkit!");
				var els = YAHOO.util.Dom.getElementsBy(function(el){return true;}, "script", newdiv);
				for(var i= 0; i<=els.length; i++)
				{
					eval(els[i].innerHTML);
				}
			}
			ilAdvancedSelectionList.fixPosition(o.argument.list_id);			
		}
	},
	
	// Success Handler
	asynchFailure: function(o)
	{
		//alert('FailureHandler');
	},
	
	listOff: function(id)
	{
//console.log("listOff");
		this.closeCnt = this.waitMouseOut;
		this.doCloseAdvContextMenu();

	},
	
	// hide list
	hide: function (id)
	{
		toggle_el = this.ctoggle_el;
	
		if (toggle_el != null && toggle_el != '')
		{
			this.ctoggle_el_class = this.toggle[toggle_el];
			toggle_obj = document.getElementById(toggle_el);
			if (toggle_obj && this.ctoggle_el_class)
			{
				toggle_obj.className = this.ctoggle_el_class;
			}
		}

		obj = document.getElementById('ilAdvSelListTable_' + id);
		if (typeof obj != "undefined" && obj)
		{
			obj.style.display='none';
			this.openedMenu = "";
		}
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
		anchor = document.getElementById(id);
		anchor.style.display='';
	},
	
	submitForm: function (id, hid_name, hid_val, form_id, cmd)
	{
		this.setHiddenInput(id, hid_name, hid_val);
		form_el = document.getElementById(form_id);
		hidden_cmd_el = document.getElementById("ilAdvSelListHiddenCmd_" + id);
		hidden_cmd_el.name = 'cmd[' + cmd + ']';
		hidden_cmd_el.value = '1';
		form_el.submit();
	},
	
	selectForm: function (id, hid_name, hid_val, title)
	{
		this.setHiddenInput(id, hid_name, hid_val);
		anchor_text = document.getElementById("ilAdvSelListAnchorText_" + id);
		anchor_text.innerHTML = title;
		this.hide(id);
	},
	
	setHiddenInput: function (id, hid_name, hid_val)
	{
		hidden_el = document.getElementById("ilAdvSelListHidden_" + id);
		hidden_el.name = hid_name;
		hidden_el.value = hid_val;
	}

};
var ilAdvancedSelectionList = new ilAdvancedSelectionListFunc();
ilAddOnLoad(function(){ilAdvancedSelectionList.init()});
//setTimeout("ilAdvancedSelectionList.doCloseAdvContextMenu()", 200);
