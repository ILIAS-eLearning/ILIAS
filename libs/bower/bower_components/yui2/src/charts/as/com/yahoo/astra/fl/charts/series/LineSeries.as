package com.yahoo.astra.fl.charts.series
{
	import com.yahoo.astra.animation.Animation;
	import com.yahoo.astra.animation.AnimationEvent;
	import com.yahoo.astra.fl.charts.*;
	import com.yahoo.astra.fl.charts.axes.NumericAxis;
	import com.yahoo.astra.fl.charts.skins.CircleSkin;
	import com.yahoo.astra.utils.GraphicsUtil;
	
	import fl.core.UIComponent;
	
	import flash.display.DisplayObject;
	import flash.geom.Point;
	import flash.geom.Rectangle;
	
	/**
     * The weight, in pixels, of the line drawn between points in this series.
     *
     * @default 3
     */
    [Style(name="lineWeight", type="Number")]
	
	/**
     * If true, lines are drawn between the markers. If false, only the markers are drawn.
     *
     * @default true
     */
    [Style(name="connectPoints", type="Boolean")]
	
	/**
     * If true, draws a dashed line between discontinuous points.
     *
     * @default false
     */
    [Style(name="connectDiscontinuousPoints", type="Boolean")]
	
	/**
     * The length of dashes in a discontinuous line. 
     *
     * @default 10
     */
    [Style(name="discontinuousDashLength", type="Number")]
	
	/**
     * If true, the series will include a fill under the line, extending to the axis.
     *
     * @default false
     */
    [Style(name="showAreaFill", type="Boolean")]
	
	/**
     * The alpha value of the area fill.
     *
     * @default 0.6
     */
    [Style(name="areaFillAlpha", type="Number")]
    
	/**
	 * Renders data points as a series of connected line segments.
	 * 
	 * @author Josh Tynjala
	 */
	public class LineSeries extends CartesianSeries
	{
		
	//--------------------------------------
	//  Class Variables
	//--------------------------------------
		
		/**
		 * @private
		 */
		private static var defaultStyles:Object =
		{
			markerSkin: CircleSkin,
			lineWeight: 3,
			connectPoints: true,
			connectDiscontinuousPoints: false,
			discontinuousDashLength: 10,
			showAreaFill: false,
			areaFillAlpha: 0.6
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
		 *  Constructor.
		 */
		public function LineSeries(data:Object = null)
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
			var series:LineSeries = new LineSeries();
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
			
			if(!this.dataProvider)
			{
				return;
			}
			
			var markerSize:Number = this.getStyleValue("markerSize") as Number;
			
			var startValues:Array = [];
			var endValues:Array = [];
			var itemCount:int = this.length;
			for(var i:int = 0; i < itemCount; i++)
			{
				var position:Point = CartesianChart(this.chart).itemToPosition(this, i);
				
				var marker:DisplayObject = this.markers[i] as DisplayObject;
				var ratio:Number = marker.width / marker.height;
				if(isNaN(ratio)) ratio = 1;
				marker.height = markerSize;
				marker.width = marker.height * ratio;
				
				if(marker is UIComponent) 
				{
					(marker as UIComponent).drawNow();
				}
				
				//if we have a bad position, don't display the marker
				if(isNaN(position.x) || isNaN(position.y))
				{
					this.invalidateMarker(ISeriesItemRenderer(marker));
				}
				else if(this.isMarkerInvalid(ISeriesItemRenderer(marker)))
				{
					marker.x = position.x - marker.width / 2;
					marker.y = position.y - marker.height / 2;
					this.validateMarker(ISeriesItemRenderer(marker));
				}
				
				//correct start value for marker size
				startValues.push(marker.x + marker.width / 2);
				startValues.push(marker.y + marker.height / 2);
				
				endValues.push(position.x);
				endValues.push(position.y);
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
				this.drawMarkers(startValues);
			}
		}
		
		/**
		 * @private
		 */
		private function tweenUpdateHandler(event:AnimationEvent):void
		{
			this.drawMarkers(event.parameters as Array);
		}
		
		/**
		 * @private
		 */
		private function drawMarkers(data:Array):void
		{
			var primaryIsVertical:Boolean = true;
			var yAxis:String = this.axis == "primary" ? "verticalAxis" : "secondaryVerticalAxis";
			var primaryAxis:NumericAxis = CartesianChart(this.chart)[yAxis] as NumericAxis;

			if(!primaryAxis)
			{
				var xAxis:String = this.axis == "primary" ? "horizontalAxis" : "secondaryHorizontalAxis";
				primaryIsVertical = false;
				primaryAxis = CartesianChart(this.chart)[xAxis] as NumericAxis;
			}
			
			var originPosition:Number = primaryAxis.valueToLocal(primaryAxis.origin);
			
			var lineColor:uint = this.getStyleValue("lineColor") != null ? this.getStyleValue("lineColor") as uint : this.getStyleValue("color") as uint;
			var connectDiscontinuousPoints:Boolean = this.getStyleValue("connectDiscontinuousPoints") as Boolean;
			var discontinuousDashLength:Number = this.getStyleValue("discontinuousDashLength") as Number;
			var showAreaFill:Boolean = this.getStyleValue("showAreaFill") as Boolean;
			
			this.graphics.clear();
			this.setPrimaryLineStyle();
			this.beginAreaFill(showAreaFill, lineColor);
			
			var firstValidPosition:Point;
			var lastValidPosition:Point;
			
			//used to determine if the data must be drawn
			var seriesBounds:Rectangle = new Rectangle(0, 0, this.width, this.height);
			var lastMarkerValid:Boolean = false;
			var itemCount:int = this.length;
			for(var i:int = 0; i < itemCount; i++)
			{
				var marker:DisplayObject = DisplayObject(this.markers[i]);
				var xPosition:Number = data[i * 2] as Number;
				var yPosition:Number = data[i * 2 + 1] as Number;

				var markerValid:Boolean = !this.isMarkerInvalid(ISeriesItemRenderer(marker));
				
				//if the position is valid, move or draw as needed
				if(markerValid)
				{
					marker.x = xPosition - marker.width / 2;
					marker.y = yPosition - marker.height / 2;
					marker.visible = yPosition <= seriesBounds.height + seriesBounds.y && yPosition >= seriesBounds.y;
					
					if(lastValidPosition && !lastMarkerValid && connectDiscontinuousPoints)
					{
						this.setPrimaryLineStyle(connectDiscontinuousPoints);
			
						//draw a discontinuous line from the last valid position and the new valid position
						GraphicsUtil.drawDashedLine(this.graphics, lastValidPosition.x, lastValidPosition.y, xPosition, yPosition, discontinuousDashLength, discontinuousDashLength);
						
						this.setPrimaryLineStyle();
					}
					else if(!lastValidPosition || (!lastMarkerValid && !connectDiscontinuousPoints))
					{
						//if the last position is not valid, simply move to the new position
						var newY:Number = yPosition;
						if(yPosition < seriesBounds.y) 
						{
							newY = seriesBounds.y;
						}
						if(yPosition > seriesBounds.y + seriesBounds.height) 
						{
							newY = seriesBounds.y + seriesBounds.height;
						}

						this.graphics.moveTo(xPosition, newY);
					}
					else //current and last position are both valid
					{
						var minX:Number = Math.min(lastValidPosition.x, xPosition);
						var maxX:Number = Math.max(lastValidPosition.x, xPosition);
						var minY:Number = Math.min(lastValidPosition.y, yPosition);
						var maxY:Number = Math.max(lastValidPosition.y, yPosition);
						var lineBounds:Rectangle = new Rectangle(minX, minY, maxX - minX, maxY - minY);
						
						//if x or y position is equal between points, the rectangle will have
						//a width or height of zero (so no line will be drawn where one should!)
						if(lineBounds.width == 0)
						{
							lineBounds.width = 1;
						}
						
						if(lineBounds.height == 0)
						{
							lineBounds.height = 1;
						}
						
						var bottom:Number = seriesBounds.y + seriesBounds.height;

						//if line between the last point and this point is within
						//the series bounds, draw it, otherwise, only move to the new point.
						if(lineBounds.intersects(seriesBounds) ||
							yPosition == seriesBounds.y ||
							yPosition == seriesBounds.y + seriesBounds.height ||
							xPosition == seriesBounds.x ||
							xPosition == seriesBounds.x + seriesBounds.width ||
							showAreaFill)
						{
							var x1:Number = lastValidPosition.x;
							var x2:Number = xPosition;
							var y1:Number = lastValidPosition.y;
							var y2:Number = yPosition;
							if(yPosition > bottom)
							{	
								if(lastValidPosition.y == yPosition)
								{
									this.graphics.lineTo(xPosition, bottom);
								}
								else
								{
									if(lastValidPosition.y < seriesBounds.y) 
									{
										newX = x2 - ((y2 - seriesBounds.y)*(x2-x1)/(y2-y1));
										this.graphics.lineTo(newX, seriesBounds.y);
										this.setPrimaryLineStyle();
									}
									var newX:Number = x2 - ((y2 - bottom)*(x2-x1)/(y2-y1));
									this.graphics.lineTo(newX, bottom);
								}
								this.graphics.lineStyle(0, 0, 0);
							}
							else if(yPosition < seriesBounds.y)
							{
								if(lastValidPosition.y == yPosition)
								{
									this.graphics.lineTo(xPosition, seriesBounds.y);
								}
								else
								{										
									if(lastValidPosition.y > bottom) 
									{
										newX = x2 - ((y2 - bottom)*(x2-x1)/(y2-y1));	
										this.graphics.lineTo(newX, bottom);
										this.setPrimaryLineStyle();
									}
													
									newX = x2 - ((y2 - seriesBounds.y)*(x2-x1)/(y2-y1));
									if(lastValidPosition.y < seriesBounds.y) this.graphics.lineStyle(0, 0, 0);
									this.graphics.lineTo(newX, seriesBounds.y);
								}
								this.graphics.lineStyle(0, 0, 0);
							}
							else
							{	
								if(lastValidPosition.y > bottom) 
								{
									newX = x2 - ((y2 - bottom)*(x2-x1)/(y2-y1));	
									this.graphics.lineTo(newX, bottom);
									this.setPrimaryLineStyle();
								}
						
								if(lastValidPosition.y < seriesBounds.y)
								{
									newX = x2 - ((y2 - seriesBounds.y)*(x2-x1)/(y2-y1));							
									this.graphics.lineTo(newX, seriesBounds.y);
									this.setPrimaryLineStyle();									
								}

								this.graphics.lineTo(xPosition, yPosition);
							}	
						}
						else
						{
							this.graphics.moveTo(xPosition, yPosition);
						}						
					}
					lastMarkerValid = true;
					lastValidPosition = new Point(xPosition, yPosition);
					if(!firstValidPosition)
					{
						firstValidPosition = lastValidPosition.clone();
					}
				}
				else
				{
					if(showAreaFill) this.closeAreaFill(primaryIsVertical, originPosition, firstValidPosition, lastValidPosition);
					this.setPrimaryLineStyle();
					this.beginAreaFill(showAreaFill, lineColor);
					lastMarkerValid = false;
					firstValidPosition = null;
				}
				
			}
			
			if(showAreaFill)
			{
				this.closeAreaFill(primaryIsVertical, originPosition, firstValidPosition, lastValidPosition);
			}
		}
		
		/**
		 * @private
		 * Begins drawing an area fill.
		 */
		private function beginAreaFill(showAreaFill:Boolean, color:uint):void
		{
			if(!showAreaFill)
			{
				return;
			}
			
			var areaFillAlpha:Number = this.getStyleValue("areaFillAlpha") as Number;
			this.graphics.beginFill(color, areaFillAlpha);
		}
		
		/**
		 * @private
		 * Sets the line style when connecting points. The forceColor flag
		 * will use the color even when the connectPoints style is set to false.
		 * This is used primarily to allow connectDiscontinousPoints to work
		 * when connectPoints is false.
		 */
		private function setPrimaryLineStyle(forceColor:Boolean = false):void
		{
			var connectPoints:Boolean = this.getStyleValue("connectPoints") as Boolean;
			if(!connectPoints && !forceColor)
			{
				this.graphics.lineStyle(0, 0, 0);
				return;
			}
			
			var lineWeight:int = this.getStyleValue("lineWeight") as int;
			var lineColor:uint = this.getStyleValue("lineColor") != null ? this.getStyleValue("lineColor") as uint : this.getStyleValue("color") as uint;
			var lineAlpha:Number = this.getStyleValue("lineAlpha") as Number;
			this.graphics.lineStyle(lineWeight, lineColor, lineAlpha, false, "normal", "none");
		}
		
		/**
		 * @private
		 * Closes an area fill. Called after the full line is drawn. May also be
		 * called when bad data is encountered.
		 */
		private function closeAreaFill(vertical:Boolean, origin:Number, firstValidPosition:Point, lastValidPosition:Point):void
		{
			if(isNaN(origin) || firstValidPosition == null || lastValidPosition == null) return;
			this.graphics.lineStyle(0, 0, 0);
			if(vertical)
			{
				this.graphics.lineTo(lastValidPosition.x, origin);
				this.graphics.lineTo(firstValidPosition.x, origin);
				this.graphics.lineTo(firstValidPosition.x, firstValidPosition.y);
			}
			else
			{
				this.graphics.lineTo(origin, lastValidPosition.y);
				this.graphics.lineTo(origin, firstValidPosition.y);
				this.graphics.lineTo(firstValidPosition.x, firstValidPosition.y);
			}
			this.graphics.endFill();
		}	
		
	}
}
