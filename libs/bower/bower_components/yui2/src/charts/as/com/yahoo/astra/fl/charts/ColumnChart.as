package com.yahoo.astra.fl.charts
{
	import com.yahoo.astra.fl.charts.series.ColumnSeries;
	
	/**
	 * The amount of space between items within a series
	 * @default 0
	 */
	[Style(name="seriesItemSpacing", type="Number")]
	
	/**
	 * A chart that displays its data points with vertical columns.
	 * 
	 * @author Josh Tynjala
	 */	
	public class ColumnChart extends CartesianChart
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function ColumnChart()
		{
			super();
			this.defaultSeriesType = ColumnSeries;
		}

        /**
         * @private
         *
         * @langversion 3.0
         * @playerversion Flash 9.0.28.0
         */
		private static var defaultStyles:Object = {
			seriesItemSpacing:0
		}
		
		/**
		 * @private
		 * @copy fl.core.UIComponent#getStyleDefinition()
		 */
		public static function getStyleDefinition():Object
		{
			return mergeStyles(defaultStyles, CartesianChart.getStyleDefinition());
		}				
	}
}
