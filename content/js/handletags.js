function replaceAll(heystack, needle, newneedle) 
{
	var i=0;
	while (heystack.indexOf(needle)!=-1) 
	{
		i++;if(i>100) break;
		heystack = heystack.replace(needle, newneedle);
	}
	return(heystack);
}


function addTagInEditor(tagName,className) 
{
// {{{
	
	if (editor.hasSelectedText() && editor.getSelectedHTML()!="&nbsp;"  && editor.getSelectedHTML()!=" ") 
	{
		editor.surroundHTML(startMarker,endMarker);
		text = editor.getHTML();
		
		text = replaceAll(text, "<br />","%%BR%%");
		text = replaceAll(text, "<br/>","%%BR%%");
		text = replaceAll(text, "<br>","%%BR%%");
		
		text = replaceAll(text,"&nbsp;"," ");
		text2 = addTag(text, tagName, className);
		
		// Zur Sicherheit alle eventuell übriggebliebenen Hilfszeichen entfernen.
		for(i=1;i<=10;i++) 
		{
			for(j=1;j<=10;j++) 
			{
				text2 = replaceAll(text2,"%%"+i+"x"+j+"x%%","");
				text2 = replaceAll(text2,"+%"+i+"x"+j+"x%+","");
			}
		}
		
		
		
		text2 = replaceAll(text2, "<"+tagName+" class=\""+className+"\"></"+tagName+">", "");
		text2 = replaceAll(text2, "<span class=\"ilc_Strong\"></span>", "");
		text2 = replaceAll(text2, "<span class=\"ilc_Emph\"></span>", "");
		text2 = replaceAll(text2, "<span class=\"ilc_Quotation\"></span>", "");
		text2 = replaceAll(text2, "<span class=\"ilc_Comment\"></span>", "");
		text2 = replaceAll(text2, "<code class=\"ilc_Code\"></code>", "");
		
		text2 = replaceAll(text2, "%%BR%%", "<br />");
		
		editor.setHTML(text2);
		
		text2 = editor.getHTML();

		text2 = replaceAll(text2, "<span class=\"ilc_Strong\">&nbsp;</span>", " ");
		text2 = replaceAll(text2, "<span class=\"ilc_Emph\">&nbsp;</span>", " ");
		text2 = replaceAll(text2, "<span class=\"ilc_Quotation\">&nbsp;</span>", " ");
		text2 = replaceAll(text2, "<span class=\"ilc_Comment\">&nbsp;</span>", " ");
		text2 = replaceAll(text2, "<code class=\"ilc_Code\">&nbsp;</code>", " ");

		text2 = replaceAll(text2, "<span class=\"ilc_Strong\"> </span>", " ");
		text2 = replaceAll(text2, "<span class=\"ilc_Emph\"> </span>", " ");
		text2 = replaceAll(text2, "<span class=\"ilc_Quotation\"> </span>", " ");
		text2 = replaceAll(text2, "<span class=\"ilc_Comment\"> </span>", " ");
		text2 = replaceAll(text2, "<code class=\"ilc_Code\"> </code>", " ");
		
		for (i=0;i<10;i++) {
			text2 = replaceAll(text2, " <\/span>","</span> ");
			text2 = replaceAll(text2, " <\/code>","</code> ");
		}
		
		text2 = replaceAll(text2,"&nbsp;"," ");
		
		editor.setHTML(text2);
	} 
	else 
	{
		text2 = editor.getHTML();
		text2 = replaceAll(text2, "<"+tagName+" class=\""+className+"\"></"+tagName+">", "");
		text2 = replaceAll(text2, "<span class=\"ilc_Strong\"></span>", "");
		text2 = replaceAll(text2, "<span class=\"ilc_Emph\"></span>", "");
		text2 = replaceAll(text2, "<span class=\"ilc_Quotation\"></span>", "");
		text2 = replaceAll(text2, "<span class=\"ilc_Comment\"></span>", "");
		text2 = replaceAll(text2, "<code class=\"ilc_Code\"></code>", "");

		text2 = replaceAll(text2, "<span class=\"ilc_Strong\">&nbsp;</span>", " ");
		text2 = replaceAll(text2, "<span class=\"ilc_Emph\">&nbsp;</span>", " ");
		text2 = replaceAll(text2, "<span class=\"ilc_Quotation\">&nbsp;</span>", " ");
		text2 = replaceAll(text2, "<span class=\"ilc_Comment\">&nbsp;</span>", " ");
		text2 = replaceAll(text2, "<code class=\"ilc_Code\">&nbsp;</code>", " ");
		
		text2 = replaceAll(text2,"&nbsp;"," ");

		for (i=0;i<10;i++) {
			text2 = replaceAll(text2, " <\/span>","</span> ");
			text2 = replaceAll(text2, " <\/code>","</code> ");
		}

		editor.setHTML(text2);
	}
	
	editor.updateToolbar();
		// }}}
}


