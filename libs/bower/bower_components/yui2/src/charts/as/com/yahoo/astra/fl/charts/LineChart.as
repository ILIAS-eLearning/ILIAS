package com.yahoo.astra.fl.charts
{
	import fl.core.UIComponent;
	import com.yahoo.astra.fl.charts.series.ISeries;
	import com.yahoo.astra.fl.charts.series.LineSeries;
	import com.yahoo.astra.fl.charts.skins.*;
	
	//--------------------------------------
	//  Styles
	//--------------------------------------
	
	/**
     * The weight, in pixels, of the line drawn between points in each series.
     * 
     * <p>An Array of values that correspond to series indices in the data
     * provider. If the number of values in the Array is less than the number
     * of series, then the next series will restart at index zero in the style
     * Array. If the value of this style is an empty Array, then each individual series
     * will use the default or modified value set on the series itself.</p> 
     * 
     * <p>Example: If the seriesLineWeights style is equal to [2, 3] and there
     * are three series in the chart's data provider, then the series at index 0
     * will have a line weight of 2, index 1 will have a line weight of 3, and
     * index 2 will have a line weight of 2 (starting over from the beginning).</p>
     *
     * @default null
     */
    [Style(name="seriesLineWeights", type="Array")]

	/**
	 * The color of the line drawn between points in each series. When not specified,
	 * the line color is determined by the color style.
	 * 
	 * @default []
	 */
	[Style(name="seriesLineColors", type="Array")]
	
	/**
	 * The alpha of the line drawn between points in each series. 
	 *
	 * @default [1]
	 */
	[Style(name="seriesLineAlphas", type="Array")]

	/**
	 * A chart that displays its data points with connected line segments.
	 * 
	 * @author Josh Tynjala
	 */
	public class LineChart extends CartesianChart
	{
		
	//--------------------------------------
	//  Class Variables
	//--------------------------------------
		
		/**
		 * @private
		 */
		private static var defaultStyles:Object = 
		{	
			seriesLineWeights: [],
			seriesMarkerSkins: [CircleSkin, DiamondSkin, RectangleSkin, TriangleSkin]
		};
			
		/**
		 * @private
		 * The chart styles that correspond to styles on each series.
		 */
		private static const LINE_SERIES_STYLES:Object = 
		{
			lineWeight: "seriesLineWeights"
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
		public function LineChart()
		{
			super();
			this.defaultSeriesType = LineSeries;
		}
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
		
		/**
		 * @private
		 */
		override protected function refreshSeries():void
		{
			super.refreshSeries();
			
			var seriesCount:int = this.series.length;
			for(var i:int = 0; i < seriesCount; i++)
			{
				var currentSeries:ISeries = this.series[i] as ISeries;
				this.copyStylesToSeries(currentSeries, LINE_SERIES_STYLES);
			}
		}
		
		/**
		 * @private
		 */
		override protected function configUI():void
		{
			super.configUI();
			this.setChildIndex(this.axisLayer, this.getChildIndex(this.content))
		}
	}
}
