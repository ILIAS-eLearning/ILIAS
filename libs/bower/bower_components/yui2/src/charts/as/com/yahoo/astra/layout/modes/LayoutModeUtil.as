package com.yahoo.astra.layout.modes
{
	import flash.display.DisplayObject;
	import flash.geom.Rectangle;
	
	/**
	 * Utility functions shared by implementations of ILayoutMode.
	 * 
	 * @author Josh Tynjala
	 * @see ILayoutMode
	 */
	public class LayoutModeUtil
	{
		
	//--------------------------------------
	//  Static Methods
	//--------------------------------------
	
		/**
		 * Calculates the rectangular bounds occupied by the target's children.
		 * 
		 * @param children		The set of children to use to calculate the maximum bounds
		 */
		public static function calculateChildBounds(children:Array):Rectangle
		{
			var minX:Number = 0;
			var maxX:Number = 0;
			var minY:Number = 0;
			var maxY:Number = 0;
			var childCount:int = children.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(children[i]);
				var childMaxX:Number = child.x + child.width;
				var childMaxY:Number = child.y + child.height;
				minX = Math.min(minX, child.x);
				minY = Math.min(minY, child.y);
				maxX = Math.max(maxX, childMaxX);
				maxY = Math.max(maxY, childMaxY);
			}
			
			return new Rectangle(minX, minY, maxX - minX, maxY - minY);
		}

	}
}