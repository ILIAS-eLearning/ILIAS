/**
* Parses ASX feeds and returns an indexed array with all elements
*
* @author	Jeroen Wijering
* @version	1.0
**/


import com.jeroenwijering.feeds.AbstractParser;
import com.jeroenwijering.utils.StringMagic;


class com.jeroenwijering.feeds.ASXParser extends AbstractParser {


	/** Contructor **/
	function ASXParser() { super(); };


	/** build an array with all regular elements **/
	private function setElements() {
		elements = new Object();
		elements["title"] = "title";
		elements["author"] = "author";
		elements["abstract"] = "description";
	};


	/** Convert RSS structure to array **/
	private function parse(xml:XML):Array {
		var arr = new Array();
		var tpl = xml.firstChild.firstChild;
		while(tpl != null) {
			if (tpl.nodeName.toLowerCase() == "entry") {
				var obj = new Object();
				for(var j=0; j<tpl.childNodes.length; j++) {
					var nod:XMLNode = tpl.childNodes[j];
					var nnm = nod.nodeName.toLowerCase();
					if(elements[nnm] != undefined) {
						obj[elements[nnm]] = nod.firstChild.nodeValue;
					} else if(nnm == "moreinfo") {
						obj["link"] = nod.attributes.href;
					} else if(nnm == "duration") {
						obj["duration"] = 
							StringMagic.toSeconds(nod.attributes.value);
					} else if(nnm == "ref") {
						obj["file"] = nod.attributes.href;
						var typ = nod.attributes.href.substr(-3);
						if(mimetypes[typ]!=undefined) {
							obj["type"] = mimetypes[typ];
						}
						if(obj["file"].substr(0,4) == "rtmp") {
							obj["type"] = "rtmp";
						} else if(obj['file'].indexOf('youtube.com') > -1) {
							obj["type"] = "youtube";
						}
					} else if(nnm == "param") {
						obj[nod.attributes.name] = nod.attributes.value;
					}
				}
				arr.push(obj);
			}
			tpl = tpl.nextSibling;
		}
		return arr;
	};


}