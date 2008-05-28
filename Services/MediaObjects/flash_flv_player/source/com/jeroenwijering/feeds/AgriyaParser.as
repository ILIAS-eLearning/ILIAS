/**
* Parses Agriya playlists and returns an indexed array with all elements
*
* @author	Jeroen Wijering
* @version	1.0
**/


import com.jeroenwijering.feeds.AbstractParser;
import com.jeroenwijering.utils.StringMagic;


class com.jeroenwijering.feeds.AgriyaParser extends AbstractParser {


	/** Contructor **/
	function AgriyaParser() { super(); };


	/** Convert Agriya structure to array **/
	private function parse(xml:XML):Array {
		var arr = new Array();
		var tpl = xml.firstChild.firstChild.firstChild;
		while(tpl != null) {
			if (tpl.nodeName.toLowerCase() == "video") {
				var obj = new Object();
				obj['file'] = tpl.attributes.Path;
				obj['image'] = tpl.attributes.Thumbnail;
				obj['title'] = tpl.attributes.Description;
				if(obj["file"].substr(0,4) == "rtmp") {
					obj["type"] = "rtmp";
				} else if(obj['file'].indexOf('youtube.com') > -1) {
					obj["type"] = "youtube";
				} else { 
					obj['type'] = obj["file"].substr(-3).toLowerCase();
				}
				arr.push(obj);
			}
			tpl = tpl.nextSibling;
		}
		return arr;
	};


}