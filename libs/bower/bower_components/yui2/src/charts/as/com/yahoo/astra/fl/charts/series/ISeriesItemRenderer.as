package com.yahoo.astra.fl.charts.series
{
	import flash.events.IEventDispatcher;
	
	/**
	 * A renderer for an item in a series on a chart.
	 * 
	 * <p>Important: Must be a subclass of <code>DisplayObject</code></p>
	 * 
	 * @see flash.display.DisplayObject
	 * @author Josh Tynjala
	 */
	public interface ISeriesItemRenderer extends IEventDispatcher
	{
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * The data provider for the item that this item renderer represents.
		 * Custom implementations of <code>ISeriesItemRenderer</code>
		 * may use this property to render additional information for
		 * the user.
		 */
		function get data():Object;
		
		/**
		 * @private
		 */
		function set data(value:Object):void;	
		
		/**
		 * The series data that is displayed by this renderer.
		 */
		function get series():ISeries;
		
		/**
		 * @private
		 */
		function set series(value:ISeries):void;
	}
}