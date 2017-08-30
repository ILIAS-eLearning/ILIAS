package com.yahoo.astra.fl.charts
{
	/**
	 * A type of chart that displays its data in categories.
	 * 
	 * @author Josh Tynjala
	 */
	public interface ICategoryChart
	{
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
		
		/**
		 * The names of the categories displayed on the category axis. If the
		 * chart does not have a category axis, this value will be ignored.
		 */
		function get categoryNames():Array
		
		/**
		 * @private
		 */
		function set categoryNames(value:Array):void
	}
}