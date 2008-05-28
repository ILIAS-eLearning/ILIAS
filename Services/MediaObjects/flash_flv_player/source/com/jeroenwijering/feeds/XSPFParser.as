/**
* Parses ATOM feeds and returns an indexed array with all elements.
*
* @author	Jeroen Wijering
* @version	1.5
**/


import com.jeroenwijering.feeds.AbstractParser;
import com.jeroenwijering.utils.StringMagic;


class com.jeroenwijering.feeds.XSPFParser extends AbstractParser {


	/** Contructor **/
	function XSPFParser() { super(); };


	/** build an array with all regular elements **/
	private function setElements() {
		elements = new Object();
		elements["title"] = "title";
		elements["creator"] = "author";
		elements["info"] = "link";
		elements["image"] = "image";
		elements["identifier"] = "id";
		elements["album"] = "category";
	};


	/** Convert ATOM structure to array **/
	private function parse(xml:XML):Array {
		var arr = new Array();
		var tpl = xml.firstChild.firstChild;
		while(tpl != null) { 
			if (tpl.nodeName == 'trackList') {
				for(var i=0; i<tpl.childNodes.length; i++) {
					var obj = new Object();
					for(var j=0; j<tpl.childNodes[i].childNodes.length; j++) {
						var nod:XMLNode = tpl.childNodes[i].childNodes[j];
						var nnm = nod.nodeName.toLowerCase();
						if(elements[nnm]!=undefined) {
							obj[elements[nnm]] = nod.firstChild.nodeValue;
						} else if(nnm == "location"  && obj['type']!="flv") {
							obj["file"] = nod.firstChild.nodeValue;
							var typ = obj["file"].substr(-3).toLowerCase();
							if(obj["file"].substr(0,4) == "rtmp") {
								obj["type"] = "rtmp";
							} else if(obj['file'].indexOf('youtube.com') > -1) {
								obj["type"] = "youtube";
							} else if(mimetypes[typ] != undefined) {
								obj["type"] = mimetypes[typ];
							}
						} else if(nnm == "annotation") {
							obj["description"] = StringMagic.stripTagsBreaks(
								nod.firstChild.nodeValue);
						} else if(nnm == "link" && 
							nod.attributes.rel == "captions") {
							obj["captions"] = nod.firstChild.nodeValue;
						} else if(nnm == "link" && 
							nod.attributes.rel == "audio") {
							obj["audio"] = nod.firstChild.nodeValue;
						} else if(nnm == "meta") {
							obj[nod.attributes.rel] = nod.firstChild.nodeValue;
						}
					}
					arr.push(obj);
				}
			}
			tpl = tpl.nextSibling;
		}
		return arr;
	};


}