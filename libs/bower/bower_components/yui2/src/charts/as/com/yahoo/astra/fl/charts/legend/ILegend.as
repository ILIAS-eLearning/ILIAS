package com.yahoo.astra.fl.charts.legend
{
	/**
	 * Properties required by a chart's legend.
	 * 
	 * @see com.yahoo.astra.fl.charts.Chart
	 * 
	 * @author Josh Tynjala
	 */
	public interface ILegend
	{
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * An Array of LegendItemData objects.
		 * 
		 * @see com.yahoo.astra.fl.charts.legend.LegendItemData
		 */
		function get dataProvider():Array
		
		/**
		 * @private
		 */
		function set dataProvider(value:Array):void;

		/**
		 * The maximum available width for the legend.
		 */		
		function get maxWidth():Number;
		
		/** 
		 * @private (setter)
		 */		
		function set maxWidth(value:Number):void;

		/** 
		 * The maximum available height for the legend.
		 */		
		function get maxHeight():Number;
		
		/** 
		 * @private (setter)
		 */		
		function set maxHeight(value:Number):void;		
	}
}