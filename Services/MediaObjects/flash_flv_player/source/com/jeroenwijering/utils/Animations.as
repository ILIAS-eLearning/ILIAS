/**
* A couple of commonly used animation functions.
*
* @author	Jeroen Wijering
* @version	1.2
**/


class com.jeroenwijering.utils.Animations {


	/**
	* Fadein function for MovieClip.
	*
	* @param tgt	Movieclip to fade.
	* @param end	Final alpha value.
	* @param inc	Speed of the fade (increment per frame).
	**/
	public static function fadeIn(tgt:MovieClip,end:Number,spd:Number):Void {
		arguments.length < 3 ? spd = 20: null;
		arguments.length < 2 ? end = 100: null;
		tgt._visible = true;
		tgt.onEnterFrame = function() {
			if(this._alpha > end-spd) {
				delete this.onEnterFrame;
				this._alpha = end;
			} else {
				this._alpha += spd;
			}
		};
	};


	/**
	* Fadeout function for MovieClip.
	*
	* @param tgt	Movieclip to fade.
	* @param end	Final alpha value.
	* @param inc	Speed of the fade (increment per frame).
	* @param rmv	Remove the clip after fadeout.
	**/
	public static function fadeOut(tgt:MovieClip,end:Number,
		spd:Number,rmv:Boolean):Void {
		arguments.length < 4 ? rmv = false: null;
		arguments.length < 3 ? spd = 20: null;
		arguments.length < 2 ? end = 0: null;
		tgt.onEnterFrame = function() {
			if(this._alpha < end+spd) {
				delete this.onEnterFrame;
				this._alpha = end;
				end == 0 ? this._visible = false: null;
				rmv == true ? this.removeMovieClip(): null;
			} else {
				this._alpha -= spd;
			}
		};
	};


	/** 
	* Crossfade a given MovieClip to/from to 0.
	* 
	* @param tgt	Movieclip to fade.
	* @param alp	Top alpha value. 
	**/
	public static function crossfade(tgt:MovieClip, alp:Number) {
		var phs = "out";
		var pct = alp/5;
		tgt.onEnterFrame = function() {
			if(phs == "out") {
				this._alpha -= pct;
				if (this._alpha < 1) { phs = "in"; }
			} else {
				this._alpha += pct;
				this._alpha >= alp ? delete this.onEnterFrame : null; 
			}
		}; 
	};


	/**
	* Easing enterframe function for a Movieclip.
	*
	* @param tgt	MovieClip of the balloon to iterate
	* @param xps	Final x position.
	* @param yps	Final y position.
	* @param spd	Speed of the ease (1 to 10)
	**/
	public static function easeTo(tgt:MovieClip,xps:Number,yps:Number,
		spd:Number):Void {
		arguments.length < 4 ? spd = 2: null;
		tgt.onEnterFrame = function() {
			this._x = xps-(xps-this._x)/(1+1/spd);
			this._y = yps-(yps-this._y)/(1+1/spd);
			if (this._x>xps-1 && this._x<xps+1 && 
				this._y>yps-1 && this._y<yps+1) {
				this._x = Math.round(xps);
				this._y = Math.round(yps);
				delete this.onEnterFrame;
			} 
		}; 
	};


	/** 
	* Ease typewrite text into a tag after a given delay. 
	*
	* @param tgt	Movieclip to draw the shape into.
	* @param rnd	Random number of frames to wait.
	* @param txt	(optionally) text to write (else the current is used)
	**/
	public static function easeText(tgt:MovieClip,rnd:Number,
		txt:String,spd:Number) {
		if (arguments.length < 3) {
			tgt.str = tgt.tf.text;
			tgt.hstr = tgt.tf.htmlText;
		} else { tgt.str = tgt.hstr = txt; }
		if (arguments.length < 4) { spd = 1.4; }
		tgt.tf.text = "";
		tgt.i = 0;
		tgt.rnd = rnd;
		tgt.onEnterFrame = function() {
			if(this.i > this.rnd) { 
				this.tf.text = this.str.substr(0, this.str.length - 
					Math.floor((this.str.length - this.tf.text.length)/spd));
			}
			if(this.tf.text == this.str) {
				this.tf.htmlText = this.hstr;
				if(this.more != undefined) { this.more._visible = true; }
				delete this.onEnterFrame;
			}
			this.i++;
		};
	};


