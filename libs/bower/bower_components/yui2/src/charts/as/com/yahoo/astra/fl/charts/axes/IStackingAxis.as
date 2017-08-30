package com.yahoo.astra.fl.charts.axes
{
	import com.yahoo.astra.fl.charts.series.IStackedSeries;
	
	/**
	 * A type of axis that allows values to be stacked.
	 * 
	 * @author Josh Tynjala
	 */
	public interface IStackingAxis extends IAxis
	{
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
		
		/**
		 * If true, the axis will allow the stacking of series that implement
		 * the interface IStackedSeries.
		 * 
		 * <p>Must be explicitly enabled.
		 * 
		 * @see com.yahoo.astra.fl.charts.series.IStackedSeries
		 */
		function get stackingEnabled():Boolean;
		
		/**
		 * @private
		 */
		function set stackingEnabled(value:Boolean):void;
		
	//--------------------------------------
	//  Methods
	//--------------------------------------
	
		/**
		 * Calculates the sum of values if they were stacked on the axis.
		 * The first value is important because some axis types, such as
		 * NumericAxis, may differentiate between positive and negative values.
		 * 
		 * @see NumericAxis
		 */
		function stack(top:Object, ...rest:Array):Object;
	}
}