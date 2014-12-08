tinyMCEPopup.requireLangPack();

var LatexDialog = {
	init : function() {
		var f = document.forms[0];
		// Get the selected contents as text and place it in the input
		var value = "";
		var elm = tinyMCEPopup.editor.selection.getNode();
		if (elm != null)
		{
			var id = ("getAttribute" in elm) ? elm.getAttribute("class") : '';
			if (id == "latex")
			{
				var text = "";
				for (i = 0; i < elm.childNodes.length; i++)
				{
					text = text + elm.childNodes[i].data;
				}
                if(text != 'undefined')
                {
                    value = text;
                }	
			}
			else
			{
				value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
			}
		}
		else
		{
			value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		}
		f.latex_code.value = value;
		onLatexCodeChanged.call($("#latex_code"));
	},

	insert : function() {
		// Insert the contents from the input into the document
		var elm = tinyMCEPopup.editor.selection.getNode();
		tinyMCEPopup.execCommand("mceBeginUndoLevel");
		var latex_code = document.forms[0].latex_code.value;
		if (latex_code.length > 0)
		{
			if (elm == null) 
			{
				tinyMCEPopup.editor.execCommand("mceInsertContent", false, '<span class="latex">' + latex_code + '</span>');
			} 
			else
			{
				var id = elm.getAttribute("class");
				if (id == "latex")
				{
					elm.innerHTML = "";
					tinyMCEPopup.editor.execCommand("mceRemoveNode", false, elm);
					tinyMCEPopup.editor.execCommand("mceInsertContent", false, '<span class="latex">' + latex_code + '</span>');
				}
				else
				{
					tinyMCEPopup.editor.execCommand("mceInsertContent", false, '<span class="latex">' + latex_code + '</span>');
				}
			}
		}
		tinyMCEPopup.execCommand("mceEndUndoLevel");
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(LatexDialog.init, LatexDialog);
