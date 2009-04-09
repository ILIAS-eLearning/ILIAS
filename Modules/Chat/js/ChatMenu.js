function ilChatMenu()
{
	var me = this;
	var oMenu = new YAHOO.widget.Menu("chatmenu", {position: 'dynamic'});
	var oMenuConfigurations = [];
	
	var currentMenuId;
	var currentAdditionalInformation;
	
	oMenu.render(document.getElementsByTagName("body")[0]);

	oMenu.subscribe('show', function () {
		// position menu at coursor position
		this.setPosition();

		// if additional content is available
		// additional content may be displayable content which
		// will appear on the right side of the menu
		if (currentMenuId && oMenuConfigurations[currentMenuId].additional)
		{
			var obj = document.getElementById('chatmenu');
			var parent = obj.parentNode;
			obj.parentNode.appendChild(oMenuConfigurations[currentMenuId].additional);
			oMenuConfigurations[currentMenuId].additional.style.position = 'absolute';
			oMenuConfigurations[currentMenuId].additional.style.left = obj.offsetLeft + obj.offsetWidth + 'px';
			oMenuConfigurations[currentMenuId].additional.style.top = obj.offsetTop + 'px';
			
			currentAdditionalInformation = oMenuConfigurations[currentMenuId].additional;
		}
	}, this, true);
	
	oMenu.subscribe('hide', function () {
		if (currentAdditionalInformation && currentAdditionalInformation.parentNode)
		{
			var parent = currentAdditionalInformation.parentNode;
			parent.removeChild(currentAdditionalInformation);	
		}
		currentAdditionalInformation = false;
		currentMenuId = false;
	}, this, true);
	
	this.show = function (id)
	{
		oMenu.hide();
		oMenu.clearContent();
		if (oMenuConfigurations[id].items.length <= 0)
		{
			this.hide();
			return;
		}
		
		// save current menu id for internal use
		currentMenuId = id;
		
		oMenu.addItems(oMenuConfigurations[id].items);
		
		for(var i in oMenuConfigurations[id].titles)
		{
			oMenu.setItemGroupTitle(oMenuConfigurations[id].titles[i], i);	
		}
		
		oMenu.render(document.getElementsByTagName("body")[0]);
		oMenu.show();
	};
	
	this.setPosition = function()
	{
		var scrOfX = 0, scrOfY = 0;
	  	if( typeof( window.pageYOffset ) == 'number' ) {
		  	//NS
		  	scrOfY = window.pageYOffset;
	    	scrOfX = window.pageXOffset;
	  	}
	  	else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		  	//DOM
		  	scrOfY = document.body.scrollTop;
	    	scrOfX = document.body.scrollLeft;
	  	}
	  	else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		  	//IE6
		  	scrOfY = document.documentElement.scrollTop;
	    	scrOfX = document.documentElement.scrollLeft;
	  	}
		
		//obj.style.display = "block";
		var x = (mouse_x - 3 + scrOfX );
		var y = (mouse_y - 3 + scrOfY);
		
		oMenu.cfg.setProperty('xy', [x,y]);
	}
	
	this.hide = function () { 
		oMenu.hide();
	};
	
	this.addMenuConfiguration = function(config)
	{
		oMenuConfigurations[config.id] = config;
	}
};

var chatmenu = new ilChatMenu();
