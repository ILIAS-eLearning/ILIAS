/**
* Manages scrolling of a designated MovieClip, automatic or with scrollbar.
*
* @example
* var myScroller = new com.jeroenwijering.utils.Scroller(myMovie,myMask);
* myscroller.scrollTo(200);
*
* @author	Jeroen Wijering
* @version	1.8
**/


import com.jeroenwijering.utils.Animations;


class com.jeroenwijering.utils.Scroller {


	/** Movieclip that should be scrolled **/
	private var targetClip:MovieClip;
	/** Mask of the movieclip **/
	private var maskClip:MovieClip;
	/** Use automatic scroling, defaults to false **/
	private var autoScroll:Boolean = false;
	/** scrollbar front color **/
	private var frontColor:Number = 0x000000;
	/** scrollbar highlighting color **/
	private var lightColor:Number = 0x000000;
	/** size ratio clip:mask **/
	private var sizeRatio:Number;
	/** scroll interval id for autoscroller and dragging of scrollbar **/
	private var scrollInterval:Number;
	/** corrent scroll index **/
	private var currentScroll:Number = 0;
	/** autoscroll multiplier **/
	private var AUTOSCROLL_SPEED:Number = 0.25;
	/** Movieclip the scrollbar is drawn into **/
	private var SCROLLER_CLIP:MovieClip;
	/** Color object of the scrollbar back **/
	private var SCROLLER_BACK_COLOR:Color;
	/** Color object of the scrollbar front **/
	private var SCROLLER_FRONT_COLOR:Color;


	/** Sets up scrolling behaviour and scrollbar **/
	function Scroller(tgt:MovieClip,msk:MovieClip,asc:Boolean,
		fcl:Number,hcl:Number) {
		targetClip = tgt;
		maskClip = msk;
		arguments.length > 2 ? autoScroll = asc: null;
		arguments.length > 3 ? frontColor = fcl: null;
		arguments.length > 4 ? lightColor = hcl: null;
		sizeRatio = maskClip._height/targetClip._height;
		if(autoScroll == false) {
			drawScrollbar();
		} else {
			scrollInterval = setInterval(this,"doAutoscroll",50);
		}
		if(System.capabilities.os.toLowerCase().indexOf("mac") == -1) {
			Mouse.addListener(this);
		}
	};


	/** Draw the scrollbar. **/
	private function drawScrollbar() {
		targetClip._parent.createEmptyMovieClip("scrollbar",
			targetClip._parent.getNextHighestDepth());
		SCROLLER_CLIP = targetClip._parent.scrollbar;
		SCROLLER_CLIP._x = maskClip._x+maskClip._width - 1;
		SCROLLER_CLIP._y = maskClip._y+3;
		SCROLLER_CLIP.createEmptyMovieClip("back",0);
		SCROLLER_CLIP.back._alpha = 0;
		SCROLLER_CLIP.back._y = -3;
		drawSquare(SCROLLER_CLIP.back,12,maskClip._height,frontColor);
		SCROLLER_CLIP.createEmptyMovieClip("bar",1);
		SCROLLER_CLIP.bar._x = 4;
		SCROLLER_CLIP.bar._alpha = 50;
		drawSquare(SCROLLER_CLIP.bar,4,maskClip._height-5,frontColor);
		SCROLLER_CLIP.createEmptyMovieClip("front",2);
		SCROLLER_CLIP.front._x = 3;
		drawSquare(SCROLLER_CLIP.front,6,
			SCROLLER_CLIP.bar._height*sizeRatio,frontColor);
		SCROLLER_CLIP.front.createEmptyMovieClip("bg",1);
		SCROLLER_CLIP.front.bg._x = -3;
		SCROLLER_CLIP.front.bg._alpha = 0;
		drawSquare(SCROLLER_CLIP.front.bg,12,
			SCROLLER_CLIP.front._height,frontColor);
		SCROLLER_FRONT_COLOR = new Color(SCROLLER_CLIP.front);
		setScrollbarEvents();
	};


	/** Set use of mousewheel to scroll playlist. **/
	public function onMouseWheel(dta:Number) { 
		scrollTo(currentScroll-dta*20); 
	};


	/** Set autoscroll events. **/
	private function doAutoscroll() {
		if (maskClip._xmouse>0 && maskClip._xmouse<maskClip._width/
			(maskClip._xscale/100) && maskClip._ymouse>0 && 
			maskClip._ymouse<maskClip._height/(maskClip._yscale/100)) {
			var dif:Number = 
				maskClip._ymouse*(maskClip._yscale/100)-maskClip._height/2;
			scrollTo(currentScroll+Math.floor(dif*AUTOSCROLL_SPEED));
		}
	};


	/** All scrollbar mouse events grouped together. **/
	private function setScrollbarEvents():Void {
		var instance:Scroller = this;
		SCROLLER_CLIP.front.onRollOver = 
			SCROLLER_CLIP.back.onRollOver = function() {
			instance.SCROLLER_FRONT_COLOR.setRGB(instance.lightColor);
		};
		SCROLLER_CLIP.front.onRollOut = 
			SCROLLER_CLIP.back.onRollOut = function() {
			instance.SCROLLER_FRONT_COLOR.setRGB(instance.frontColor);
		};
		SCROLLER_CLIP.back.onRelease = function() { 
			if(this._ymouse > this._parent.front._y + 
				this._parent.front._height) { 
				instance.scrollTo(instance.currentScroll + 
					instance.maskClip._height/2); 
			} else if (this._ymouse < this._parent.front._y) { 
				instance.scrollTo(instance.currentScroll - 
					instance.maskClip._height/2); 
			}
		};
		SCROLLER_CLIP.front.onPress = function() { 
			this.startDrag(false,3,0,3,instance.SCROLLER_CLIP.bar._height - 
				this._height);
			instance.scrollInterval = setInterval(instance,"scrollTo",100);
		};
		SCROLLER_CLIP.front.onRelease = 
			SCROLLER_CLIP.front.onReleaseOutside = function() { 
			this.stopDrag();
			clearInterval(instance.scrollInterval);
		};
		scrollTo(maskClip._y - targetClip._y);
	};


	/** Scroll the MovieClip to a given Y position. **/
	public function scrollTo(yps:Number):Void {
		if(arguments.length == 0 && autoScroll == false) {
			yps = SCROLLER_CLIP.front._y*maskClip._height / 
				SCROLLER_CLIP.front._height;
		}
		if(yps<5) {
			yps=0;
		} else if (yps>targetClip._height-maskClip._height-5) {
			yps = targetClip._height - maskClip._height;
		}
		Animations.easeTo(targetClip,targetClip._x,maskClip._y - yps);
		SCROLLER_CLIP.front._y = yps*SCROLLER_CLIP.front._height / 
			maskClip._height;
		currentScroll = yps;
	};


	/** Remove the scrollbar from stage **/
	public function purgeScrollbar() {
		clearInterval(scrollInterval);
		Mouse.removeListener(this);
		scrollTo(0);
		SCROLLER_CLIP.removeMovieClip();
	};


	/** Draw a square in a given movieclip. **/
	private function drawSquare(tgt:MovieClip,wth:Number,hei:Number,
		clr:Number) {
		tgt.clear();
		tgt.beginFill(clr,100);
		tgt.moveTo(0,0);
		tgt.lineTo(wth,0);
		tgt.lineTo(wth,hei);
		tgt.lineTo(0,hei);
		tgt.lineTo(0,0);
		tgt.endFill();
	};


}