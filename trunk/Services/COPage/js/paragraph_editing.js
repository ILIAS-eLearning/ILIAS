function addInternalLink(Link,title) 
{
	// {{{	
	if(Link.indexOf("[/iln]")==-1 && Link.indexOf("/]")!=-1) 
	{
		insert_text(Link);
	} 
	else 
	{
		//var i = Link.indexOf(" [/iln]");
		bbfontstyle(Link, "[/iln]");
	}
	// }}}
}