function tt_bis(Text,BisText) {
	local_T2 = Text.substr(0,Text.indexOf(BisText));
	return(local_T2);
}

function tt_hinter(Text,AbText) {
	local_T2 = Text.substring(Text.indexOf(AbText)+AbText.length,Text.length);
	return(local_T2);
}
function tt_zwischen(Text,Ab,Bis) {
	local_T2 = tt_hinter(Text,Ab);
	local_T2 = tt_bis(local_T2,Bis);
	return(local_T2);
}

function addLine(L) {
	// document.F.T2.value += L+"\n";
}

var startMarker = "*$123$*";
var endMarker = "#$123$#";

var inTag = new Array();
var inTag2 = new Array();
var insideTag = new Array();
var splitTag = new Array();

var fall5Depth = 0;

function inWhichTag(T) {
	local_inTag = new Array();
	
	while (T.indexOf("<")!=-1) {
		T2 = tt_hinter(T,"<");
		T3 = tt_bis(T2,">");
		T = tt_hinter(T2,">");
		
		if(T3.substr(0,1)=="/") {
			local_inTag.pop();
		} else {
			local_inTag.push(T3);
		}
		
	}
	return(local_inTag);
}

function splitTags(T) {
	local_splitTag = new Array();
	
	while (T.indexOf("<")!=-1) {
	
		T1 = tt_bis(T,"<");
		T2 = tt_zwischen(T,"<",">");
		T = tt_hinter(T,">");
		
		local_splitTag.push(T1);
		local_splitTag.push("<"+T2+">");
	}
	if (T!="") local_splitTag.push(T);
	
	return(local_splitTag);
}

/**
*	feststellen, ob ein Tag schon in der Liste der Tags vorkommt.
*/
function isInTag(tags,tagName,className) {
	found = false;
	for(i=0;i<tags.length;i++) {
		T = "<"+tags[i];
		if (T.indexOf("<"+tagName)!=-1) {
			if (className=="") {
				return(true);
			} else {
				if (T.indexOf("class=\""+className+"\"") != -1 || T.indexOf("class='"+className+"'") != -1 || T.indexOf("class="+className+"") != -1) {
					return(true);
				}
			}
		}
	}
	return(found);
}

function isTagsInside(text,tagName,className) {
	local_splitTag = splitTags(text);
	for(i=0;i<local_splitTag.length;i++) {
		if(local_splitTag[i].indexOf("<"+tagName)!=-1) {
			if(className=="") return(true);
			else if (local_splitTag[i].indexOf("class=\""+className+"\"")!=-1) {
				return(true);
			}
		}
	}
	return(false);
}

function isTag(text,tagName,className) {
	text = "<"+text;
	if(text.indexOf("<"+tagName)!=-1) {
		
		if(className=="") return(true);
		else if (text.indexOf("class=\""+className+"\"")!=-1) {
			return(true);
		}
	}
	return(false);
}

function nurTag(nTag) {
	if (nTag.indexOf(" ")!=-1) {
		T = tt_bis(nTag," ");
	} else {
		T = nTag;
	}
	return(T);
}

