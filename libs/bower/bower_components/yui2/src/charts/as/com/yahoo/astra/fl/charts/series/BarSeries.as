package com.yahoo.astra.fl.charts.series
{
	import com.yahoo.astra.animation.Animation;
	import com.yahoo.astra.animation.AnimationEvent;
	import com.yahoo.astra.fl.charts.*;
	import com.yahoo.astra.fl.charts.axes.IAxis;
	import com.yahoo.astra.fl.charts.axes.IClusteringAxis;
	import com.yahoo.astra.fl.charts.axes.IOriginAxis;
	import com.yahoo.astra.fl.charts.skins.RectangleSkin;
	import com.yahoo.astra.fl.utils.UIComponentUtil;
	
	import fl.core.UIComponent;
	
	import flash.display.DisplayObject;
	import flash.geom.Point;

	/**
	 * Renders data points as a series of horizontal bars.
	 * 
	 * @author Josh Tynjala
	 */
	public class BarSeries extends CartesianSeries
	{
		
	//--------------------------------------
	//  Class Variables
	//--------------------------------------
		
		/**
		 * @private
		 */
		private static var defaultStyles:Object =
		{
			markerSkin: RectangleSkin,
			markerSize: 18
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
			return mergeStyles(defaultStyles, CartesianSeries.getStyleDefinition());
		}
		
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function BarSeries(data:Object = null)
		{
			super(data);
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * @private
		 * The Animation instance that controls animation in this series.
		 */
		private var _animation:Animation;
	
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
	
		/**
		 * @inheritDoc
		 */
		override public function clone():ISeries
		{
			var series:BarSeries = new BarSeries();
			if(this.dataProvider is Array)
			{
				//copy the array rather than pass it by reference
				series.dataProvider = (this.dataProvider as Array).concat();
			}
			else if(this.dataProvider is XMLList)
			{
				series.dataProvider = (this.dataProvider as XMLList).copy();
			}
			series.displayName = this.displayName;
			series.horizontalField = this.horizontalField;
			series.verticalField = this.verticalField;
			
			return series;
		}
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
		
		/**
		 * @private
		 */
		override protected function draw():void
		{
			super.draw();
			
			this.graphics.clear();
			
			//if we don't have data, let's get out of here
			if(!this.dataProvider)
			{
				return;
			}
			
			this.graphics.lineStyle(1, 0x0000ff);
			
			//grab the axes
			var cartesianChart:CartesianChart = this.chart as CartesianChart;
			var xAxis:String = this.axis == "primary" ? "horizontalAxis" : "secondaryHorizontalAxis";
			var valueAxis:IOriginAxis = cartesianChart[xAxis] as IOriginAxis;			
			var otherAxis:IAxis = cartesianChart.verticalAxis;
			if(!valueAxis)
			{
				throw new Error("To use a BarSeries object, the horizontal axis of the chart it appears within must be an IOriginAxis.");
				return;
			}
			
			var markerSizes:Array = [];
			var allSeriesOfType:Array = ChartUtil.findSeriesOfType(this, this.chart as IChart);
			var totalMarkerSize:Number = this.calculateTotalMarkerSize(otherAxis, markerSizes);
			var seriesIndex:int = allSeriesOfType.indexOf(this);
			var markerSize:Number = markerSizes[seriesIndex] as Number;
			var yOffset:Number = this.calculateYOffset(valueAxis, otherAxis, markerSizes, totalMarkerSize, allSeriesOfType);
			var seriesItemSpacing:Number = UIComponentUtil.getStyleValue(UIComponent(this.chart), "seriesItemSpacing") as Number;
			
			var startValues:Array = [];
			var endValues:Array = [];
			var itemCount:int = this.length;
			for(var i:int = 0; i < itemCount; i++)
			{
				var originValue:Object = this.calculateOriginValue(i, valueAxis, allSeriesOfType);
				var originPosition:Number = valueAxis.valueToLocal(originValue);
				
				var position:Point = IChart(this.chart).itemToPosition(this, i);
				var marker:DisplayObject = this.markers[i] as DisplayObject;
				position.y += (allSeriesOfType.length - 1) * seriesItemSpacing;
				
				marker.y = position.y + yOffset;
				marker.height = markerSize;
				
				//if we have a bad position, don't display the marker
				if(isNaN(position.x) || isNaN(position.y))
				{
					this.invalidateMarker(ISeriesItemRenderer(marker));
				}
				else if(this.isMarkerInvalid(ISeriesItemRenderer(marker)))
				{
					//initialize the marker to the origin
					marker.x = originPosition;
					marker.width = 0;
				
					if(marker is UIComponent) 
					{
						(marker as UIComponent).drawNow();
					}
					this.validateMarker(ISeriesItemRenderer(marker));
				}
				
				//stupid Flash UIComponent rounding!
				position.x = Math.round(position.x);
				originPosition = Math.round(originPosition);
				
				var calculatedWidth:Number = originPosition - position.x;
				if(calculatedWidth < 0)
				{
					calculatedWidth = Math.abs(calculatedWidth);
					position.x = Math.round(originPosition);
					//always put the marker on the origin
					marker.x = position.x;
				}
				
				startValues.push(marker.x, marker.width);
				endValues.push(position.x, calculatedWidth);
			}
			
			//handle animating all the markers in one fell swoop.
			if(this._animation)
			{
				this._animation.removeEventListener(AnimationEvent.UPDATE, tweenUpdateHandler);
				this._animation.removeEventListener(AnimationEvent.COMPLETE, tweenUpdateHandler);
				this._animation = null;
			}
			
			//don't animate on livepreview!
			if(this.isLivePreview || !this.getStyleValue("animationEnabled"))
			{
				this.drawMarkers(endValues);
			}
			else
			{
				var animationDuration:int = this.getStyleValue("animationDuration") as int;
				var animationEasingFunction:Function = this.getStyleValue("animationEasingFunction") as Function;
				
				this._animation = new Animation(animationDuration, startValues, endValues);
				this._animation.addEventListener(AnimationEvent.UPDATE, tweenUpdateHandler);
				this._animation.addEventListener(AnimationEvent.COMPLETE, tweenUpdateHandler);
				this._animation.easingFunction = animationEasingFunction;
			}
		}
		
		/**
		 * @private
		 * Determines the maximum possible marker size for the containing chart.
		 */
		protected function calculateMaximumAllowedMarkerSize(axis:IAxis):Number
		{
			var seriesItemSpacing:Number = UIComponentUtil.getStyleValue(UIComponent(this.chart), "seriesItemSpacing") as Number;
			if(axis is IClusteringAxis)
			{
				var allSeriesOfType:Array = ChartUtil.findSeriesOfType(this, this.chart as IChart);
				var availableHeight:Number = this.height - (IClusteringAxis(axis).clusterCount * seriesItemSpacing *(allSeriesOfType.length - 1));
				return (availableHeight / IClusteringAxis(axis).clusterCount) / allSeriesOfType.length;
			}
			return Number.POSITIVE_INFINITY;
		}
		
		/**
		 * @private
		 * Determines the marker size for a series.
		 */
		protected function calculateMarkerSize(series:ISeries, axis:IAxis):Number
		{
			var markerSize:Number = UIComponentUtil.getStyleValue(UIComponent(series), "markerSize") as Number;
			var maximumAllowedMarkerSize:Number = this.calculateMaximumAllowedMarkerSize(axis);
			markerSize = Math.min(maximumAllowedMarkerSize, markerSize);
			
			//we need to use floor because CS3 UIComponents round the position
			markerSize = Math.floor(markerSize);
			return markerSize;
		}
		
		/**
		 * @private
		 * Calculates the sum of the chart's series marker sizes.
		 */
		protected function calculateTotalMarkerSize(axis:IAxis, sizes:Array):Number
		{
			var totalMarkerSize:Number = 0;
			var allSeriesOfType:Array = ChartUtil.findSeriesOfType(this, this.chart as IChart);
			var seriesCount:int = allSeriesOfType.length;
			var seriesItemSpacing:Number = UIComponentUtil.getStyleValue(UIComponent(this.chart), "seriesItemSpacing") as Number;
			for(var i:int = 0; i < seriesCount; i++)
			{
				var series:BarSeries = BarSeries(allSeriesOfType[i]);
				var markerSize:Number = this.calculateMarkerSize(series, axis);
				sizes.push(markerSize);
				if(axis is IClusteringAxis)
				{
					totalMarkerSize += markerSize;
				}
				else
				{
					totalMarkerSize = Math.max(totalMarkerSize, markerSize);
				}
			}
			totalMarkerSize += seriesItemSpacing * (seriesCount-1);
			return totalMarkerSize;
		}
		
		/**
		 * @private
		 * Calculates the y offset caused by clustering.
		 */
		protected function calculateYOffset(valueAxis:IOriginAxis, otherAxis:IAxis, markerSizes:Array, totalMarkerSize:Number, allSeriesOfType:Array):Number
		{
			var seriesIndex:int = allSeriesOfType.indexOf(this);
			var seriesItemSpacing:Number = UIComponentUtil.getStyleValue(UIComponent(this.chart), "seriesItemSpacing") as Number;
			var seriesCount:int = allSeriesOfType.length;
			//special case for axes that allow clustering
			if(otherAxis is IClusteringAxis)
			{
				var yOffset:Number = 0;
				for(var i:int = 0; i < seriesIndex; i++)
				{
					yOffset += markerSizes[i] as Number;
				}
				yOffset -= (markerSizes.length - (i+1)) * seriesItemSpacing;
				//center based on the sum of all marker sizes
				return -(totalMarkerSize / 2) + yOffset;
			}
			//center based on the marker size of this series
			return -(markerSizes[seriesIndex] as Number) / 2;
		}
		
		/**
		 * @private
		 * Determines the origin of the column. Either the axis origin or the
		 * stacked value.
		 */
		protected function calculateOriginValue(index:int, axis:IOriginAxis, allSeriesOfType:Array):Object
		{
			return axis.origin;
		}
		
	//--------------------------------------
	//  Private Methods
	//--------------------------------------
		
		/**
		 * @private
		 * Draws the markers. Used with animation.
		 */
		private function drawMarkers(data:Array):void
		{
			var itemCount:int = this.length;
			for(var i:int = 0; i < itemCount; i++)
			{
				var marker:DisplayObject = this.markers[i] as DisplayObject;
				var markerX:Number = data[i * 2];
				var markerWidth:Number = data[i * 2 + 1];
				marker.x = markerX;
				marker.width = markerWidth;
				
				if(marker is UIComponent) 
				{
					UIComponent(marker).drawNow();
				}
			}
		}
		
	//--------------------------------------
	//  Private Event Handlers
	//--------------------------------------
		
		/**
		 * @private
		 * Draws the markers every time the tween updates.
		 */
		private function tweenUpdateHandler(event:AnimationEvent):void
		{
			var data:Array = event.parameters as Array;
			this.drawMarkers(data);
		}
		
	}
}