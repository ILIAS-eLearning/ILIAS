/**
* Parses RSS, ATOM and XSPF lists and returns them as a numerical array.
*
* @author	Jeroen Wijering
* @version	1.7
**/


import com.jeroenwijering.feeds.*;


class com.jeroenwijering.feeds.FeedManager {


	/** The array the XML is parsed into. **/
	public var feed:Array;
	/** XML file **/
	private var feedXML:XML;
	/** Flag for captions. **/
	public var captions:Boolean;
	/** Flag for extra audiotrack. **/
	public var audio:Boolean;
	/** Flag for all items in mp3 **/
	public var onlymp3s:Boolean;
	/** Flag for chapter index **/
	public var ischapters:Boolean;
	/** Flag for enclosures **/
	private var enclosures:Boolean;
	/** Reference to the parser object **/
	private var parser:AbstractParser;
	/** An array with objects listening to feed updates **/
	private var listeners:Array;
	/** A prefix string for all files **/
	private var prefix:String = "";
	/** Stream to use **/
	private var stream:String;
	/** Array with all file elements **/
	private var elements:Object = {
		file:"",
		fallback:"",
		title:"",
		link:"",
		id:"",
		image:"",
		author:"",
		captions:"",
		audio:"",
		category:"",
		start:"",
		type:"",
		duration:""
	};
	/** array with all supported filetypes **/
	private var filetypes:Array = new Array(
		"flv","mp3","rbs","jpg","gif","png","rtmp",
		"swf","mp4","m4v","m4a","mov","3gp","3g2"
	);


	/** Constructor. **/
	function FeedManager(enc:Boolean,jvs:String,pre:String,str:String) {
		enc == true ? enclosures = true: enclosures = false;
		if(jvs == "true") { enableJavascript(); }
		pre == undefined ? null: prefix = pre;
		str == undefined ? null: stream = "_"+str;
		listeners = new Array();
	};


	/** Enable javascript access to loadFile command.  **/
	private function enableJavascript() {
		if(flash.external.ExternalInterface.available) {
			flash.external.ExternalInterface.addCallback(
				"loadFile",this,loadFile);
			flash.external.ExternalInterface.addCallback(
				"addItem",this,addItem);
			flash.external.ExternalInterface.addCallback(
				"removeItem",this,removeItem);
			flash.external.ExternalInterface.addCallback(
				"itemData",this,itemData);
			flash.external.ExternalInterface.addCallback(
				"getLength",this,getLength);
		}
	};


	/** Load an XML playlist or single media file. **/
	public function loadFile(obj:Object) {
		feed = new Array();
		var ftp = "xml";
		for(var i = filetypes.length; --i >= 0;) {
			if(obj['file'].substr(0,4).toLowerCase() == "rtmp") {
				ftp = "rtmp";
			} else if(obj['file'].indexOf('youtube.com') > -1) {
				ftp = "youtube";
			} else if(obj['type'] == filetypes[i]) {
				ftp = filetypes[i]; 
			} else if (obj['file'].substr(-3).toLowerCase() == filetypes[i]) {
				ftp = filetypes[i]; 
			}
		}
		if (ftp == "xml") {
			loadXML(unescape(obj['file']));
		} else {
			feed[0] = new Object();
			feed[0]['type'] = ftp;
			for(var itm in elements) {
				if(obj[itm] != undefined) {
					feed[0][itm] = obj[itm];
				}
			}
			playersPostProcess();
		}
	};