function addTag(Text,tagName,className) {

	// ----------------------------------------------------------------------
	// {{{ Hier werden doppelte geschachtelte Tags entfernt.
	T1 = splitTags(Text);
	T2 = new Array();
	
	da = new Array();
	da2 = new Array();
	
	for(i=0;i<T1.length;i++) {
		nr = "-";
		if (T1[i].substr(0,1)=="<") {
			if (T1[i].substr(0,2)=="</") {
			
				nr = da2[da2.length-1];
				if(nr==0) T2.push(T1[i]);
				da.pop();
				da2.pop();
			} else {
				
				nr = 0;
				for(j=0;j<da.length;j++) {
					if(da[j] == T1[i]) nr++;
				}
				
				da.push(T1[i]);
				da2.push(nr);
				if(nr==0) T2.push(T1[i]);
			}
		
		} else {
			T2.push(T1[i]);
		
		}
	}

	Text = "";
	for(i=0;i<T2.length;i++) {
		Text += T2[i];
	}
	// }}}
	// ----------------------------------------------------------------------
	
	Vor = tt_bis(Text,startMarker);
	Hinter = tt_hinter(Text,endMarker);
	Zwischen = tt_zwischen(Text,startMarker,endMarker); 
	
	//alert(Zwischen);
	
	//alert(Text);
	
	//alert(Vor+"\n"+Zwischen+"\n"+Hinter);
	
	while(Zwischen.substr(0,1)=="<" && Zwischen.substr(0,2)!="</") {
		Z1 = Zwischen.substring(0,Zwischen.indexOf(">")+1);
		Z2 = Zwischen.substring(Zwischen.indexOf(">")+1,Zwischen.length);
		Vor += Z1;
		Zwischen = Z2;
	}

	while (Zwischen.substr(0,1)==" ") {
		Zwischen = Zwischen.substring(1,Zwischen.length);
		Vor += " ";
	}

	while (Zwischen.substr(Zwischen.length-1,1)==" ") {
		Zwischen = Zwischen.substring(0,Zwischen.length-1);
		Hinter = " "+Hinter;
	}

	
	//alert(Vor+"\n"+Zwischen+"\n"+Hinter);
	while (Zwischen.substr(Zwischen.length-1,1)==">") {
		ip = Zwischen.lastIndexOf("<");
		if (Zwischen.substr(ip,2)=="</") {
			Z1 = Zwischen.substring(0,ip);
			Z2 = Zwischen.substring(ip,Zwischen.length);
			
			Zwischen = Z1;
			Hinter = Z2+Hinter;
		} else break;
	}

	//alert(Vor+"\n#"+Zwischen+"#\n"+Hinter);
	
	//addLine(Vor);
	
	inTag = inWhichTag(Vor);
	
/*

	for(i=0;i<inTag.length;i++) {
		addLine(inTag[i]);
	}
	addLine("--------------------------------------");
	//addLine(Vor+Zwischen);
*/

	inTag2 = inWhichTag(Vor+Zwischen);

	/*
	for(i=0;i<inTag2.length;i++) {
		addLine(inTag2[i]);
	}
	addLine("--------------------------------------");
	
	j=0;
	for(i=inTag.length;i<inTag2.length;i++) {
		insideTag[j++] = inTag2[i];
	}
	*/
	
	/*
	addLine("--------------------------------------");
	for(i=0;i<insideTag.length;i++) {
		addLine(insideTag[i]);
	}
	*/
	
	//addLine("--------------------------------------");
	
	//addLine(Zwischen);
	
	splitTag = splitTags(Zwischen);
	
/*
	for(i=0;i<splitTag.length;i++) {
		addLine(splitTag[i]);
	}
*/	
	// zunächst einmal den möglichen Fall feststellen
	Fall = 0;
	if (!isInTag(inTag,tagName,className) && !isInTag(inTag2,tagName,className) && !isTagsInside(Zwischen,tagName,className) ) Fall = 1;
	else if (!isInTag(inTag,tagName,className) && !isInTag(inTag2,tagName,className) && isTagsInside(Zwischen,tagName,className) ) Fall = 5;
	else if (!isInTag(inTag,tagName,className) && isInTag(inTag2,tagName,className) ) Fall = 3;
	else if (isInTag(inTag,tagName,className) && !isInTag(inTag2,tagName,className) ) Fall = 4;
	else if (isInTag(inTag,tagName,className) && isInTag(inTag2,tagName,className) ) Fall = 2;
	
	//addLine("Fall: "+Fall);
		
    if(Fall==1) {
		for(i=0;i<splitTag.length;i++) {
			S = splitTag[i];

			if(S.substr(0,1)!="<") {
				S = "<"+tagName+" class=\""+className+"\">"+S+"</"+tagName+">";
			}
			
			splitTag[i] = S;
		}
	} else if (Fall==2) {
	
		//addLine("Zwischen: "+Zwischen);
		/*for(i=0;i<splitTag.length;i++) {
			addLine(splitTag[i]);
		}*/
		
		
		if(inTag.length == inTag2.length) {	// Fall 2a
			//addLine("A");
			insVor = "";
			insNach = "";
			for(i=inTag.length-1;i>=0;i--) {
				if (isTag( inTag[i], tagName,className )) break;
				insVor = insVor+"</"+nurTag(inTag[i])+">";
				insNach = "<"+inTag[i]+">"+insNach;
			}
			insVor2 = "";
			insNach2 = "";
			for(i=inTag2.length-1;i>=0;i--) {
				if (isTag( inTag2[i], tagName,className )) break;
				insVor2 = insVor2+"</"+nurTag(inTag2[i])+">";
				insNach2 = "<"+inTag2[i]+">"+insNach2;
			}
			//addLine("X: "+insVor+" - "+insNach);
			
			splitTag[0] = insVor+"</"+tagName+">"+insNach+splitTag[0];
			splitTag[splitTag.length-1] = splitTag[splitTag.length-1]+insVor2+"<"+tagName+" class=\""+className+"\">"+insNach2;
		} else if(inTag.length > inTag2.length) {	// Fall 2b
			//addLine("B");
			insVor = "";
			insNach = "";
			for(i=inTag.length-1;i>=0;i--) {
				if (isTag( inTag[i], tagName,className )) break;
				insVor = insVor+"</"+nurTag(inTag[i])+">";
				insNach = "<"+inTag[i]+">"+insNach;
			}
			insVor2 = "";
			insNach2 = "";
			for(i=inTag2.length-1;i>=0;i--) {
				if (isTag( inTag2[i], tagName,className )) break;
				insVor2 = insVor2+"</"+nurTag(inTag2[i])+">";
				insNach2 = "<"+inTag2[i]+">"+insNach2;
			}
			//addLine("X: "+insVor+" - "+insNach+" / "+insVor2+" - "+insNach2);
			
			splitTag[0] = insVor+"</"+tagName+">"+insNach+splitTag[0];
			splitTag[splitTag.length-1] = splitTag[splitTag.length-1]+insVor2+"<"+tagName+" class=\""+className+"\">"+insNach2;
			
		} else if(inTag.length < inTag2.length) {	// Fall 2c
			
			//addLine("C");
			insVor = "";
			insNach = "";
			for(i=inTag.length-1;i>=0;i--) {
				if (isTag( inTag[i], tagName,className )) break;
				insVor = insVor+"</"+nurTag(inTag[i])+">";
				insNach = "<"+inTag[i]+">"+insNach;
			}
			insVor2 = "";
			insNach2 = "";
			for(i=inTag2.length-1;i>=0;i--) {
				if (isTag( inTag2[i], tagName,className )) break;
				insVor2 = insVor2+"</"+nurTag(inTag2[i])+">";
				insNach2 = "<"+inTag2[i]+">"+insNach2;
			}
			
			//addLine("X: "+insVor+" - "+insNach+" / "+insVor2+" - "+insNach2);
			
			splitTag[0] = insVor+"</"+tagName+">"+insNach+splitTag[0];
			splitTag[splitTag.length-1] = splitTag[splitTag.length-1]+insVor2+"<"+tagName+" class=\""+className+"\">"+insNach2;
			
			
		
		}
		
	
	} else if (Fall==3) {
	
		
		for(i=0;i<splitTag.length;i++) {
			
			if (isTag(splitTag[i], tagName, className)) {	// Wenn es das richtige Tag ist, dann ok und raus aus der schleife
				splitTag[i] = "";
				
				break;
			} else if (splitTag[i].substr(0,1)=="<") {	// Sonst, wenn es ein anderes Tag ist, dann tagName drumherum.
				splitTag[i] = "</"+tagName+">"+splitTag[i]+"<"+tagName+" class=\""+className+"\">";
			}
			
		}
		
		splitTag[0] = "<"+tagName+" class=\""+className+"\">"+splitTag[0]; 
	
		/*
		for(i=0;i<splitTag.length;i++) {
			addLine(splitTag[i]);
		}
		*/
		
	} else if (Fall==4) {
	
		splitTag2 = splitTags(Vor+Zwischen);
		splitTag3 = splitTags(Zwischen);
	
		//addLine("Zwischen: "+Zwischen);
		
/*
for(i=0;i<inTag.length;i++) {
			addLine(inTag[i]);
		}
		addLine("------------------------");
		for(i=0;i<inTag2.length;i++) {
			addLine(inTag2[i]);
		}
		addLine("#####################################");
*/

		pos=0;
		for(i=inTag.length-1;i>=0;i--) {
			if(isTag(inTag[i],tagName,className)) {
				break;
			}
			pos++;
		}
//		addLine("pos: "+pos);
		
		j=pos;
		found = false;
		for(i=0;i<splitTag3.length;i++) {
			S = splitTag3[i];
			
			if(S.substr(0,1)=="<") {
				if (found==false) {
					if (j==0) {
						S = "";
						found = true;
					} else {
						j--;
					}
				} else {
					S = "</"+tagName+">"+S+"<"+tagName+" class=\""+className+"\">";
				}
			}
			
			splitTag3[i] = S;
		}
		
		splitTag3[splitTag3.length-1] = splitTag3[splitTag3.length-1]+"</"+tagName+">"; 
		
		Zwischen2 = "";
		for(i=0;i<splitTag3.length;i++) {
			//addLine(splitTag3[i]);
			Zwischen2 += splitTag3[i];
		}
		
	} else if (Fall==5) {
		
		fall5Depth++;
		
		splitTag3 = splitTags(Zwischen);
		
		/*
		for(i=0;i<splitTag3.length;i++) {
			addLine(splitTag3[i]);
		}
		*/
		
		Zwischen2 = "";
		nr = 1;
		for(i=0;i<splitTag3.length;i++) {
			if(isTag(splitTag3[i],tagName,className) || splitTag3[i].substr(0,2)=="</") {
				splitTag3[i] += "+%"+nr+"x"+fall5Depth+"x%+";
				nr++;
				splitTag3[i] += "%%"+nr+"x"+fall5Depth+"x%%";
				//break;
			} 
			//addLine(splitTag3[i]);
		}
		for(i=0;i<splitTag3.length;i++) {
			Zwischen2 += splitTag3[i];
		}
		Text2 = Vor+"%%1x"+fall5Depth+"x%%"+Zwischen2+"+%"+nr+"x"+fall5Depth+"x%+"+Hinter;
		addLine("Text2: "+Text2);
		addLine("^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^");
		//Text2 = Text2.replace(startMarker,"$$1$$");
		//Text2 = Text2.replace(endMarker,"+$2$+");
	
		SM = startMarker;
		EM = endMarker;
		
		for(jnr=1;jnr<=nr;jnr++) {
			startMarker = "%%"+jnr+"x"+fall5Depth+"x%%";
			endMarker = "+%"+jnr+"x"+fall5Depth+"x%+";
			Text2 = addTag(Text2,tagName,className);
		}
		
		
		/*
		startMarker = "%%2x"+fall5Depth+"x%%";
		endMarker = "+%2x"+fall5Depth+"x%+";
		Ergebis = addTag(Text2,tagName,className);
		*/
		fall5Depth--;
		
		startMarker = SM;
		endMarker = EM;
		
		
	}	

	
	
	//addLine("--------------------------------------");
	
	if (Fall==5) {
		// nix
	} else if (Fall==4) {
		Ergebnis = Vor+Zwischen2+Hinter;
	} else {
		Zwischen2 = "";
		for(i=0;i<splitTag.length;i++) {
			//addLine(splitTag[i]);
			Zwischen2 += splitTag[i];
		}
		Ergebnis = Vor+Zwischen2+Hinter;
	}
	
	
	
	// Aufräumen, wenn öffnendes und schießendes Tag direkt hintereinander folgen, dann können diese raus.
	for(i=0;i<50;i++) {
		Ergebnis = Ergebnis.replace("<"+tagName+" class=\""+className+"\"></"+tagName+">","");
	}
	
	//addLine("ERGEBNIS:");
	//addLine(Ergebnis);
	
	return(Ergebnis);
}

/*
F1 = "A*b#c <span class=\"bold\">123</span> xyz";
F2 = "Abc <span class=\"bold\">1*2#3</span> xyz";
F2b = "Abc <span class=\"bold\">1*a<span class=\"italic\">bc#xy</span>z3</span> xyz";
F3 = "Ab*c <span class=\"bold\">1#23</span> xyz";
F4 = "Abc <span class=\"bold\">12*3</span> x#yz";
F4b = "Abc <i><span class=\"italic\"><span class=\"bold\">1<span class=\"quot\">2*3</span>33</span> x</span>asd#yz</i>";
F5 = "Ab*c <span class=\"bold\">123</span> x#yz";
*/
/*
function start() {
	
	E = addTag(document.F.T1.value, "span", "bold");
	document.F.T2.value = E;
}
*/
