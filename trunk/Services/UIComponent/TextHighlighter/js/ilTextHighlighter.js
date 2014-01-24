
/* Copyright (c) 1998-2012 ILIAS open source,

Code is based on:

highlight v3
Highlights arbitrary terms.
<http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html>

MIT license.
Johann Burkard
<http://johannburkard.de>
<mailto:jb@eaio.com>

*/

il.TextHighlighter =
{
	highlight: function (id, pat)
	{
		pat = pat.toUpperCase();
		var n = document.getElementById(id);
		if (typeof n != "undefined")
		{
			this.performHighlighting(n, pat);
		}
	},
	
	performHighlighting: function (node, pat)
	{
		var skip = 0;
		if (node.nodeType == 3)
		{
			var pos = node.data.toUpperCase().indexOf(pat);
			if (pos >= 0)
			{
				var spannode = document.createElement('span');
				spannode.className = 'ilHighlighted';
				var middlebit = node.splitText(pos);
				var endbit = middlebit.splitText(pat.length);
				var middleclone = middlebit.cloneNode(true);
				spannode.appendChild(middleclone);
				middlebit.parentNode.replaceChild(spannode, middlebit);
				skip = 1;
			}
		}
		else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName))
		{
			for (var i = 0; i < node.childNodes.length; ++i)
			{
				i += this.performHighlighting(node.childNodes[i], pat);
			}
		}
		return skip;
	}
}