/**
* Class for loading, scaling and smoothing images to a given MovieClip.
*
* @example 
* import com.jeroenwijering.utils.ImageLoader;
* var myLoader = new ImageLoader(this);
* myLoader.loadImage("somephoto.jpg");
* 
* @author	Jeroen Wijering
* @version	1.10
**/


import com.jeroenwijering.utils.*;


class com.jeroenwijering.utils.ImageLoader {


	/** MovieClip Loader Instance **/
	private var mcLoader:MovieClipLoader;
	/** Target MovieClip **/
	private var targetClip:MovieClip;
	/** Target Width **/
	private var targetWidth:Number;
	/** Target Height **/
	private var targetHeight:Number;
	/** Source URL **/
	private var sourceURL:String;
	/** Source Width **/
	private var sourceWidth:Number;
	/** Source Height **/
	private var sourceHeight:Number;
	/** Source Length (for SWF) **/
	private var sourceLength:Number;
	/** Overstretch Boolean **/
	private var overStretch:String = "none";
	/** Boolean that checks whether an SWF is loaded **/
	private var useSmoothing:Boolean;
	/** Color of a solid background the BitmapArray might detect **/
	private var backColor:String;
	/** Interval for SWF meta checking **/
	private var metaInt:Number;


	/**
	 * Constructor for the ImageLoader
	 *
	 * @param tgt	MovieClip to load the image into
	 * @param ost	Overstretch parameter (true/false/fit/none)
	 * @param wid	Width of the image target, defaults to target MC width
	 * @param hei	Height if the image target, defaults to target MC height
	 */
	function ImageLoader(tgt:MovieClip,ost:String,wid:Number,hei:Number) {
		targetClip = tgt;
		arguments.length > 1 ? overStretch = String(ost): null;
		if(arguments.length > 2) { 
			targetWidth = wid;
			targetHeight = hei;
		}
		mcLoader = new MovieClipLoader();
		mcLoader.addListener(this);
	};


	/** Switch image with bitmaparray if possible. **/
	public function onLoadInit(inTarget:MovieClip):Void {
		if(useSmoothing  == true) {
			var bmp = new flash.display.BitmapData(targetClip.mc._width,
				targetClip.mc._height, true, 0x000000);
			bmp.draw(targetClip.mc);
			if(overStretch == "false") { fillBackColor(bmp); }
			var bmc:MovieClip = targetClip.createEmptyMovieClip("smc",
				targetClip.getNextHighestDepth());
			bmc.attachBitmap(bmp, bmc.getNextHighestDepth(),"auto",true);
			targetClip.mc.unloadMovie();
			targetClip.mc.removeMovieClip();
			delete targetClip.mc;
			scaleImage(targetClip.smc);
			onLoadFinished();
		} else {
			if(sourceURL.toLowerCase().indexOf(".swf") == -1) {
				scaleImage(targetClip.mc);
			}
			onLoadFinished();
		}
	};


	/* Fill the stage with a solid backcolor if that matches the image. **/
	private function fillBackColor(bmp) { 
		var ltp="0x"+bmp.getPixel(0,0).toString(16);
		var brp="0x"+bmp.getPixel(bmp.width-1,bmp.height-1).toString(16);
		if(ltp == brp) {
			backColor = ltp;
			targetClip.createEmptyMovieClip("bck",0);
			targetClip.bck.beginFill(backColor,100);
			targetClip.bck.moveTo(0,0);
			targetClip.bck.lineTo(targetWidth,0);
			targetClip.bck.lineTo(targetWidth,targetHeight);
			targetClip.bck.lineTo(0,targetHeight);
			targetClip.bck.lineTo(0,0);
			targetClip.bck.endFill();
		} else {
			delete backColor;
		}
	};


	/** Scale the image while maintaining aspectratio **/
	private function scaleImage(tgt:MovieClip):Void {
		targetClip._xscale = targetClip._yscale = 100;
		var tcf = tgt._currentframe;
		tgt.gotoAndStop(1);
		sourceWidth = tgt._width;
		sourceHeight = tgt._height;
		sourceLength = tgt._totalframes/20;
		var xsr:Number = targetWidth/sourceWidth;
		var ysr:Number = targetHeight/sourceHeight;
		if (overStretch == "fit" || Math.abs(xsr-ysr) < 0.1) {
			tgt._width = targetWidth;
			tgt._height = targetHeight;
		} else if ((overStretch == "true" && xsr > ysr) || 
			(overStretch == "false" && xsr < ysr)) { 
			tgt._xscale = tgt._yscale = xsr*100;
		} else if(overStretch == "none") {
			tgt._xscale = tgt._yscale = 100;
		} else { 
			tgt._xscale = tgt._yscale = ysr*100;
		}
		if(targetWidth != undefined) {
			tgt._x = targetWidth/2 - tgt._width/2;
			tgt._y = targetHeight/2 - tgt._height/2;
		}
		tgt.gotoAndPlay(tcf);
		onMetaData();
	};


	/**
	 * Start loading an image.
	 *
	 * @param img	URL of the image to load.
	 */
	public function loadImage(img:String):Void {
		sourceURL = img;
		targetClip.mc.clear();
		targetClip.smc.unloadMovie();
		targetClip.smc.removeMovieClip();
		delete targetClip.smc;
		checkSmoothing(img);
		var raw:MovieClip = targetClip.createEmptyMovieClip("mc",1);
		mcLoader.loadClip(img,raw);
		if(backColor != undefined) {
			targetClip.bck.removeMovieClip();
		}
		if(img.toLowerCase().indexOf(".swf") > -1) {
			metaInt = setInterval(this,"setSWFMeta",200);
		}
	};


	/** Check whether smoothing can be enabled. **/
	private function checkSmoothing(img:String):Void {
		var idx:Number = _root._url.indexOf("/",8);
		var rot:String = _root._url.substring(0,idx);
		if(img.toLowerCase().indexOf(".swf") > -1 || 
			_root._url.indexOf("file://") > -1) {
			useSmoothing = false;
		} else  if (img.indexOf("http://") > -1 && img.indexOf(rot) == -1) {
			useSmoothing = false;
		} else  if (System.capabilities.version.indexOf("7,0,") > -1 ||
			img.indexOf("enclosure") > -1) {
			useSmoothing = false;
		} else {
			useSmoothing = true;
		}
	};


	/** Check when to set the SWF metadata **/
	private function setSWFMeta() {
		if(targetClip.mc._currentframe > 0) {
			clearInterval(metaInt);
			scaleImage(targetClip.mc);
		}
	};


	/** Event handler; invoked when loading is in progress. **/
	public function onLoadProgress(tgt:MovieClip,btl:Number,btt:Number) {};


	/** Event handler; invoked when image is loaded. **/
	public function onLoadFinished() { };


	/** Event handler; invoked when metadata is received. **/
	public function onMetaData() { };


}