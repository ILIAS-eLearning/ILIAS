/**
* Parses ATOM feeds and returns an indexed array with all elements
*
* @author	Jeroen Wijering
* @version	1.5
**/


import com.jeroenwijering.feeds.AbstractParser;
import com.jeroenwijering.utils.StringMagic;


class com.jeroenwijering.feeds.RSSParser extends AbstractParser {


	/** Contructor **/
	function RSSParser() { super(); };


	/** build an array with all regular elements **/
	private function setElements() {
		elements = new Object();
		elements["title"] = "title";
		elements["guid"] = "id";
		elements["category"] = "category";
		elements["link"] = "link";
		elements["geo:lat"] = "latitude";
		elements["geo:long"] = "longitude";
		elements["geo:city"] = "city";
	};


	/** Convert RSS structure to array **/
	private function parse(xml:XML):Array {
		var arr = new Array();
		var tpl = xml.firstChild.firstChild.firstChild;
		var ttl;
		while(tpl != null) {
			if (tpl.nodeName.toLowerCase() == "item") {
				var obj = new Object();
				for(var j=0; j<tpl.childNodes.length; j++) {
					var nod:XMLNode = tpl.childNodes[j];
					var nnm = nod.nodeName.toLowerCase();
					if(elements[nnm] != undefined) {
						obj[elements[nnm]] = nod.firstChild.nodeValue;
					} else if(nnm == "description") {
						obj["description"] = StringMagic.stripTagsBreaks(
							nod.firstChild.nodeValue);
					} else if(nnm == "pubdate") {
						obj["date"] = rfc2Date(nod.firstChild.nodeValue);
					} else if(nnm == "dc:date") {
						obj["date"] = iso2Date(nod.firstChild.nodeValue);
					} else if(nnm == "media:credit") {
						obj["author"] = nod.firstChild.nodeValue;
					} else if(nnm == "media:thumbnail") {
						obj["image"] = nod.attributes.url;
					} else if(nnm == "itunes:image") {
						obj["image"] = nod.attributes.href;
					} else if(nnm == "georss:point") {
						var gpt = nod.firstChild.nodeValue.split(" ");
						obj["latitude"] = Number(gpt[0]);
						obj["longitude"] = Number(gpt[1]);
					} else if(nnm == "enclosure" || nnm == "media:content") {
						var typ = nod.attributes.type.toLowerCase();
						if(mimetypes[typ]!=undefined && obj["type"] == undefined) {
							obj["type"] = mimetypes[typ];
							obj['file'] = nod.attributes.url;
							obj['duration'] = 
								StringMagic.toSeconds(nod.attributes.duration);
							if(obj["file"].substr(0,4) == "rtmp") {
								obj["type"] = "rtmp";
							} else if(obj['file'].indexOf('youtube.com') > -1) {
								obj["type"] = "youtube";
							}
							if(nod.childNodes[0].nodeName=="media:thumbnail"){
								obj["image"]=nod.childNodes[0].attributes.url;
							}
						} else if(obj["type"] != undefined && typ == "video/x-flv") {
							obj['fallback'] = nod.attributes.url;
						} else if(typ == "captions") {
							obj["captions"] = nod.attributes.url;
						} else if(typ == "audio") {
							obj["audio"] = nod.attributes.url;
						}
					} else if(nnm == "media:group") { 
						for(var k=0; k< nod.childNodes.length; k++) {
							var ncn=nod.childNodes[k].nodeName.toLowerCase();
							if(ncn == "media:content") {
								var ftp = nod.childNodes[k].attributes.type.toLowerCase();
								if(mimetypes[ftp] != undefined && obj["type"] == undefined) {
									obj["file"] = nod.childNodes[k].attributes.url;
									obj['duration'] = StringMagic.toSeconds(
										nod.attributes.duration);
									obj["type"]=mimetypes[ftp];
									if(obj["file"].substr(0,4) == "rtmp") {
										obj["type"] = "rtmp";
									} else if(obj['file'].indexOf('youtube.com') > -1) {
										obj["type"] = "youtube";
									}
								} 
								if(obj["type"] != undefined && ftp == "video/x-flv") {
									obj['fallback'] = nod.childNodes[k].attributes.url;
								}
							}
							if(ncn == "media:thumbnail") {
								obj["image"]=nod.childNodes[k].attributes.url;
							}
							if(ncn == "media:credit") {
								obj["author"]=nod.childNodes[k].firstChild.nodeValue;
							}
						}
					}
				}
				if(obj["image"] == undefined) {
					if(obj["file"].indexOf(".jpg") > 0 || 
						obj["file"].indexOf(".png") > 0 || 
						obj["file"].indexOf(".gif") > 0) {
						obj["image"] = obj["file"];
					}
				}
				if(obj["author"] == undefined) { obj["author"] = ttl; }
				arr.push(obj);
			} else if (tpl.nodeName == "title") {
				ttl = tpl.firstChild.nodeValue;
			}
			tpl = tpl.nextSibling;
		}
		return arr;
	};


}