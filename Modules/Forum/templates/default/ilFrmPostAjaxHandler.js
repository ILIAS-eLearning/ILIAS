function ilFrmQuoteAjaxHandler(t, ed)
{	
	var ilFrmQuoteCallback =
	{
		success: function(o) {
			if(typeof o.responseText != "undefined")
			{
				var marker = tinyMCE.activeEditor.selection.getBookmark();
				tinyMCE.execCommand("mceInsertContent", false, t._ilfrmquote2html(ed, o.responseText));
			}
		},
		failure:  function(o) {
			//alert('ilFrmQuoteFailureHandler');
		}
	};

	var request = YAHOO.util.Connect.asyncRequest('GET', '{IL_FRM_QUOTE_CALLBACK_SRC}', ilFrmQuoteCallback);
	
	return false;
}