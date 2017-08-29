package com.yahoo.astra.fl.charts.axes
{	
	/**
	 * A visual representation of an IAxis instance.
	 * 
	 * Should be a subclass of UIComponent.
	 * 
	 * @author Josh Tynjala
	 */
	public interface IAxisRenderer
	{
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * The total length of the axis renderer, in pixels.
		 */
		function get length():Number;
		
		/**
		 * An Array of AxisData objects specifying the positions of the ticks.
		 * 
		 * @see AxisData
		 */
		function get ticks():Array;
		
		/**
		 * @private
		 */
		function set ticks(value:Array):void;
		
		/**
		 * An Array of AxisData objects specifying the positions of the minor ticks.
		 * 
		 * @see AxisData
		 */
		function get minorTicks():Array
		
		/**
		 * @private
		 */
		function set minorTicks(value:Array):void;
	}
}