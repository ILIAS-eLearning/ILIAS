/**
* Controlbar user interface management of the players MCV pattern.
*
* @author	Jeroen Wijering
* @version	1.11
**/


import com.jeroenwijering.players.*;
import com.jeroenwijering.utils.*;


class com.jeroenwijering.players.ControlbarView extends AbstractView { 


	/** currently active item **/
	private var currentItem:Number;
	/** full width of the scrubbars **/
	private var barWidths:Number;
	/** duration of the currently playing item **/
	private var itemLength:Number;
	/** progress of the  currently playing item **/
	private var itemProgress:Number = 0
	/** do not rescale loadbar on rebuffering **/
	private var wasLoaded:Boolean = false;
	/** interval for hiding the display **/
	private var hideInt:Number;


	/** Constructor **/
	function ControlbarView(ctr:AbstractController,cfg:Object,fed:Object) { 
		super(ctr,cfg,fed);
		setColorsClicks();
		setDimensions();
		Stage.addListener(this);
	};


	/** Sets up colors and clicks of all controlbar items. **/
	private function setColorsClicks() {
		var ref = this;
		var tgt = config["clip"].controlbar;
		tgt.col = new Color(tgt.back);
		tgt.col.setRGB(config["backcolor"]);
		tgt.playpause.col1 = new Color(tgt.playpause.ply);
		tgt.playpause.col1.setRGB(config["frontcolor"]);
		tgt.playpause.col2 = new Color(tgt.playpause.pas);
		tgt.playpause.col2.setRGB(config["frontcolor"]);
		tgt.playpause.onRollOver = function() { 
			this.col1.setRGB(ref.config["lightcolor"]);
			this.col2.setRGB(ref.config["lightcolor"]);
		};
		tgt.playpause.onRollOut = function() { 
			this.col1.setRGB(ref.config["frontcolor"]);
			this.col2.setRGB(ref.config["frontcolor"]);
		};
		tgt.playpause.onPress = function() { ref.sendEvent("playpause"); };
		tgt.prev.col = new Color(tgt.prev.icn);
		tgt.prev.col.setRGB(config["frontcolor"]);
		tgt.prev.onRollOver = function() { 
			this.col.setRGB(ref.config["lightcolor"]);
		};
		tgt.prev.onRollOut = function() { 
			this.col.setRGB(ref.config["frontcolor"]);
		};
		tgt.prev.onPress = function() { ref.sendEvent("prev"); };
		tgt.next.col = new Color(tgt.next.icn);
		tgt.next.col.setRGB(config["frontcolor"]);
		tgt.next.onRollOver = function() { 
			this.col.setRGB(ref.config["lightcolor"]);
		};
		tgt.next.onRollOut = function() { 
			this.col.setRGB(ref.config["frontcolor"]);
		};
		tgt.next.onPress = function() { ref.sendEvent("next"); };
		tgt.scrub.elpTxt.textColor = config["frontcolor"];
		tgt.scrub.remTxt.textColor = config["frontcolor"];
		tgt.scrub.col = new Color(tgt.scrub.icn);
		tgt.scrub.col.setRGB(config["frontcolor"]);
		tgt.scrub.col2 = new Color(tgt.scrub.bar);
		tgt.scrub.col2.setRGB(config["frontcolor"]);
		tgt.scrub.col3 = new Color(tgt.scrub.bck);
		tgt.scrub.col3.setRGB(config["frontcolor"]);
		tgt.scrub.bck.onRollOver = function() { 
			this._parent.col.setRGB(ref.config["lightcolor"]); 
		};
		tgt.scrub.bck.onRollOut = function() { 
			this._parent.col.setRGB(ref.config["frontcolor"]); 
		};
		tgt.scrub.bck.onPress = function() {
			this.onEnterFrame = function() {
				var xm = this._parent._xmouse;
				if(xm < this._parent.bck._width + this._parent.bck._x && 
					xm > this._parent.bck._x) {
					this._parent.icn._x = this._parent._xmouse - 1;
				}
			}
		};
		tgt.scrub.bck.onRelease= tgt.scrub.bck.onReleaseOutside= function() {
			var sec = (this._parent._xmouse-this._parent.bar._x) /
				ref.barWidths*ref.itemLength;
			ref.sendEvent("scrub",Math.round(sec));
			delete this.onEnterFrame;
		};
		tgt.scrub.bck.tabEnabled = false;
		tgt.fs.col1 = new Color(tgt.fs.ns);
		tgt.fs.col2 = new Color(tgt.fs.fs);
		tgt.fs.col.setRGB(ref.config["frontcolor"]);
		tgt.fs.col2.setRGB(ref.config["frontcolor"]);
		tgt.fs.onRollOver = function() { 
			this.col1.setRGB(ref.config["lightcolor"]); 
			this.col2.setRGB(ref.config["lightcolor"]);
		};
		tgt.fs.onRollOut = function() { 
			this.col1.setRGB(ref.config["frontcolor"]);
			this.col2.setRGB(ref.config["frontcolor"]);
		};
		tgt.fs.onPress = function() {
			ref.sendEvent("fullscreen");
			this.col1.setRGB(ref.config["frontcolor"]);
			this.col2.setRGB(ref.config["frontcolor"]);
		};
		tgt.cc.col = new Color(tgt.cc.icn);
		tgt.cc.col.setRGB(ref.config["frontcolor"]);
		tgt.cc.onRollOver = function() { 
			this.col.setRGB(ref.config["lightcolor"]); 
		};
		tgt.cc.onRollOut = function() { 
			this.col.setRGB(ref.config["frontcolor"]);
		};
		tgt.cc.onPress = function() {
			ref.sendEvent("captions");
		};
		tgt.au.col = new Color(tgt.au.icn);
		tgt.au.col.setRGB(ref.config["frontcolor"]);
		tgt.au.onRollOver = function() { 
			this.col.setRGB(ref.config["lightcolor"]); 
		};
		tgt.au.onRollOut = function() { 
			this.col.setRGB(ref.config["frontcolor"]);
		};
		tgt.au.onPress = function() {
			ref.sendEvent("audio");
		};
		tgt.dl.col = new Color(tgt.dl.icn);
		tgt.dl.col.setRGB(ref.config["frontcolor"]);
		tgt.dl.onRollOver = function() { 
			this.col.setRGB(ref.config["lightcolor"]); 
		};
		tgt.dl.onRollOut = function() { 
			this.col.setRGB(ref.config["frontcolor"]);
		};
		tgt.dl.onPress = function() {
			ref.sendEvent("getlink",ref.currentItem);
		};
		tgt.vol.col = new Color(tgt.vol.bar);
		tgt.vol.col.setRGB(config["frontcolor"]);
		tgt.vol.col2 = new Color(tgt.vol.bck);
		tgt.vol.col2.setRGB(config["frontcolor"]);
		tgt.vol.col3 = new Color(tgt.vol.icn);
		tgt.vol.col3.setRGB(config["frontcolor"]);
		tgt.vol.onRollOver = function() { 
			this.col.setRGB(ref.config["lightcolor"]);
			this.col3.setRGB(ref.config["lightcolor"]);
		};
		tgt.vol.onRollOut = function() { 
			this.col.setRGB(ref.config["frontcolor"]);
			this.col3.setRGB(ref.config["frontcolor"]);
		};
		tgt.vol.onPress = function() { 
			this.onEnterFrame = function() { 
				this.msk._width = this._xmouse-12;
			}; 
		};
		tgt.vol.onRelease = tgt.vol.onReleaseOutside = function() { 
			ref.sendEvent("volume",(this._xmouse-12)*5);
			delete this.onEnterFrame; 
		};
		if(config["displayheight"] == config["height"]) {
			Mouse.addListener(this);
		}
	};


