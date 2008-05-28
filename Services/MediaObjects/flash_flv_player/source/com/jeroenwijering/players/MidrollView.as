/**

* Displays XML-fed text advertisements.
*
* @author	Jeroen Wijering
* @version	1.0
**/


import com.jeroenwijering.players.*;
import com.jeroenwijering.utils.*;


class com.jeroenwijering.players.MidrollView extends AbstractView {


	/** Prefix for the midroll XML **/
	private var prefix = "http://www.ltassrv.com/midroll/config.asp?cid=";
	/** Reference to the XML parser. **/
	private var parser:XMLParser;
	/** Reference to the image loader. **/
	private var loader:ImageLoader;
	/** A list with all the configuration parameters. **/
	private var adconfig:Object;
	/** A list with all the advertisements. **/
	private var advertisements:Array;
	/** A reference to the midroll clip **/
	private var clip:MovieClip;
	/** Currently active ad **/
	private var currentAd:Number;
	/** Current playback time. **/
	private var currentTime:Number;
	/** Current playback state **/
	private var currentState:Number;
	/** Ad showing interval delay **/
	private var interval:Number;
	/** Hae we rotated though all ads? **/
	private var rotated:Boolean;


	/** Constructor; loads the ads and sets up the display **/
	function MidrollView(ctr:AbstractController,cfg:Object,fed:Object) {
		super(ctr,cfg,fed);
		var ref = this;
		clip = config['clip'].midroll;
		clip._visible = false;
		parser = new XMLParser();
		parser.onComplete = function() {
			ref.saveConfig(this.output['childs'][0]);
			ref.saveAds(this.output['childs'][1]);
		}; 
		trace(prefix+config['midroll']);
		parser.parse(prefix+config['midroll']);
		loader = new ImageLoader(clip.ovl.img.img,'false',50,50);
		loader.onLoadFinished = function() {
			Animations.fadeIn(ref.clip.ovl.img.img);
		};
		Stage.addListener(this);
	};


	/** Save the configuration options. **/
	function saveConfig(cfg:Object) {
		adconfig = new Object();
		for (var i=0; i<cfg['childs'].length; i++) {
			adconfig[cfg['childs'][i]['name']] = cfg['childs'][i]['value'];
		}
		if(config['lightcolor'] != '0x000000') {
			adconfig['mouseover_color'] = config['lightcolor'];
			adconfig['mouseover_extras'] = config['lightcolor'];
		}
		setColorsClicks();
		setDimensions();
	};


	/** Save the ads to an array. **/
	function saveAds(ads:Object) {
		advertisements = new Array();
		for (var i=0; i<ads['childs'].length; i++) {
			var obj = new Object();
			for (var j=0; j < ads['childs'][i]['childs'].length; j++) {
				obj[ads['childs'][i]['childs'][j]['name']] =
					ads['childs'][i]['childs'][j]['value'];
			}
			advertisements.push(obj);
		}
	};


