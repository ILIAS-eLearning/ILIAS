/**
* Captions display management of the players MCV pattern.
*
* @author	Jeroen Wijering
* @version	1.4
**/


import com.jeroenwijering.players.*;
import flash.filters.DropShadowFilter;


class com.jeroenwijering.players.CaptionsView extends AbstractView { 


	/** The current volume **/
	private var parser:CaptionsParser;
	/** The captions array **/
	private var captions:Array;
	/** The current elapsed time **/
	private var currentTime:Number;
	/** The captions textfield **/
	private var clip:MovieClip;
	/** Boolean for captionate captions **/
	private var captionate:Boolean = false;
	/** Time of last caption **/
	private var capTime:Number;
	/** Captionate track to use **/
	private var capTrack:Number = 0;


	/** Constructor, loads caption file. **/
	function CaptionsView(ctr:AbstractController,cfg:Object,fed:Object) {
		super(ctr,cfg,fed);
		var ref = this;
		Stage.addListener(this);
		parser = new CaptionsParser();
		parser.onParseComplete = function() {
			this.parseArray.sortOn("bgn",Array.NUMERIC);
			ref.captions = this.parseArray;
			delete this;
		}
		clip = config["clip"].captions;
		setDimensions();
	};


	/** onLoad override, sets capture sizes. **/
	private function setDimensions() {
		clip.txt.autoSize = "center";
		clip.bck._height = clip.txt._height + 10;
		if(Stage["displayState"] == "fullScreen") {
			clip._width = Stage.width;
			clip._yscale= clip._xscale;
			clip._y = Stage.height - clip._height;
		} else {
			clip._width = config["displaywidth"];
			clip._yscale = clip._xscale;
			clip._y = config["displayheight"] - clip._height;
		}
		if(System.capabilities.version.indexOf("7,0,") == -1) {
			var blr = 2 + Math.round(clip._yscale/100);
			var flt = new flash.filters.DropShadowFilter(
				0,0,0x000000,1,blr,blr,50,2);
			clip.filters = new Array(flt);
		}
	};


	/** parse a new captions file every time an item is set **/
	private function setItem(idx:Number) {
		captions = new Array();
		if(feeder.feed[idx]["captions"] == undefined) {
			clip.bck._alpha = 0;
		} else if(feeder.feed[idx]["captions"].indexOf("captionate") > -1 ||
			feeder.feed[idx]["captions"] == "true") {
			captionate = true;
			var tck = Number(feeder.feed[idx]["captions"].substr(-1));
			if(isNaN(tck)) { 
				capTrack = 0;
			} else {
				capTrack = tck;
			}
		} else {
			parser.parse(feeder.feed[idx]["captions"]);
		}
	};


	/** Check elapsed time, evaluate captions every second. **/
	private function setTime(elp:Number,rem:Number) {
		currentTime = elp;
		if (captionate == false) {
			setCaption();
		}
	};


	/** Check if a new caption should be displayed **/
	private function setCaption() {
		var nxt:Number = captions.length;
		for (var i=0; i<captions.length; i++) {
			if(captions[i]["bgn"] > currentTime) {
				nxt = i;
				break;
			}
		}
		if(captions[nxt-1]["bgn"] + captions[nxt-1]["dur"] > currentTime) {
			clip.txt.htmlText = captions[nxt-1]["txt"];
			if(System.capabilities.version.indexOf("7,0,") > -1) {
				clip.bck._alpha = 50;
				clip.bck._height = Math.round(clip.txt._height + 10);
			} else {
				clip.bck._height = Math.round(clip.txt._height + 15);
			}
			if(Stage["displayState"] == "fullScreen") {
				clip._y = Stage.height - clip._height;
			} else {
				clip._y = config["displayheight"] - clip._height;
			}
		} else {
			clip.txt.htmlText = "";
		}
	};


	/** Captionate input **/
	public function onCaptionate(cap:Array) {
		clip.txt.htmlText = cap[capTrack];
		capTime = currentTime;
	};


	/** OnResize Handler: catches stage resizing **/
	public function onResize() { setDimensions(); };


	/** Catches fullscreen escape  **/
	public function onFullScreen(fs:Boolean) { 
		if(fs == false) { setDimensions(); }
	};


}