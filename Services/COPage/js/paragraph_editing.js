function addInternalLink(Link,title) 
{
	// {{{	

	if(Link.indexOf("[/iln]")==-1 && Link.indexOf("/]")!=-1) 
	{
		editor.insertHTML(Link);
	} 
	else 
	{
	
		Link = Link.replace("[iln ","<a class=\"ilc_IntLink\" ");
		Link = Link.replace("\"]","\">");
		Link = Link.replace(" [/iln]","");
	
	
		if(editor.hasSelectedText()) 
		{
			st = editor.getSelectedHTML();
			st = st.replace("&nbsp;","");
			st = st.trim();
			if(st!="") title = editor.getSelectedHTML();
		}
		
		A = getClearTags();
		if (A[0]=="") {
			editor.insertHTML(Link+title+"</a>");
			S = editor.getHTML();
			
			editor.setHTML(S+"&nbsp;&nbsp;");
		} else {
			N = Link+title+"</a>";
			S = editor.getSelectedHTML();
			editor.insertHTML("#!#*#!#");
			H = editor.getHTML();
			H = H.replace("#!#*#!#",A[1]+N+A[0]);
			
			for(k=0;k<20;k++) {
				H = H.replace("<span class=\"ilc_Comment\"></span>","");
				H = H.replace("<p>","");
				H = H.replace("</p>","");
				H = H.replace("<strong></strong>","");
			}
			
			editor.setHTML(H+"&nbsp;");
		}
	}
	editor.updateToolbar();
	editor.focusEditor();
	// }}}
}
