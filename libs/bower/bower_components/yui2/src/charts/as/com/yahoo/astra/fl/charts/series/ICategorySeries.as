package com.yahoo.astra.fl.charts.series
{
	/**
	 * Defines functions and properties for an ISeries that relies on categories.
	 * 
	 * @see com.yahoo.astra.fl.charts.legend.ILegend
	 * @see com.yahoo.astra.fl.charts.legend.LegendItemData
	 * 
	 * @author Josh Tynjala
	 */
	public interface ICategorySeries extends ISeries
	{
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * The field used to access categories for this series.
		 */
		function get categoryField():String
		
		/**
		 * @private
		 */
		function set categoryField(value:String):void;
		
		/**
		 * The names of the categories displayed on the category axis. If the
		 * chart does not have a category axis, this value will be ignored.
		 */
		function get categoryNames():Array;
		
		/**
		 * @private
		 */
		function set categoryNames(value:Array):void
		
	//--------------------------------------
	//  Methods
	//--------------------------------------
	
		/**
		 * Creates an Array of LegendItemData objects to pass to the chart's legend.
		 */
		function createLegendItemData():Array
		
		/**
		 * Determines the category to which the item belongs.
		 */
		function itemToCategory(item:Object, index:int):String;
	}
}