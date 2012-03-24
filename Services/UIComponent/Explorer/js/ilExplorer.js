
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

il.Explorer = {
	
	refresh: function(id, url) {
		il.Util.ajaxReplaceInner(url, id);
		return false;
	}
}