	/** Sets up dimensions of all controlbar items. **/
	private function setDimensions() {
		clearInterval(hideInt);
		var tgt = config["clip"].controlbar;
		// overall position and width
		if(Stage["displayState"] == "fullScreen") {
			tgt._x = Math.round(Stage.width/2-200);
			var cbw = 400;
			tgt._y = Stage.height - 40;
			tgt._alpha = 100;
			tgt.back._alpha = 40;
			tgt.fs.fs._visible = false;
			tgt.fs.ns._visible = true;
		} else if(config["displayheight"] == config["height"]) {
			tgt._y = config["displayheight"] - 40;
			if(config["displaywidth"] > 450 && 
				config["displaywidth"] == config["width"]) {
				tgt._x = Math.round(Stage.width/2-200);
				var cbw = 400;
			} else {
				tgt._x = 20;
				var cbw = config["displaywidth"] - 40;
			}
			tgt._alpha = 0;
			tgt._visible = false;
			tgt.back._alpha = 40;
			tgt.fs.fs._visible = true;
			tgt.fs.ns._visible = false;
		} else {
			tgt._x = 0;
			tgt._y = config["displayheight"];
			var cbw = config["width"];
			tgt._alpha = 100;
			tgt.back._alpha = 100;
			tgt.fs.fs._visible = true;
			tgt.fs.ns._visible = false;
		}
		if(config["largecontrols"] == "true") {
			tgt._xscale = tgt._yscale = 200;
			if(Stage["displayState"] == "fullScreen") {
				tgt._y = Stage.height - 60;
				cbw = 300;
				tgt._x = Math.round(Stage.width/2 - 300);
			} else {
				cbw /= 2;
			}
		}
		tgt.back._width = cbw;
		// all buttons
		if(feeder.feed.length - feeder.numads == 1 ||
			(config["displayheight"] < config["height"] - 50 && cbw < 200) ||
			(config["displaywidth"] < config["width"] - 50 && cbw < 200)) {
			tgt.prev._visible = tgt.next._visible = false;
			tgt.scrub.shd._width = cbw-17;
			tgt.scrub._x = 17;
		} else {
			tgt.prev._visible = tgt.next._visible = true;
			tgt.scrub.shd._width = cbw-51;
			tgt.scrub._x = 51;
		}
		var xp = cbw;
		if(cbw > 50 && config["showvolume"] == "true") {
			xp -= 37;
			tgt.scrub.shd._width -= 37;
			tgt.vol._x = xp;
		} else {
			xp -= 1;
			tgt.scrub.shd._width -= 1;
			tgt.vol._x = xp;
		}
		if (feeder.audio == true) {
			xp -= 17;
			tgt.scrub.shd._width -= 17;
			tgt.au._x = xp;
		} else {
			tgt.au._visible = false;
		}
		if (feeder.captions == true) {
			xp -= 17;
			tgt.scrub.shd._width -= 17;
			tgt.cc._x = xp;
		} else {
			tgt.cc._visible = false;
		}
		if (config["showdownload"] == "true") {
			xp -= 17;
			tgt.scrub.shd._width -= 17;
			tgt.dl._x = xp;
		} else {
			tgt.dl._visible = false;
		}
		if((Stage["displayState"] == undefined ||
			config["usefullscreen"] == "false" ||
			feeder.onlymp3s == true) && 
			config["fsbuttonlink"] == undefined) {
			tgt.fs._visible = false;
		} else {
			xp -= 18;
			tgt.scrub.shd._width -= 18;
			tgt.fs._x = xp;
		}
		if(config["showdigits"] == "false" || tgt.scrub.shd._width < 120 ||
			System.capabilities.version.indexOf("7,0,") > -1) {
			tgt.scrub.elpTxt._visible = tgt.scrub.remTxt._visible = false;
			tgt.scrub.bar._x = tgt.scrub.bck._x = tgt.scrub.icn._x = 5;
			barWidths = tgt.scrub.bck._width = tgt.scrub.shd._width - 10;
		} else {	
			tgt.scrub.elpTxt._visible = tgt.scrub.remTxt._visible = true;
			tgt.scrub.bar._x = tgt.scrub.bck._x = tgt.scrub.icn._x = 42;
			barWidths = tgt.scrub.bck._width = tgt.scrub.shd._width - 84;
			tgt.scrub.remTxt._x = tgt.scrub.shd._width - 39;
		}	
		tgt.scrub.bar._width = 0;
	};


