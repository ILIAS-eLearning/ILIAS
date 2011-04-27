
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

ilCOPagePres =
{
	init: function ()
	{
		this.initToc();
		this.initInteractiveImages();
	},
	
	//
	// Toc (as used in Wikis)
	//
	
	initToc: function ()
	{
		// init toc
		var cookiePos = document.cookie.indexOf("pg_hidetoc=");
		if (cookiePos > -1 && document.cookie.charAt(cookiePos + 11) == 1)
		{
			this.toggleToc();
		}
	},

	// toggle table of contents
	toggleToc: function()
	{
		var toc = document.getElementById('ilPageTocContent');

		if (!toc)
		{
			return;
		}
		var toc_on = document.getElementById('ilPageTocOn');
		var toc_off = document.getElementById('ilPageTocOff');
		if (toc && toc.style.display == 'none')
		{
			toc.style.display = 'block';
			toc_on.style.display = 'none';
			toc_off.style.display = '';
			document.cookie = "pg_hidetoc=0";
		}
		else
		{
			toc_on.style.display = '';
			toc_off.style.display = 'none';
			toc.style.display = 'none';
			document.cookie = "pg_hidetoc=1";
		}
	},
	
	//
	// Interactive Images
	//

	iim_trigger: {},
	iim_area: {},

	// 
	initInteractiveImages: function ()
	{
		// preload overlay images (necessary?)
		
		// add onmouseover event to all map areas
		$("map.iim > area").mouseenter(this.enterIIMArea);
		$("map.iim > area").mouseleave(this.leaveIIMArea);
	},
	
	enterIIMArea: function (e)
	{
		//$("img#zzz").css('display', '');
	},
	
	leaveIIMArea: function (e)
	{
		//$("img#zzz").css('display', 'none');
	},
	
	addIIMTrigger: function(tr)
	{
console.log('add trigger');
console.log(tr);
		this.iim_trigger[tr.tr_id] = tr;
	},
	
	addIIMArea: function(a)
	{
console.log('add area');
console.log(a);
		this.iim_area[a.area_id] = a;
	}
}
ilAddOnLoad(function() {ilCOPagePres.init();});