	/** Parse an XML file, return the array when done. **/
	private function loadXML(url:String) {
		var ref = this;
		feedXML = new XML();
		feedXML.ignoreWhite = true;
		feedXML.onLoad = function(scs:Boolean) {
			if(scs) {
				var fmt = this.firstChild.nodeName.toLowerCase();
				if( fmt == 'rss') {
					ref.parser = new RSSParser(ref.prefix);
					ref.feed = ref.parser.parse(this);
				} else if (fmt == 'feed') { 
					ref.parser = new ATOMParser(ref.prefix);
					ref.feed = ref.parser.parse(this);
				} else if (fmt == 'playlist') { 
					ref.parser = new XSPFParser(ref.prefix);
					ref.feed = ref.parser.parse(this);
				} else if (fmt == 'asx') { 
					ref.parser = new ASXParser(ref.prefix);
					ref.feed = ref.parser.parse(this);
				} else if (fmt == 'videolist') { 
					ref.parser = new AgriyaParser(ref.prefix);
					ref.feed = ref.parser.parse(this);
				}
				if(_root.audio != undefined) {
					ref.feed[0]["audio"] = unescape(_root.audio);
				}
				ref.playersPostProcess(url);
			}
		};
		if(_root._url.indexOf("file://") > -1) { feedXML.load(url); } 
		else if(url.indexOf('?') > -1) { feedXML.load(url+'&'+random(999)); }
		else { feedXML.load(url+'?'+random(999)); }
	};


	/** set a number of flags specifically used by the players **/
	private function playersPostProcess(url:String) {
		onlymp3s = true;
		feed.length > 1 ? ischapters = true: ischapters = false;
		captions = false;
		audio = false;
		for(var i=0; i<feed.length; i++) {
			feed[i]["file"] = prefix+feed[i]["file"];
			if(stream != undefined) {
				if(feed[i]["type"] == "rtmp") {
					feed[i]["id"] += stream;
					feed[i]["file"] = feed[i]["file"];
				} else if(feed[i]["type"] == "flv") {
					feed[i]["file"] = 
						feed[i]["file"].substr(0,feed[i]["file"].length-4) + 
						stream + feed[i]["file"].substr(-4);
				}
			}
			if(feed[i]["type"] != "mp3") { onlymp3s = false; }
			if(feed[i]["start"] == undefined) { feed[i]["start"] = 0; }
			if(feed[i]['file'] != feed[0]['file']) { ischapters = false; }
			if(feed[i]["captions"] != undefined) { captions = true; }
			if(feed[i]["audio"] != undefined) { audio = true; }
			if(feed[i]['duration'] == undefined || isNaN(feed[i]['duration'])){
				feed[i]['duration'] = 0; 
			}
			if(feed[i]['fallback'] != undefined) {
				var maj = Number(System.capabilities.version.split(' ')[1].substr(0,1));
				var min = Number(System.capabilities.version.split(',')[2]);
				if(maj < 9 || (maj == 9 && min < 90)) {
					feed[i]['file'] = feed[i]['fallback'];
				}
			}
		}
		updateListeners('new');
	}


	/** Return the lenght of the feed array. **/
	public function getLength():Number {
		return feed.length;
	}


	/** Add an item to the feed **/
	public function addItem(obj:Object,idx:Number) {
		if(obj['title'] == undefined) { obj['title'] = obj['file']; }
		if(obj['type'] == undefined) { obj['type'] = obj['file'].substr(-3); }
		if(arguments.length == 1 || idx >= feed.length) {
			feed.push(obj);
		} else {
			var arr1 = feed.slice(0,idx);
			var arr2 = feed.slice(idx);
			arr1.push(obj);
			feed = arr1.concat(arr2);
		}
		updateListeners('add');
	};


	/** Remove an item from the feed **/
	public function removeItem(idx:Number) {
		if(feed.length == 1) {
			return;
		} else  if(arguments.length == 0 || idx >= feed.length) {
			feed.pop();
		} else {
			feed.splice(idx,1);
		}
		updateListeners('remove');
	};


	/** Retrieve playlist data for a specific item **/
	public function itemData(idx:Number):Object {
		return feed[idx];
	};


	/** Add a feed update listener. **/
	public function addListener(lst:Object) {
		listeners.push(lst);
	};


	/** Remove a feed update listener. **/
	public function removeListener(lst:Object) {
		for(var i = listeners.length; --i >= 0; ) {
			if(listeners[i] == lst) {
				listeners.splice(i,1);
				return;
			}
		}
	};


	/** Notify all listeners of a feed update **/
	private function updateListeners(typ:String) {
		for(var i = listeners.length; --i >= 0; ) {
			listeners[i].onFeedUpdate(typ);
		}
	};


}