	/** Setup the colors and clicks of the ad overlay. **/
	function setColorsClicks() {
		var ref = this;
		clip.btn.bck._alpha = adconfig['opacity'];
		clip.btn.bck.col = new Color(clip.btn.bck);
		clip.btn.bck.col.setRGB(adconfig['background_color']);
		clip.btn.lne.col = new Color(clip.btn.lne);
		clip.btn.lne.col.setRGB(adconfig['textcolor_description']);
		clip.btn.onRollOver = function() {
			this.lne.col.setRGB(ref.adconfig['mouseover_extras']);
		};
		clip.btn.onRollOut = function() {
			this.lne.col.setRGB(ref.adconfig['textcolor_description']);
		};
		clip.btn.onRelease = function() {
			ref.showMidroll(true);
		};
		clip.ovl.setMask(clip.msk);
		clip.ovl.bck._alpha = adconfig['opacity'];
		clip.ovl.bck.col = new Color(clip.ovl.bck);
		clip.ovl.bck.col.setRGB(adconfig['background_color']);
		clip.ovl.bck.onRelease = function() {};
		clip.ovl.bck.useHandCursor = false;
		clip.ovl.cls.col = new Color(clip.ovl.cls);
		clip.ovl.cls.col.setRGB(adconfig['textcolor_description']);
		clip.ovl.cls.onRollOver = function() {
			this.col.setRGB(ref.adconfig['mouseover_extras']);
		};
		clip.ovl.cls.onRollOut = function() {
			this.col.setRGB(ref.adconfig['textcolor_description']);
		};
		clip.ovl.cls.onRelease = function() { ref.hideMidroll(); };
		clip.ovl.abt.tf.text = adconfig['about_txt'];
		clip.ovl.abt.col = new Color(clip.ovl.abt);
		clip.ovl.abt.col.setRGB(adconfig['textcolor_description']);
		clip.ovl.abt.onRollOver = function() {
			this.col.setRGB(ref.adconfig['mouseover_extras']);
		};
		clip.ovl.abt.onRollOut = function() {
			this.col.setRGB(ref.adconfig['textcolor_description']);
		};
		clip.ovl.abt.onRelease = function() {
			getURL(ref.adconfig['about_url'],ref.config['linktarget']);
		};
		clip.ovl.prv.col = new Color(clip.ovl.prv);
		clip.ovl.prv.col.setRGB(adconfig['textcolor_description']);
		clip.ovl.prv.onRollOver = function() {
			this.col.setRGB(ref.adconfig['mouseover_extras']);
		};
		clip.ovl.prv.onRollOut = function() {
			this.col.setRGB(ref.adconfig['textcolor_description']);
		};
		clip.ovl.prv.onRelease = function() {
			if(ref.currentAd == 0) {
				ref.setAd(ref.advertisements.length-1,true);
			} else {
				ref.setAd(ref.currentAd-1,true);
			}
		};
		clip.ovl.nxt.col = new Color(clip.ovl.nxt);
		clip.ovl.nxt.col.setRGB(adconfig['textcolor_description']);
		clip.ovl.nxt.onRollOver = function() {
			this.col.setRGB(ref.adconfig['mouseover_extras']);
		};
		clip.ovl.nxt.onRollOut = function() {
			this.col.setRGB(ref.adconfig['textcolor_description']);
		};
		clip.ovl.nxt.onRelease = function() {
			if(ref.currentAd == ref.advertisements.length-1) {
				ref.setAd(0,true);
			} else {
				ref.setAd(ref.currentAd+1,true);
			}
		};
		clip.ovl.img.col = new Color(clip.ovl.img.lne);
		clip.ovl.img.col.setRGB(adconfig['textcolor_description']);
		clip.ovl.tit.col = new Color(clip.ovl.tit);
		clip.ovl.tit.col.setRGB(adconfig['textcolor_title']);
		clip.ovl.tit.tf.autoSize = "left";
		clip.ovl.dsc.col = new Color(clip.ovl.dsc);
		clip.ovl.dsc.col.setRGB(adconfig['textcolor_description']);
		clip.ovl.dsc.tf.autoSize = "left";
		clip.ovl.lnk.col = new Color(clip.ovl.lnk);
		clip.ovl.lnk.col.setRGB(adconfig['textcolor_link']);
		clip.ovl.lnk.tf.textColor = adconfig['textcolor_link'];
		clip.ovl.lnk.tf.autoSize = "left";
		clip.ovl.hit.onRollOver = function() { ref.overAd(); };
		clip.ovl.hit.onRollOut = function() { ref.outAd(); };
		clip.ovl.hit.onRelease = function() { ref.visitAd(); };
	};


	/** Setup dimensions of the players. **/
	function setDimensions() {
		var stw = config['displaywidth'];
		var sth = config['displayheight'];
		if(Stage["displayState"] == "fullScreen") {
			stw = Stage.width;
			sth = Stage.height;
		}
		clip._y = sth-70;
		clip.btn._x = stw-45;
		if(clip.btn._y < 48) { clip.btn._y = 48 - sth; }
		clip.msk._width = stw;
		clip.ovl.bck._width = stw;
		clip.ovl.lne._width = stw;
		clip.ovl.hit._width = stw-20;
		clip.ovl.cls._x = stw-60;
		clip.ovl.abt._x = stw-145;
		clip.ovl.prv._x = stw-26;
		clip.ovl.nxt._x = stw;
	};