	/** Show and hide the play/pause button and show activity icon **/
	private function setState(stt:Number) {
		var tgt = config["clip"].controlbar.playpause;
		switch(stt) {
			case 0:
				tgt.ply._visible = true;
				tgt.pas._visible = false;
				break;
			case 1:
				tgt.pas._visible = true;
				tgt.ply._visible = false;
				break;
			case 2:
				tgt.pas._visible = true;
				tgt.ply._visible = false;
				break;
		}
	};


	/** Print current time to controlBar **/
	private function setTime(elp:Number,rem:Number) {
		itemLength = elp + rem;
		itemProgress = Math.round(rem/(itemLength)*100);
		var tgt = config["clip"].controlbar.scrub;
		var w = Math.floor(elp/(elp+rem)*barWidths) - 2;
		elp == 0 || w < 2 ? tgt.bar._width = 0: tgt.bar._width = w - 2;
		tgt.icn._x = tgt.bar._width + tgt.bar._x + 1;
		tgt.elpTxt.text = StringMagic.addLeading(elp/60) + ":" +
			StringMagic.addLeading(elp%60);
		if(tgt.bck._width == barWidths) {
			if(_root.showdigits == "total") {
				tgt.remTxt.text = StringMagic.addLeading((elp+rem)/60)+ ":" +
					StringMagic.addLeading((elp+rem)%60);
			} else {
				tgt.remTxt.text = StringMagic.addLeading(rem/60)+ ":" +
					StringMagic.addLeading(rem%60);
			}
		}
	};