	/** 
	* Overwrite the contents of a textfield letter by letter with a new value
	*
	* @param tgt	Movieclip to draw the shape into.
	* @param txt	(optionally) text to write (else the current is used)
	**/
	public static function overwrite(tgt:MovieClip,txt:String) {
		var prv = tgt.tf.text;
		var nxt = txt;
		var arr = new Array();
		if (prv.length < nxt.length) {
			var dif = nxt.length-prv.length;
			for (var i=0; i<dif; i++) { 
				prv += " ";
			}
		} else if (nxt.length < prv.length) {
				var dif = prv.length-nxt.length;
			for (var i=0; i<dif; i++) { 
				nxt += " ";
			}
		}
		for (var i=0; i<nxt.length; i++) { arr.push(i); }
		tgt.tf.text = prv;
		tgt.onEnterFrame = function() {
			if (arr.length == 0) {
				delete this.onEnterFrame;
				return;
			}
			var idx = random(arr.length);
			var nmb = arr[idx];
			arr.splice(idx,1);
			this.tf.text = this.tf.text.substr(0,nmb) + 
				nxt.charAt(nmb) + this.tf.text.substr(nmb+1);
		};
	};


	/** 
	* Overwrite the contents of a textfield letter by letter with a new value
	*
	* @param tgt	Movieclip to draw the shape into.
	* @param txt	(optionally) text to write (else the current is used)
	**/
	public static function write(tgt:MovieClip,txt:String) {
		var stg = 0;
		var num = 8;
		tgt.onEnterFrame = function() {
			if(stg == 0) {
				this.tf.text = tgt.tf.text.substr(0,this.tf.text.length-1);
				this.tf.text.length == 0 ? stg = 1: null;
			} else if(stg == 1) {
				this.tf.text == "" ? this.tf.text = "_": this.tf.text = "";
				num--;
				num == 0 ? stg = 2: null;
			} else if (stg == 2) {
				this.tf.text += txt.charAt(tgt.tf.text.length);
				this.tf.text.length == txt.length ? stg = 3: null;
			} else if (stg == 3) {
				if(this.tf.text.charAt(this.tf.text.length-1) == "_") {
					this.tf.text = this.tf.text.substr(0,
						this.tf.text.length-1)+" ";
				} else {
					this.tf.text = this.tf.text.substr(0,
						this.tf.text.length-1)+"_";
				}
				num++;
				num == 8 ? stg = 4: null;
			} else {
				delete this.onEnterFrame;
			}
		};
	};

	/**
	* Make a Movieclip jump to a specific scale
	*
	* @param tgt	Movieclip that should jump.
	* @param scl	Final scale.
	* @param spd	Scaling speed.
	**/
	public static function jump(tgt:MovieClip,scl:Number,spd:Number):Void {
		arguments.length < 2 ? scl = 100: null;
		arguments.length < 3 ? spd = 1: null;
		tgt.onEnterFrame = function() {
			this._xscale = this._yscale = scl-(scl-this._xscale)/(1+1/scl);
			if(this._xscale > scl - 1 && this._xscale < scl + 1) {
				delete this.onEnterFrame;
				this._xscale = this._yscale = scl;
			} 
		};
	};


	/**
	* Transform the color of a MovieClip over time
	*
	* @param tgt	Target MovieClip.
	* @param red	Red channel offset.
	* @param gre	Green channel offset.
	* @param blu	Blue channel offset.
	* @param dur	Duration of the transformation (1 to 100).
	**/
	public static function setColor(tgt:MovieClip,red:Number,gre:Number,
		blu:Number,dur:Number):Void {
		arguments.length < 5 ? dur = 5: null;
		tgt.col = new Color(tgt);
		tgt.cr = tgt.cg = tgt.cb = 0;
		tgt.onEnterFrame = function() {
			this.cr = this.cr+(red-this.cr)/dur;
			this.cg = this.cg+(gre-this.cg)/dur;
			this.cb = this.cb+(blu-this.cb)/dur;
			this.col.setTransform({rb:this.cr, gb:this.cg, bb:this.cb});
			if (Math.abs(this.cr-red)<2 && Math.abs(this.cg-gre)<2 && 
				Math.abs(this.cb-blu)<2) {
				delete this.onEnterFrame;
				this.col.setTransform({rb:red, gb:gre, bb:blu}); 
			}  
		}; 
	};

}