	/** Show the midroll **/
	function showMidroll(man:Boolean) {
		clip._visible = true;
		clip.ovl._y = 70;
		Animations.easeTo(clip.btn,clip.btn._x,48-Stage.height);
		Animations.easeTo(clip.ovl,0,0);
		interval = setInterval(this,'setAd',200,currentAd,man);
	};


	/** Show the midroll **/
	function hideMidroll() {
		clearInterval(interval);
		Animations.easeTo(clip.btn,clip.btn._x,48);
		Animations.easeTo(clip.ovl,0,70);
		clip.ovl.tit.tf.text = "";
		clip.ovl.dsc.tf.text = "";
		clip.ovl.lnk.tf.text = "";
		clip.ovl.img.img._alpha = 0;
	};


	/** Roll over the ad **/
	private function overAd() {
		clip.ovl.img.col.setRGB(adconfig['mouseover_color']);
		clip.ovl.tit.col.setRGB(adconfig['mouseover_color']);
		clip.ovl.dsc.col.setRGB(adconfig['mouseover_color']);
		clip.ovl.lnk.col.setRGB(adconfig['mouseover_color']);
	};


	/** Roll over the ad **/
	private function outAd() {
		clip.ovl.img.col.setRGB(adconfig['textcolor_description']);
		clip.ovl.tit.col.setRGB(adconfig['textcolor_title']);
		clip.ovl.dsc.col.setRGB(adconfig['textcolor_description']);
		clip.ovl.lnk.col.setRGB(adconfig['textcolor_link']);
	};


	/** Jump to the ad url **/
	private function visitAd() {
		outAd();
		if(currentState > 0) { sendEvent('playpause'); }
		getURL(advertisements[currentAd]['click_url'],'_blank');
	};


	/** Change the height to reflect the volume **/
	private function setTime(elp:Number) {
		if(elp > adconfig['initial_delay'] && currentAd == undefined) {
			currentAd = 0;
			showMidroll();
		}
	};


	/** Set a specific ad in the midroll **/
	private function setAd(idx:Number,man:Boolean) {
		if(advertisements[idx]['image'].length > 10) { 
			clip.ovl.tit._x = clip.ovl.dsc._x = clip.ovl.lnk._x = 68;
			clip.ovl.img._visible = true;
			loader.loadImage(advertisements[idx]['image']);
			clip.ovl.dsc.tf._width = clip.ovl.bck._width - 120;
		} else {
			clip.ovl.tit._x = clip.ovl.dsc._x = clip.ovl.lnk._x = 8;
			clip.ovl.img.img._alpha = 0;
			clip.ovl.img._visible = false;
			clip.ovl.dsc.tf._width = clip.ovl.bck._width - 60;
		}
		var num = Math.round((clip.ovl.bck._width - clip.ovl.dsc._x)/6);
		var dsc = StringMagic.chopString(advertisements[idx]['description'],num,1);
		if( dsc != advertisements[idx]['description']) { dsc += ' ..'; }
		Animations.easeText(clip.ovl.tit,advertisements[idx]['title']);
		Animations.easeText(clip.ovl.dsc,dsc);
		Animations.easeText(clip.ovl.lnk,advertisements[idx]['display_url']);
		currentAd = idx;
		clearInterval(interval);
		if (rotated == true && man != true) {
			rotated = false;
			hideMidroll();
			idx = 0;
			return;
		} else if(currentAd == advertisements.length-1) {
			if (man != true) {
				rotated = true;
			}
			idx = 0;
		} else {
			idx++; 
		}
		interval = setInterval(this,'setAd',adconfig['display_duration']*1000,idx);
	}


	/** Only display the eq if a song is playing **/
	private function setState(stt:Number) { 
		currentState = stt;
		if(stt == 3) { 
			hideMidroll();
		}
	};


	/** Catches stage resizing **/
	public function onResize() { setDimensions(); };


	/** Catches fullscreen escape  **/
	public function onFullScreen(fs:Boolean) {
		if(fs == false) { setDimensions(); }
	};


};