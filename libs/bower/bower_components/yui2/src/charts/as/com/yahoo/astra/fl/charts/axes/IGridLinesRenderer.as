package com.yahoo.astra.fl.charts.axes
{
	/**
	 * A renderer for grid lines appearing on a chart's axis.
	 * 
	 * @author Josh Tynjala
	 */
	public interface IGridLinesRenderer
	{
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * The axis renderer from which the grid lines receive their
		 * major and minor unit data.
		 */
		function get axisRenderer():IAxisRenderer;
		
		/**
		 * @private
		 */
		function set axisRenderer(value:IAxisRenderer):void;
	}
}