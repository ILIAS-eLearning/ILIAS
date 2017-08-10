package com.yahoo.astra.fl.charts.axes
{
	import com.yahoo.astra.utils.GeomUtil;
	import com.yahoo.astra.utils.NumberUtil;
	
	import fl.core.UIComponent;
	
	import flash.geom.Point;

	//TODO: Add support for labels.
	/**
	 * The default axis renderer for radial axes.
	 * 
	 * @author Josh Tynjala
	 */
	public class RadialAxisRenderer extends UIComponent implements IRadialAxisRenderer
	{
		
	//--------------------------------------
	//  Class Variables
	//--------------------------------------
		
		/**
		 * @private
		 */
		private static var defaultStyles:Object = 
		{
			//axis
			showAxis: true,
			axisWeight: 1,
			axisColor: 0x888a85,
			
			//ticks
			showTicks: true,
			tickWeight: 1,
			tickColor: 0x888a85,
			tickLength: 4,
			tickPosition: TickPosition.INSIDE,
			
			//minor ticks
			showMinorTicks: true,
			minorTickWeight: 1,
			minorTickColor: 0x888a85,
			minorTickLength: 3,
			minorTickPosition: TickPosition.INSIDE
		};
		
	//--------------------------------------
	//  Class Methods
	//--------------------------------------
	
		/**
		 * @copy fl.core.UIComponent#getStyleDefinition()
		 */
		public static function getStyleDefinition():Object
		{
			return mergeStyles(defaultStyles, UIComponent.getStyleDefinition());
		}
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function RadialAxisRenderer()
		{
			super();
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
		
		/**
		 * @inheritDoc
		 */
		public function get length():Number
		{
			return Math.min(this.width, this.height) * Math.PI;
		}
		
		/**
		 * @private
		 * Storage for the ticks property.
		 */
		private var _ticks:Array = [];
		
		/**
		 * @inheritDoc
		 */
		public function get ticks():Array
		{
			return this._ticks;
		}
		
		/**
		 * @private
		 */
		public function set ticks(value:Array):void
		{
			this._ticks = value;
			this.invalidate();
		}
		
		/**
		 * @private
		 * Storage for the minorTicks property.
		 */
		private var _minorTicks:Array = [];
		
		/**
		 * @inheritDoc
		 */
		public function get minorTicks():Array
		{
			return this._minorTicks;
		}
		
		/**
		 * @private
		 */
		public function set minorTicks(value:Array):void
		{
			this._minorTicks = value;
			this.invalidate();
		}
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
		
		/**
		 * @inheritDoc
		 */
		public function updateBounds():void
		{
			//no labels are created at this time, so this function is pretty useless
		}
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
	
		/**
		 * @private
		 */
		override protected function draw():void
		{
			var showTicks:Boolean = this.getStyleValue("showTicks") as Boolean;
			var showMinorTicks:Boolean = this.getStyleValue("showMinorTicks") as Boolean;
			var ticks:Array = this.ticks.concat();
			var minorTicks:Array = this.minorTicks.concat();
			if(showMinorTicks && showTicks)
			{
				//filter out minor ticks that appear at the same position
				//as major ticks.
				minorTicks = minorTicks.filter(function(item:AxisData, index:int, source:Array):Boolean
				{
					return !ticks.some(function(item2:AxisData, index2:int, source2:Array):Boolean
					{
						//using fuzzyEquals because we may encounter rounding errors
						return NumberUtil.fuzzyEquals(item.position, item2.position, 10);
					});
				});
			}
			
			this.graphics.clear();
			
			this.drawAxis();
			
			var tickPosition:String = this.getStyleValue("tickPosition") as String;
			var tickLength:Number = this.getStyleValue("tickLength") as Number;
			var tickWeight:int = this.getStyleValue("tickWeight") as int;
			var tickColor:uint = this.getStyleValue("tickColor") as uint;
			this.drawTicks(ticks, showTicks, tickPosition, tickLength, tickWeight, tickColor);
			
			var minorTickPosition:String = this.getStyleValue("minorTickPosition") as String;
			var minorTickLength:Number = this.getStyleValue("minorTickLength") as Number;
			var minorTickWeight:int = this.getStyleValue("minorTickWeight") as int;
			var minorTickColor:uint = this.getStyleValue("minorTickColor") as uint;
			this.drawTicks(minorTicks, showMinorTicks, minorTickPosition, minorTickLength, minorTickWeight, minorTickColor);
			
			super.draw();
		}
		
		/**
		 * @private
		 * Draws the main axis line.
		 */
		protected function drawAxis():void
		{
			var showAxis:Boolean = this.getStyleValue("showAxis") as Boolean;
			if(!showAxis)
			{
				return;
			}
			
			var axisWeight:int = this.getStyleValue("axisWeight") as int;
			var axisColor:uint = this.getStyleValue("axisColor") as uint;
			this.graphics.lineStyle(axisWeight, axisColor);
			
			var center:Point = new Point(this.width / 2, this.height / 2);
			var radius:Number = Math.min(center.x, center.y);
			this.graphics.drawCircle(center.x, center.y, radius);
		}
		
		/**
		 * @private
		 * Draws a set of ticks along the main axis line. This function is shared
		 * by major and minor ticks.
		 */
		protected function drawTicks(data:Array, showTicks:Boolean, tickPosition:String,
			tickLength:Number, tickWeight:Number, tickColor:uint):void
		{
			if(!showTicks)
			{
				return;
			}
			
			this.graphics.lineStyle(tickWeight, tickColor);
			
			var center:Point = new Point(this.width / 2, this.height / 2);
			var radius:Number = Math.min(center.x, center.y);
			
			var dataCount:int = data.length;
			for(var i:int = 0; i < dataCount; i++)
			{
				var axisData:AxisData = AxisData(data[i]);
				if(isNaN(axisData.position))
				{
					//skip bad positions
					continue;
				}
				
				var position:Number = axisData.position;
				var angle:Number = GeomUtil.degreesToRadians(position * 360 / this.length);
				var tickCenter:Point = Point.polar(radius, angle);
				tickCenter = tickCenter.add(center);
				switch(tickPosition)
				{
					case TickPosition.OUTSIDE:
						var outsideEnd:Point = Point.polar(tickLength, angle);
						outsideEnd = outsideEnd.add(tickCenter);
						this.graphics.moveTo(tickCenter.x, tickCenter.y);
						this.graphics.lineTo(outsideEnd.x, outsideEnd.y);
						break;
					case TickPosition.INSIDE:
						var insideEnd:Point = Point.polar(tickLength, GeomUtil.degreesToRadians(180 + GeomUtil.radiansToDegrees(angle)));
						insideEnd = insideEnd.add(tickCenter);
						this.graphics.moveTo(tickCenter.x, tickCenter.y);
						this.graphics.lineTo(insideEnd.x, insideEnd.y);
						break;
					default: //CROSS
						outsideEnd = Point.polar(tickLength / 2, angle);
						outsideEnd = outsideEnd.add(tickCenter);
						insideEnd = Point.polar(tickLength / 2, GeomUtil.degreesToRadians(180 + GeomUtil.radiansToDegrees(angle)));
						insideEnd = insideEnd.add(tickCenter);
						this.graphics.moveTo(outsideEnd.x, outsideEnd.y);
						this.graphics.lineTo(insideEnd.x, insideEnd.y);
						break;
				}
			}
			
		}
		
	}
}