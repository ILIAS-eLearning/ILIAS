/**
* Parses 1-level or 2-level deep simple XML lists.
*
* @author	Jeroen Wijering
* @version	1.3
**/


class com.jeroenwijering.utils.ListParser {


	/** URL of the xml file to parse. **/
	private var parseURL:String;
	/** The array the XML is parsed into **/
	public var parseArray:Array; 
	/** Flash XML object the file is loaded into. **/
	private var parseXML:XML;
	/** Switch for numeric or associative 1-level array **/
	private var isNumeric:Boolean = false;


	/** Constructor. **/
	function ListParser() {};


	/** Parse a simple XML list file **/
	public function parse(url:String) {
		var ref = this;
		trace("URL: "+url);
		parseURL = url;
		parseArray = new Array();
		parseXML = new XML();
		parseXML.ignoreWhite = true;
		parseXML.onLoad = function(success:Boolean) {
			if(success) { 
				ref.parseList(); 
			} else { 
				parseArray.push( {title:"Feed not found: "+ref.parseURL}); 
			}
			if(parseArray.length == 0) { 
				parseArray.push({title:"Empty feed: "+ref.parseURL});
			}
			delete ref.parseXML;
			ref.onParseComplete();
		};
		if(_root._url.indexOf("file://") > -1) { 
			parseXML.load(parseURL); 
		} else if(parseURL.indexOf('?') > -1) { 
			parseXML.load(parseURL+'&'+random(999)); 
		} else { 
			parseXML.load(parseURL+'?'+random(999));
		}
	};


	/** Covert general XML list to array. **/
	private function parseList() {
		if(parseXML.firstChild.childNodes[0].nodeName == 
			parseXML.firstChild.childNodes[1].nodeName) {
			isNumeric = true;
		}
		for(var i=0; i<parseXML.firstChild.childNodes.length; i++) {
			var itm = parseXML.firstChild.childNodes[i];
			if(itm.firstChild.nodeName == null) {
				if(isNumeric == true) {
					parseArray.push(itm.firstChild.nodeValue);
				} else {
					parseArray[itm.nodeName] = itm.firstChild.nodeValue;
				}
			} else {
				parseArray[i] = new Object();
				for(var j=0; j<itm.childNodes.length; j++) {
					if(isNaN(itm.childNodes[j].firstChild.nodeValue)){ 
						parseArray[i][itm.childNodes[j].nodeName] = 
							itm.childNodes[j].firstChild.nodeValue;
					} else { 
						parseArray[i][itm.childNodes[j].nodeName] = 
							Number(itm.childNodes[j].firstChild.nodeValue);
					}
				}
			}
		} 
	};


	/** Invoked when parsing is completed. **/
	public function onParseComplete() { };


}