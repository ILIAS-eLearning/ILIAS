/**
* A couple of commonly used draw functions.
*
* @author	Jeroen Wijering
* @version	1.1
**/


class com.jeroenwijering.utils.Draw {


	/**
	* Draw a square in a given movieclip.
	*
	* @param tgt	Movieclip to draw the square into.
	* @param wth	Square width.
	* @param hei	Square height.
	* @param clr	Square color.
	* @param tck	(optional) Stroke Thickness.
	* @param cls	(optional) Stroke color.
	**/
	public static function square(tgt:MovieClip,wth:Number,hei:Number,
		clr:Number,tck:Number,cls:Number):Void {
		tgt.clear();
		if(tck != undefined) { tgt.lineStyle(tck,cls,100); }
		tgt.beginFill(clr,100);
		tgt.moveTo(0,0);
		tgt.lineTo(wth,0);
		tgt.lineTo(wth,hei);
		tgt.lineTo(0,hei);
		tgt.lineTo(0,0);
		tgt.endFill();
	};


	/**
	* Draw a rounded-corner square in a given movieclip.
	*
	* @param tgt	Movieclip to draw the square into.
	* @param wth	Square width.
	* @param hei	Square height.
	* @param rad	Square corner radius.
	* @param clr	Square color.
	* @param tck	(optional) Stroke Thickness.
	* @param cls	(optional) Stroke color.
	* @param xof	(optional) X offset value.
	* @param yof	(optional) Y offset value.
	* @param alp	(optional) fill alpha value.
	**/
	public static function roundedSquare(tgt:MovieClip,wth:Number,hei:Number,
		rad:Number,clr:Number,tck:Number,cls:Number,
		xof:Number,yof:Number,alp:Number):Void {
		tgt.clear();
		if(tck > 0) { tgt.lineStyle(tck,cls,100); }
		if(xof == undefined) { xof = yof = 0; }
		if(alp == undefined) { alp = 100; }
		tgt.beginFill(clr,alp);
		tgt.moveTo(rad+xof,yof);
		tgt.lineTo(wth-rad+xof,yof);
		tgt.curveTo(wth+xof,yof,wth+xof,rad+yof);
		tgt.lineTo(wth+xof,hei-rad+yof);
		tgt.curveTo(wth+xof,hei+yof,wth-rad+xof,hei+yof);
		tgt.lineTo(rad+xof,hei+yof);
		tgt.curveTo(xof,hei+yof,xof,hei-rad+yof);
		tgt.lineTo(xof,rad+yof);
		tgt.curveTo(xof,yof,rad+xof,yof);
		tgt.endFill();
	};


}