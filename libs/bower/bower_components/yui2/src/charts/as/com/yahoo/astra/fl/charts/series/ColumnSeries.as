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
	import flash.utils.Dictionary;

	/**
	 * Renders data points as a series of vertical columns.
	 * 
	 * @author Josh Tynjala
	 */
	public class ColumnSeries extends CartesianSeries
	{
		
	//--------------------------------------
	//  Static Variables
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
	//  Static Methods
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
		public function ColumnSeries(data:Object = null)
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
			var series:ColumnSeries = new ColumnSeries();
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
			
			//if we don't have data, let's get out of here
			if(!this.dataProvider)
			{
				return;
			}
			
			//grab the axes
			var cartesianChart:CartesianChart = this.chart as CartesianChart;
			var yAxis:String = this.axis == "primary" ? "verticalAxis" : "secondaryVerticalAxis";
			var valueAxis:IOriginAxis = cartesianChart[yAxis] as IOriginAxis;
			var otherAxis:IAxis = cartesianChart.horizontalAxis;
			if(!valueAxis)
			{
				throw new Error("To use a ColumnSeries object, the vertical axis of the chart it appears within must be an IOriginAxis.");
				return;
			}
			
			var allSeriesOfType:Array = ChartUtil.findSeriesOfType(this, cartesianChart);
			var markerSizes:Array = [];
			var totalMarkerSize:Number = this.calculateTotalMarkerSize(otherAxis, markerSizes);
			var seriesIndex:int = allSeriesOfType.indexOf(this);
			var markerSize:Number = markerSizes[seriesIndex] as Number;
			var xOffset:Number = this.calculateXOffset(valueAxis, otherAxis, markerSizes, totalMarkerSize, allSeriesOfType);			
			var seriesItemSpacing:Number = UIComponentUtil.getStyleValue(UIComponent(this.chart), "seriesItemSpacing") as Number;

			var startValues:Array = [];
			var endValues:Array = [];
			var itemCount:int = this.length;
			for(var i:int = 0; i < itemCount; i++)
			{
				var originValue:Object = this.calculateOriginValue(i, valueAxis, allSeriesOfType);
				var originPosition:Number = valueAxis.valueToLocal(originValue);
				
				var position:Point = IChart(this.chart).itemToPosition(this, i);
				position.x += (allSeriesOfType.length - 1) * seriesItemSpacing;
				var marker:DisplayObject = this.markers[i] as DisplayObject;

				marker.x = position.x + xOffset;
				
				marker.width = markerSize;
				
				//if we have a bad position, don't display the marker
				if(isNaN(position.x) || isNaN(position.y))
				{
					this.invalidateMarker(ISeriesItemRenderer(marker));
				}
				else if(this.isMarkerInvalid(ISeriesItemRenderer(marker)))
				{
					//initialize the marker to the origin
					marker.y = originPosition;
					marker.height = 0;
				
					if(marker is UIComponent) 
					{
						(marker as UIComponent).drawNow();
					}
					this.validateMarker(ISeriesItemRenderer(marker));
				}
				
				//stupid Flash UIComponent rounding!
				position.y = Math.round(position.y);
				originPosition = Math.round(originPosition);
				
				var calculatedHeight:Number = originPosition - position.y;
				if(calculatedHeight < 0)
				{
					calculatedHeight = Math.abs(calculatedHeight);
					position.y = Math.round(originPosition);
					//always put the marker on the origin
					marker.y = position.y;
				}
				
				startValues.push(marker.y, marker.height);
				endValues.push(position.y, calculatedHeight);
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
		 * Calculates the x offset caused by clustering.
		 */
		protected function calculateXOffset(valueAxis:IOriginAxis, otherAxis:IAxis, markerSizes:Array, totalMarkerSize:Number, allSeriesOfType:Array):Number
		{
			var seriesIndex:int = allSeriesOfType.indexOf(this);
			var seriesItemSpacing:Number = UIComponentUtil.getStyleValue(UIComponent(this.chart), "seriesItemSpacing") as Number;

			//special case for axes that allow clustering
			if(otherAxis is IClusteringAxis)
			{
				var xOffset:Number = 0;
				for(var i:int = 0; i < seriesIndex; i++)
				{
					xOffset += markerSizes[i] as Number;
				}
				xOffset -= (markerSizes.length - (i+1)) * seriesItemSpacing;

				//center based on the sum of all marker sizes
				return -(totalMarkerSize / 2) + xOffset;
			}
			//center based on the marker size of this series
			return -(markerSizes[seriesIndex] as Number) / 2;
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
				var series:ColumnSeries = ColumnSeries(allSeriesOfType[i]);
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
		 * Determines the maximum possible marker size for the containing chart.
		 */
		protected function calculateMaximumAllowedMarkerSize(axis:IAxis):Number
		{
			var seriesItemSpacing:Number = UIComponentUtil.getStyleValue(UIComponent(this.chart), "seriesItemSpacing") as Number;
			if(axis is IClusteringAxis)
			{
				var allSeriesOfType:Array = ChartUtil.findSeriesOfType(this, this.chart as IChart);
				var availableWidth:Number = this.width - (IClusteringAxis(axis).clusterCount * seriesItemSpacing *(allSeriesOfType.length - 1));
				return ((availableWidth / IClusteringAxis(axis).clusterCount) / allSeriesOfType.length);			
			}
			return Number.POSITIVE_INFINITY;
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
				var markerY:Number = data[i * 2];
				var markerHeight:Number = data[i * 2 + 1];
				marker.y = markerY;
				marker.height = markerHeight;
				
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