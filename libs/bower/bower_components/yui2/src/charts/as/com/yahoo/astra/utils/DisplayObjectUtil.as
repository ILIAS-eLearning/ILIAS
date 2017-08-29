package com.yahoo.astra.utils
{
	import flash.display.DisplayObject;
	import flash.geom.Point;
	import flash.geom.Rectangle;
	
	/**
	 * Utility functions for use with DisplayObjects.
	 * 
	 * @author Josh Tynjala
	 */
	public class DisplayObjectUtil
	{
		/**
		 * Converts a point from the local coordinate system of one DisplayObject to
		 * the local coordinate system of another DisplayObject.
		 *
		 * @param point					the point to convert
		 * @param firstDisplayObject	the original coordinate system
		 * @param secondDisplayObject	the new coordinate system
		 */
		public static function localToLocal(point:Point, firstDisplayObject:DisplayObject, secondDisplayObject:DisplayObject):Point
		{
			point = firstDisplayObject.localToGlobal(point);
			return secondDisplayObject.globalToLocal(point);
		}
	
		/**
		 * Aligns a DisplayObject vertically and horizontally within specific bounds.
		 * 
		 * @param target			The DisplayObject to align.
		 * @param bounds			The rectangle in which to align the target DisplayObject.
		 * @param horizontalAlign	The alignment position along the horizontal axis. If <code>null</code>,
		 * 							the target's horizontal position will not change.
		 * @param verticalAlign		The alignment position along the vertical axis. If <code>null</code>,
		 * 							the target's vertical position will not change.
		 */
		public static function align(target:DisplayObject, bounds:Rectangle, horizontalAlign:String = null, verticalAlign:String = null):void
		{	
			var horizontalDifference:Number = bounds.width - target.width;
			switch(horizontalAlign)
			{
				case "left":
					target.x = bounds.x;
					break;
				case "center":
					target.x = bounds.x + (horizontalDifference) / 2;
					break;
				case "right":
					target.x = bounds.x + horizontalDifference;
					break;
			}
					
			var verticalDifference:Number = bounds.height - target.height;
			switch(verticalAlign)
			{
				case "top":
					target.y = bounds.y;
					break;
				case "middle":
					target.y = bounds.y + (verticalDifference) / 2;
					break;
				case "bottom":
					target.y = bounds.y + verticalDifference;
					break;
			}
		}
		
		/**
		 * Resizes a DisplayObject to fit into specified bounds such that the
		 * aspect ratio of the target's width and height does not change.
		 * 
		 * @param target		The DisplayObject to resize.
		 * @param width			The desired width for the target.
		 * @param height		The desired height for the target.
		 * @param aspectRatio	The desired aspect ratio. If NaN, the aspect
		 * 						ratio is calculated from the target's current
		 * 						width and height.
		 */
		public static function resizeAndMaintainAspectRatio(target:DisplayObject, width:Number, height:Number, aspectRatio:Number = NaN):void
		{
			var currentAspectRatio:Number = !isNaN(aspectRatio) ? aspectRatio : target.width / target.height;
			var boundsAspectRatio:Number = width / height;
			
			if(currentAspectRatio < boundsAspectRatio)
			{
				target.width = Math.floor(height * currentAspectRatio);
				target.height = height;
			}
			else
			{
				target.width = width;
				target.height = Math.floor(width / currentAspectRatio);
			}
		}
	}
}