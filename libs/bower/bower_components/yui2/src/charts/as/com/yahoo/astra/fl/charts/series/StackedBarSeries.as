package com.yahoo.astra.fl.charts.series
{
	import com.yahoo.astra.fl.charts.IChart;
	import com.yahoo.astra.fl.charts.ChartUtil;
	import com.yahoo.astra.fl.charts.axes.IAxis;
	import com.yahoo.astra.fl.charts.axes.IOriginAxis;
	import com.yahoo.astra.fl.charts.axes.IClusteringAxis;
	
	/**
	 * A bar series type that stacks.
	 * 
	 * @author Josh Tynjala
	 */
	public class StackedBarSeries extends BarSeries implements IStackedSeries
	{
			
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function StackedBarSeries(data:Object=null)
		{
			super(data);
		}
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
		
		/**
		 * @private
		 * @inheritDoc
		 */
		override protected function calculateYOffset(valueAxis:IOriginAxis, otherAxis:IAxis, markerSizes:Array, totalMarkerSize:Number, allSeriesOfType:Array):Number
		{
			if(!ChartUtil.isStackingAllowed(valueAxis, this))
			{
				return super.calculateYOffset(valueAxis, otherAxis, markerSizes, totalMarkerSize, allSeriesOfType);
			}
			
			var seriesIndex:int = allSeriesOfType.indexOf(this);
			return -(markerSizes[seriesIndex] as Number) / 2;
		}
		
		/**
		 * @private
		 * @inheritDoc
		 */
		override protected function calculateTotalMarkerSize(axis:IAxis, sizes:Array):Number
		{
			if(!ChartUtil.isStackingAllowed(axis, this))
			{
				return super.calculateTotalMarkerSize(axis, sizes);
			}
			
			var totalMarkerSize:Number = 0;
			var allSeriesOfType:Array = ChartUtil.findSeriesOfType(this, this.chart as IChart);
			var seriesCount:int = allSeriesOfType.length;
			for(var i:int = 0; i < seriesCount; i++)
			{
				var series:BarSeries = BarSeries(allSeriesOfType[i]);
				var markerSize:Number = this.calculateMarkerSize(series, axis);
				sizes.push(markerSize);
				totalMarkerSize = Math.max(totalMarkerSize, markerSize);
			}
			return totalMarkerSize;
		}
		
		/**
		 * @private
		 * @inheritDoc
		 */
		override protected function calculateMaximumAllowedMarkerSize(axis:IAxis):Number
		{
			if(axis is IClusteringAxis)
			{
				var allSeriesOfType:Array = ChartUtil.findSeriesOfType(this, this.chart as IChart);
				return (this.height / IClusteringAxis(axis).clusterCount);
			}
			return Number.POSITIVE_INFINITY;
		}
		
		/**
		 * @private
		 * Determines the origin of the bar. Either the axis origin or the
		 * stacked value.
		 */
		override protected function calculateOriginValue(index:int, axis:IOriginAxis, allSeriesOfType:Array):Object
		{
			if(!ChartUtil.isStackingAllowed(axis, this))
			{
				return super.calculateOriginValue(index, axis, allSeriesOfType);
			}
			
			var seriesIndex:int = allSeriesOfType.indexOf(this);
			var originValue:Object = axis.origin;
			if(seriesIndex > 0)
			{
				var previousSeries:StackedBarSeries = StackedBarSeries(allSeriesOfType[seriesIndex - 1]);
				
				var isPositive:Boolean = IChart(this.chart).itemToAxisValue(this, index, axis) >= 0;
					
				for(var i:int = seriesIndex - 1; i > -1; i--)					
				{
					if(isPositive)
					{
						if(IChart(this.chart).itemToAxisValue(StackedBarSeries(allSeriesOfType[i]), index, axis) > 0)
						{
							originValue = IChart(this.chart).itemToAxisValue(StackedBarSeries(allSeriesOfType[i]), index, axis);
							break;								
						}							
					}
					else
					{
						if(IChart(this.chart).itemToAxisValue(StackedBarSeries(allSeriesOfType[i]), index, axis) < 0)
						{
							originValue = IChart(this.chart).itemToAxisValue(StackedBarSeries(allSeriesOfType[i]), index, axis);
							break;
						}
					}
				}
			}
			return originValue;
		}
			
		
	}
}