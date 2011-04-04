
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

var ilCOPagePresF = function() {
};
ilCOPagePresF.prototype =
{
	init: function ()
	{
		// init toc
		var cookiePos = document.cookie.indexOf("pg_hidetoc=");
		if (cookiePos > -1 && document.cookie.charAt(cookiePos + 11) == 1)
		{
			ilCOPagePres.toggleToc();
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
	}
}
var ilCOPagePres = new ilCOPagePresF();
ilAddOnLoad(function() {ilCOPagePres.init();});

