package com.yahoo.astra.fl.charts
{
	import com.yahoo.astra.fl.charts.series.StackedBarSeries;
	
	/**
	 * A chart that displays its data points with horizontal bars
	 * that are stacked horizontally.
	 * 
	 * @author Josh Tynjala
	 */
	public class StackedBarChart extends BarChart
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function StackedBarChart()
		{
			super();
			this.defaultSeriesType = StackedBarSeries;
		}
		
	}
}