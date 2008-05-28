/**
* Class for loading, scaling and smoothing images to a given MovieClip.
*
* @example
* var myLoader = new ImageLoader(this,"true",400,300);
* myLoader.loadImage("somephoto.jpg");
* 
* @author	Jeroen Wijering
* @version	1.11
**/


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
	/** Interval for SWF meta checking **/
	private var metaInt:Number;


	/** Constructor for the ImageLoader **/
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
		if(useSmoothing  == 'true') {
			var bmp = new flash.display.BitmapData(targetClip.mc._width,
				targetClip.mc._height, true, 0x000000);
			bmp.draw(targetClip.mc);
			var bmc:MovieClip = targetClip.createEmptyMovieClip("smc",
				targetClip.getNextHighestDepth());
			bmc.attachBitmap(bmp, bmc.getNextHighestDepth(),"auto",true);
			targetClip.mc.unloadMovie();
			targetClip.mc.removeMovieClip();
			delete targetClip.mc;
			scaleImage(targetClip.smc);
			onLoadFinished();
		} else {
			targetClip.mc.forceSmoothing = true;
			if(sourceURL.toLowerCase().indexOf(".swf") == -1) {
				scaleImage(targetClip.mc);
			}
			onLoadFinished();
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
		var xsr = targetWidth/sourceWidth;
		var ysr = targetHeight/sourceHeight;
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


	/** Start loading an image. **/
	public function loadImage(img:String):Void {
		sourceURL = img;
		targetClip.mc.clear();
		targetClip.smc.unloadMovie();
		targetClip.smc.removeMovieClip();
		delete targetClip.smc;
		checkSmoothing(img);
		var raw:MovieClip = targetClip.createEmptyMovieClip("mc",1);
		mcLoader.loadClip(img,raw);
		if(img.toLowerCase().indexOf(".swf") > -1) {
			metaInt = setInterval(this,"setSWFMeta",200);
		}
	};


	/** Check whether smoothing can be enabled. **/
	private function checkSmoothing(img:String):Void {
		var idx:Number = _root._url.indexOf("/",8);
		var rot:String = _root._url.substring(0,idx);
		if(System.capabilities.version.indexOf("7,0,") > -1 ||
			img.toLowerCase().indexOf(".swf") > -1 || 
			_root._url.indexOf("file://") > -1 || 
			(img.indexOf(rot) == -1 && img.indexOf('http://') == 0)) {
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


	/** Event handler; invoked when loading. **/
	public function onLoadProgress(tgt:MovieClip,btl:Number,btt:Number) {};


	/** Event handler; invoked when image is completely loaded. **/
	public function onLoadFinished() {};


	/** Event handler; invoked when metadata is received. **/
	public function onMetaData() {};


}