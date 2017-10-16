package com.yahoo.astra.fl.charts.axes
{
	/**
	 * Positioning and other data used by an IAxisRenderer to draw
	 * items like ticks. This data is created by an IAxis instance.
	 * 
	 * @author Josh Tynjala
	 * @see IAxis
	 * @see IAxisRenderer
	 */
	public class AxisData
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
		
		/**
		 * Constructor.
		 */
		public function AxisData(position:Number, value:Object, label:String)
		{
			this.position = position;
			this.value = value;
			this.label = label;
		}

	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * The position of the item on the axis renderer.
		 */
		public var position:Number;
		
		/**
		 * The value of the item.
		 */
		public var value:Object;
		
		/**
		 * The label value of the item.
		 */
		public var label:String;
	}
}