package com.yahoo.astra.fl.charts
{
	import com.yahoo.astra.fl.charts.axes.CategoryAxis;
	import com.yahoo.astra.fl.charts.axes.NumericAxis;
	import com.yahoo.astra.fl.charts.series.BarSeries;
	
	/**
	 * The amount of space between items within a series
	 * @default 0
	 */	
	[Style(name="seriesItemSpacing", type="Number")]
	
	/**
	 * A chart that displays its data points with horizontal bars.
	 * 
	 * @author Josh Tynjala
	 */
	public class BarChart extends CartesianChart
	{
		
	//--------------------------------------
	//  Class Variables
	//--------------------------------------
		
		/**
		 * @private
		 */
		private static var defaultStyles:Object = 
		{	
			showHorizontalAxisGridLines: true,
			showHorizontalAxisTicks: true,
			showHorizontalAxisMinorTicks: true,
			showVerticalAxisGridLines: false,
			showVerticalAxisTicks: false,
			showVerticalAxisMinorTicks: false,
			seriesItemSpacing:0
		};
		
	//--------------------------------------
	//  Class Methods
	//--------------------------------------
	
		/**
		 * @private
		 * @copy fl.core.UIComponent#getStyleDefinition()
		 */
		public static function getStyleDefinition():Object
		{
			return mergeStyles(defaultStyles, CartesianChart.getStyleDefinition());
		}
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function BarChart()
		{
			super();
			this.defaultSeriesType = BarSeries;
		}
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
		
		/**
		 * @private
		 */
		override protected function configUI():void
		{
			if(!this.horizontalAxis)
			{
				var numericAxis:NumericAxis = new NumericAxis();
				numericAxis.stackingEnabled = true;
				this.horizontalAxis = numericAxis;
			}
			
			if(!this.verticalAxis)
			{
				var categoryAxis:CategoryAxis = new CategoryAxis();
				this.verticalAxis = categoryAxis;
			}
			
			super.configUI();
		}
		
	}
}
