// remove non-javascript form with java script table
ilAddOnLoad(ilInitLChatMessagesMainMenu);

/** 
* Chat invitations navigation
*/
function ilInitLChatMessagesMainMenu() {
	obj = document.getElementById('ilChatMessagesMainMenuDiv');
	
	if (obj) obj.style.display = 'block';
	ilChatMessagesMainMenuOn();
}

function PositionLayerLeft(obj) {
	var curleft = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	} else if (obj.x) curleft += obj.x;
	return curleft;
}

function PositionLayerTop(obj) {
	var curtop = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	} else if (obj.y) curtop += obj.y;
	return curtop;
}

/**
* Show chat invitations table
*/
function ilChatMessagesMainMenuOn() {
	obj = document.getElementById('ilChatMessagesMainMenuTableContainer');
	obj.style.position = 'absolute';
	obj.style.top = 
		PositionLayerTop(document.getElementById('ilChatMessagesMainMenuDiv'))  + 'px';
	obj.style.left = 
		PositionLayerLeft(document.getElementById('ilChatMessagesMainMenuDiv')) + 'px';
	obj.style.display = '';
	obj.style.paddingTop =
		(document.getElementById('ilChatMessagesMainMenuDiv').offsetHeight) + 'px';
}

/**
* Hide chat invitations table
*/
function ilChatMessagesMainMenuOff() {
	obj = document.getElementById('ilChatMessagesMainMenuTableContainer');
	obj.style.display = 'none';
}

Browser = {
    IE:	!!(window.attachEvent && !window.opera),
	Opera:	!!window.opera,
	WebKit:	navigator.userAgent.indexOf('AppleWebKit/') > -1,
	Gecko:	navigator.userAgent.indexOf('Gecko') > -1 && navigator.userAgent.indexOf('KHTML') == -1,
	MobileSafari: !!navigator.userAgent.match(/Apple.*Mobile.*Safari/),
	Safari: (navigator.userAgent.indexOf('Gecko') > -1 && /Safari/.test(navigator.userAgent) && /KHTML/.test(navigator.userAgent)),
	Chrome: (navigator.userAgent.indexOf('Gecko') > -1 && /Chrome/.test(navigator.userAgent) && /KHTML/.test(navigator.userAgent))
}
	
ChatMessagesSound = {
	_enabled: true,
	enable: function(){
    	ChatMessagesSound._enabled = true;
  	},
	disable: function(){
  		ChatMessagesSound._enabled = false;
	},
	_container: 'embed',
	play: function(url){
		if(!ChatMessagesSound._enabled) return;
	   
	    if(Browser.IE)
	    {
	    	oBgsound = document.createElement('BGSOUND');
	    	oBgsound.id = 'sound';
	    	oBgsound.src = url;
	    	oBgsound.loop = 0;
	    	oBgsound.autostart = true;
	    	document.body.appendChild(oBgsound);
	    }
	    else
	    {
	    	switch(ChatMessagesSound._container)
	    	{
	    		case 'embed':
	    			oBgsound = document.createElement('EMBED');
			    	oBgsound.id = 'sound';
			    	oBgsound.src = url;
			    	oBgsound.loop = false;
			    	oBgsound.autostart = true;
			    	oBgsound.hidden = true;
			    	oBgsound.width = '0';
			    	oBgsound.height = '0'
			    	document.body.appendChild(oBgsound);
	    			break;
	    		case 'object':
	    			oBgsound = document.createElement('OBJECT');
			    	oBgsound.id = 'sound';
			    	oBgsound.data = url;
			    	oBgsound.type = 'audio/mpeg';
			    	oBgsound.width = '0';
			    	oBgsound.height = '0'
			    	document.body.appendChild(oBgsound);
	    			break;	
	    	}
	    }
	  }
}

if((Browser.Gecko && navigator.userAgent.indexOf('Win') > 0) ||
	Browser.Safari || 
	Browser.Chrome) {
	if(navigator.plugins)
	{
		qt_found = false;
		for(var i = 0; i < navigator.plugins.length; i++)
		{
			if(navigator.plugins[i].name.indexOf('QuickTime') != -1)
			{
				qt_found = true;				
			}
			
			if(qt_found == true) break;
		}
		if(qt_found == true)
		{
			ChatMessagesSound._container = 'object';
		}
	}
	else
	{
		ChatMessagesSound.play = function(){}
	}
}