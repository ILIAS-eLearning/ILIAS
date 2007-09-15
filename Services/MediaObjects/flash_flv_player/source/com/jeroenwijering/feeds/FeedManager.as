/**
* Parses RSS, ATOM and XSPF lists and returns them as a numerical array.
*
* @author	Jeroen Wijering
* @version	1.5
**/


import com.jeroenwijering.feeds.*;


class com.jeroenwijering.feeds.FeedManager {


	/** The array the XML is parsed into. **/
	public var feed:Array;
	/** XML file **/
	private var feedXML:XML;
	/** Flag for captions. **/
	public var captions:Boolean = false;
	/** Flag for extra audiotrack. **/
	public var audio:Boolean = false;
	/** Flag for all items in mp3 **/
	public var onlymp3s:Boolean = false;
	/** Flag for chapter index **/
	public var ischapters:Boolean = true;
	/** Flag for advertisements **/
	public var numads:Number = 0;
	/** Flag for overlays **/
	public var overlays:Boolean = false;
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
	/** URL to the talkr API **/
	private var talkrURL = "http://www.talkr.com/app/get_mp3.app";
	/** array with all supported filetypes **/
	private var filetypes:Array = Array(
		"flv","mp3","rbs","jpg","gif","png","rtmp","swf","mp4","m4v","3gp"
	);
	/** Array with all file elements **/
	private var elements:Object = {
		file:"",
		title:"",
		link:"",
		id:"",
		image:"",
		author:"",
		captions:"",
		audio:"",
		category:"",
		start:"",
		type:""
	};


	/** Constructor. **/
	function FeedManager(enc:Boolean,jvs:String,pre:String,str:String) {
		enc == true ? enclosures = true: enclosures = false;
		jvs == "true" ? enableJavascript(): null;
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
		}
	};


	/** Load an XML playlist or single media file. **/
	public function loadFile(obj:Object) {
		feed = new Array();
		for (var itm in elements) {
			if(obj[itm] != undefined && obj[itm].indexOf('asfunction') == -1){ 
				_root[itm] = obj[itm];
			}
		}
		var ftp = "xml";
		for(var i = filetypes.length; --i >= 0;) {
			if(obj['file'].substr(0,4).toLowerCase() == "rtmp") {
				ftp = "rtmp";
			} else if(_root.type == filetypes[i] ||
				obj['file'].substr(-3).toLowerCase() == filetypes[i]) {
				ftp = filetypes[i]; 
			}
		}
		if (ftp == "xml" && obj['file'].indexOf('asfunction') == -1) {
			loadXML(obj['file']);
		} else {
			feed[0] = new Object();
			feed[0]['type'] = ftp;
			for(var cfv in elements) {
				if(_root[cfv] != undefined) {
					feed[0][cfv] = unescape(_root[cfv]); 
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
				trace("FORMAT: "+fmt)
				if( fmt == 'rss') {
					ref.parser = new RSSParser(ref.prefix);
					ref.feed = ref.parser.parse(this);
				} else if (fmt == 'feed') { 
					ref.parser = new ATOMParser(ref.prefix);
					ref.feed = ref.parser.parse(this);
				} else if (fmt == 'playlist') { 
					ref.parser = new XSPFParser(ref.prefix);
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
		url == undefined ? null: filterOverlays();
		onlymp3s = true;
		feed.length > 1 ? ischapters = true: ischapters = false;
		captions = false;
		audio = false;
		numads = 0;
		for(var i=0; i<feed.length; i++) {
			if(enclosures == true && feed[i]['type'] == undefined && 
				url != undefined) {
				feed[i]["type"] = "mp3";
				feed[i]["file"] = talkrURL + "?feed_url=" + url + 
					"&permalink=" + feed[i]["link"];
			} else if (stream == undefined) {
				feed[i]["file"] = prefix + feed[i]["file"];
			} else {
				if(feed[i]["type"] == "rtmp") {
					feed[i]["id"] += stream;
					feed[i]["file"] = prefix + feed[i]["file"];
				} else if(feed[i]["type"] == "flv") {
					feed[i]["file"] = prefix + 
						feed[i]["file"].substr(0,feed[i]["file"].length-4) + 
						stream + feed[i]["file"].substr(-4);
				}
			}
			if(feed[i]["type"] != "mp3") { onlymp3s = false; }
			if(feed[i]["start"] == undefined) { feed[i]["start"] = 0; }
			if(feed[i]['file'] != feed[0]['file']) { ischapters = false; }
			if(feed[i]["captions"] != undefined) { captions = true; }
			if(feed[i]["audio"] != undefined) { audio = true; }
			if(feed[i]['category'] == "preroll" || 
				feed[i]['category'] == "postroll") {
				numads++;
				if(feed[i]['category'] == "preroll") {
					feed[i]['image'] = feed[i+1]['image'];
				}
			}
		}
		updateListeners();
	}


	/** Filter overlay ads out of the feeds **/
	private function filterOverlays() {
		var j = 0;
		while(j < feed.length) {
			if(feed[j]['category'] == 'overlay') {
				feed[j+1]['overlayfile'] = feed[j]['file'];
				feed[j+1]['overlaylink'] = feed[j]['link'];
				feed.splice(j,1);
				overlays = true;
			}
			j++;
		}
	};


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
		updateListeners();
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
		updateListeners();
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
	private function updateListeners() {
		for(var i = listeners.length; --i >= 0; ) {
			listeners[i].onFeedUpdate();
		}
	};


}