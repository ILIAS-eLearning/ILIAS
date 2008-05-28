/**
* Parses ATOM feeds and returns an indexed array with all elements
*
* @author	Jeroen Wijering
* @version	1.4
**/


import com.jeroenwijering.feeds.AbstractParser;
import com.jeroenwijering.utils.StringMagic;


class com.jeroenwijering.feeds.ATOMParser extends AbstractParser {


	/** Contructor **/
	function ATOMParser() { super(); };


	/** build an array with all regular elements **/
	private function setElements() {
		elements = new Object();
		elements["title"] = "title";
		elements["id"] = "id";
	};


	/** Convert ATOM structure to array **/
	private function parse(xml:XML):Array {
		var arr = new Array();
		var tpl = xml.firstChild.firstChild;
		var ttl;
		while(tpl != null) {
			if (tpl.nodeName.toLowerCase() == "entry") {
				var obj = new Object();
				for(var j=0; j<tpl.childNodes.length; j++) {
					var nod:XMLNode = tpl.childNodes[j];
					var nnm = nod.nodeName.toLowerCase();
					if(elements[nnm] != undefined) {
						obj[elements[nnm]]=nod.firstChild.nodeValue;
					} else if(nnm=="link" && nod.attributes.rel=="alternate"){
						obj["link"] =  nod.attributes.href;
					} else if(nnm == "summary") {
						obj["description"] = StringMagic.stripTagsBreaks(
							nod.firstChild.nodeValue);
					} else if(nnm == "published") {
						obj["date"] = iso2Date(nod.firstChild.nodeValue);
					} else if(nnm == "updated") {
						obj["date"] = iso2Date(nod.firstChild.nodeValue);
					} else if(nnm == "modified") {
						obj["date"] = iso2Date(nod.firstChild.nodeValue);
					} else if(nnm == "category") {
						obj["category"] = nod.attributes.term;
					} else if(nnm == "author") { 
						for(var k=0; k< nod.childNodes.length; k++) {
							if(nod.childNodes[k].nodeName == "name") {
								obj["author"] = 
									nod.childNodes[k].firstChild.nodeValue;
							}
						}
					} else if(nnm=="link" && nod.attributes.rel=="enclosure"){
						var typ = nod.attributes.type.toLowerCase();
						if(mimetypes[typ] != undefined){
							obj["file"] = nod.attributes.href;
							obj["type"] = mimetypes[typ];
							if(obj["file"].substr(0,4) == "rtmp") {
								obj["type"] = "rtmp";
							}
						} else if(obj["type"] != undefined && typ == "video/x-flv") {
							obj["fallback"] = nod.attributes.href;
						}
					} else if (nnm=="link" && nod.attributes.rel=="captions"){
						obj["captions"] = nod.attributes.href;
					} else if (nnm=="link" && nod.attributes.rel=="audio"){
						obj["audio"] = nod.attributes.href;
					} else if (nnm=="link" && nod.attributes.rel=="image"){
						obj["image"] = nod.attributes.href;
					}
				}
				obj["author"] == undefined ? obj["author"] = ttl: null;
				arr.push(obj);
			} else if (tpl.nodeName == "title") { 
				ttl = tpl.firstChild.nodeValue;
			}
			tpl = tpl.nextSibling;
		}
		return arr;
	};


}