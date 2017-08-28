package com.yahoo.astra.fl.charts.legend
{
	import flash.display.Shape;
	
	/**
	 * @author Josh Tynjala
	 */
	public class LegendItemData
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 * 
		 * @param label			The text to display on the LegendItem.
		 * @param markerSkin	The skin to use with the marker on the LegendItem.
		 * @param fillColor		The base color (possibly) used by the marker.
		 */
		public function LegendItemData(label:String = "", markerSkin:Object = null, fillColor:uint = 0x000000, fillAlpha:Number = 1, borderColor:uint = 0x000000, borderAlpha:Number = 1)
		{
			this.label = label;
			this.markerSkin = markerSkin ? markerSkin : Shape;
			this.fillColor = fillColor;
			this.fillAlpha = fillAlpha;
			this.borderColor = borderColor;
			this.borderAlpha = borderAlpha;
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * The text to display on the LegendItem.
		 */
		public var label:String;
		
		/**
		 * The skin to use for the marker on the LegendItem.
		 */
		public var markerSkin:Object;
		
		/**
		 * The base color (possibly) used by the marker fill.
		 */
		public var fillColor:uint;
		
		/**
		 * The alpha value used by the marker fill.
		 */
		public var fillAlpha:Number;
		
		/**
		 * The base color used by the marker border.
		 */
		public var borderColor:uint;
		
		/**
		 * The alpha value used by the marker border.
		 */
		public var borderAlpha:Number;
	}
	
}