	/** New item is loaded **/ 
	private function setItem(prm:Number) { 
		wasLoaded = false; 
		currentItem = prm;
		if(feeder.feed[currentItem]['category'] == "preroll" ||
			feeder.feed[currentItem]['category'] == "postroll") {
			config["clip"].controlbar.scrub.icn._alpha = 0;
		} else {
			config["clip"].controlbar.scrub.icn._alpha = 100;
		}
	};


	/** Print current buffer amount to controlbar **/
	private function setLoad(pct:Number) {
		var tgt = config["clip"].controlbar.scrub;
		if(wasLoaded == false) {
			tgt.bck._width = Math.round(barWidths*pct/100);
		}
		tgt.remTxt.text = Math.round(pct)+" %";
		pct == 100 ? wasLoaded = true: null;
	};


	/** Reflect current volume in volumebar **/
	private function setVolume(pr1:Number) {
		var tgt = config["clip"].controlbar.vol;
		tgt.msk._width = Math.round(pr1/5);
		if(pr1 == 0) {
			tgt.icn._alpha = 40;
		} else {
			tgt.icn._alpha = 100;
		}
	};


	/** Catches stage resizing **/
	public function onResize() {
		if(_root.displayheight > config["height"]+10) {
			config["height"] = config["displayheight"] = Stage.height;
			config["width"] = config["displaywidth"] = Stage.width;
		}
		setDimensions(); 
	};


	/** Catches fullscreen escape  **/
	public function onFullScreen(fs:Boolean) {
		if(fs == false) { setDimensions(); }
	};


	/** after a delay, the controlbar is hidden **/
	private function hideBar() {
		Animations.fadeOut(config['clip'].controlbar);
		clearInterval(hideInt);
	}


	/** Mouse move shows controlbar **/
	public function onMouseMove() {
		if(Stage["displayState"] != 'fullScreen' && 
			config["clip"]._xmouse < config["displaywidth"] && 
			config["showicons"] == "true") {
			Animations.fadeIn(config['clip'].controlbar);
			clearInterval(hideInt);
			if(!config["clip"].controlbar.hitTest(_xmouse,_ymouse)) {
				hideInt = setInterval(this,"hideBar",2000);
			}
		}
	};


}