/**
* Overlay banner management of the mediaplayer MCV pattern.
*
* @author	Jeroen Wijering
* @version	1.0
**/


import com.jeroenwijering.players.*;
import com.jeroenwijering.utils.*;


class com.jeroenwijering.players.OverlayView extends AbstractView { 


	/** link to the banner MC **/
	private var overlay:MovieClip;
	/** Imageloader **/
	private var loader:ImageLoader;
	/** flag for display of the banner **/
	private var state:Number = 0;


	/** Constructor, loads caption file. **/
	function OverlayView(ctr:AbstractController,cfg:Object,fed:Object) {
		super(ctr,cfg,fed);
		var ref = this;
		overlay = config['clip'].overlay;
		overlay._alpha = 0;
		overlay.icn.swapDepths(2);
		overlay.icn.onPress = function() {
			Animations.fadeOut(ref.overlay,0);
			ref.state = 3;
		};
		overlay.createEmptyMovieClip("img",1);
		loader = new ImageLoader(overlay.img,"none");
		loader.onLoadFinished = function() { ref.setDimensions(); };
		Stage.addListener(this);
	};


	/** place and scale the overlay correctly **/
	private function setDimensions() {
		overlay.icn._x = 0;
		overlay.img.mc.gotoAndPlay(1);
		if(Stage["displayState"] == "fullScreen") {
			overlay._xscale = overlay._yscale = 200;
			overlay._x = Stage.width/2 - overlay._width/2;
			overlay._y = Stage.height - overlay._height - 50;
			overlay.icn._x = overlay._width/2 - 20;
		} else {
			overlay._xscale = overlay._yscale = 100;
			overlay._x = config['displaywidth']/2 - overlay._width/2;
			overlay._y = config['displayheight'] - overlay._height - 10;
			overlay.icn._x = overlay._width - 20;
		}
	}


	/** Check for overlay **/
	private function setItem(itm:Number) {
		if(feeder.feed[itm]['overlayfile'] != undefined) {
			loader.loadImage(feeder.feed[itm]['overlayfile']);
			var lnk = feeder.feed[itm]['overlaylink'];
			var tgt = config["linktarget"];
			overlay.img.onPress = function() { getURL(lnk,tgt); };
			state = 1;
		} else {
			overlay._visible = false;
			state = 0;
		}
	};


	/** load or unload overlay **/
	private function setTime(elp:Number,rem:Number) {
		if(elp > 2 && state == 1) {
			state = 2;
			overlay.img.mc.gotoAndPlay(1);
			Animations.fadeIn(overlay,100);
		} else if (rem < 2 && state == 2) {
			Animations.fadeOut(overlay,0);
			state = 3;
		}
	}


	/** reset the overlay when the movie is finished **/
	private function setState(stt:Number) {
		if(stt == 3 && state == 3) {
			state = 1;
		}
	}


	/** OnResize Handler: catches stage resizing **/
	public function onResize() { setDimensions(); };


	/** Catches fullscreen escape  **/
	public function onFullScreen(fs:Boolean) { 
		if(fs == false) { setDimensions(); }
	};


}