package com.yahoo.astra.fl.charts.axes
{
	import com.yahoo.astra.utils.NumberUtil;
	
	import fl.core.UIComponent;

	/**
	 * Renders grid lines associated with a cartesian axis.
	 * 
	 * @author Josh Tynjala
	 */
	public class DefaultGridLinesRenderer extends UIComponent implements IGridLinesRenderer
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
		
		/**
		 * Constructor.
		 */
		public function DefaultGridLinesRenderer()
		{
			super();
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * @private
		 * Storage for the axisRenderer property.
		 */
		private var _axisRenderer:IAxisRenderer;
		
		/**
		 * @inheritDoc
		 */
		public function get axisRenderer():IAxisRenderer
		{
			return this._axisRenderer;
		}
		
		/**
		 * @private
		 */
		public function set axisRenderer(value:IAxisRenderer):void
		{
			if(this._axisRenderer != value)
			{
				this._axisRenderer = value;
				this.invalidate();
			}
		}
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
	
		/**
		 * @private
		 */
		override protected function draw():void
		{
			this.graphics.clear();
			
			if(!this.axisRenderer)
			{
				return;
			}
			
			var showLines:Boolean = this.getStyleValue("showLines") as Boolean;
			var showMinorLines:Boolean = this.getStyleValue("showMinorLines") as Boolean;
			
			//grab the line and minor line data from the axis renderer
			var lines:Array = this.axisRenderer.ticks.concat();
			var minorLines:Array = this.axisRenderer.minorTicks.concat();
			if(showMinorLines && showLines)
			{
				//filter out minor ticks that appear at the same position
				//as major ticks.
				minorLines = minorLines.filter(function(item:AxisData, index:int, source:Array):Boolean
				{
					return !lines.some(function(item2:AxisData, index2:int, source2:Array):Boolean
					{
						//using fuzzyEquals because we may encounter rounding errors
						return NumberUtil.fuzzyEquals(item.position, item2.position, 10);
					});
				});
			}
			
			
			var lineWeight:int = this.getStyleValue("lineWeight") as int;
			var lineColor:uint = this.getStyleValue("lineColor") as uint;
			var fillColors:Array = this.getStyleValue("fillColors") as Array;
			var fillAlphas:Array = this.getStyleValue("fillAlphas") as Array;
			this.drawLines(lines, showLines, lineWeight, lineColor, fillColors, fillAlphas);

			var minorLineWeight:int = this.getStyleValue("minorLineWeight") as int;
			var minorLineColor:uint = this.getStyleValue("minorLineColor") as uint;
			this.drawLines(minorLines, showMinorLines, minorLineWeight, minorLineColor);
			
			super.draw();
		}
		
		/**
		 * Draws a set of lines based on AxisData positioning.
		 */
		protected function drawLines(data:Array, showLines:Boolean, lineWeight:Number, lineColor:uint, fillColors:Array = null, fillAlphas:Array = null):void
		{	
			var lastPosition:Number;
			var renderer:ICartesianAxisRenderer = ICartesianAxisRenderer(this.axisRenderer);
			var dataCount:int = data.length;
			
			var showZeroGridLine:Boolean = this.getStyleValue("showZeroGridLine") as Boolean;
			var zeroGridLineWeight:Number = this.getStyleValue("zeroGridLineWeight") as Number;
			var zeroGridLineColor:uint = this.getStyleValue("zeroGridLineColor") as uint;
			for(var i:int = 0; i < dataCount; i++)
			{
				var axisData:AxisData = AxisData(data[i]);
				if(isNaN(axisData.position))
				{
					//skip bad positions
					continue;
				}
				
				var position:Number = axisData.position;
				var nonOriginZero:Boolean =(i > 0 && axisData.value == 0 && showZeroGridLine);
				if(renderer.orientation == AxisOrientation.VERTICAL)
				{
					if(!isNaN(lastPosition) && fillColors && fillColors.length > 0)
					{
						var color:uint = fillColors[(i - 1) % fillColors.length];
						var alpha:Number = (fillAlphas && fillAlphas.length > 0) ? fillAlphas[(i - 1) % fillAlphas.length] : 1;
						this.graphics.lineStyle(0, 0, 0);
						this.graphics.beginFill(color, alpha);
						this.graphics.drawRect(0, lastPosition, renderer.contentBounds.width, position - lastPosition);
						this.graphics.endFill();
					}
					
					if(showLines)
					{
						if(nonOriginZero)
						{
							this.graphics.lineStyle(zeroGridLineWeight, zeroGridLineColor);
						}
						else
						{
							this.graphics.lineStyle(lineWeight, lineColor);
						}
						this.graphics.moveTo(0, position);
						this.graphics.lineTo(renderer.contentBounds.width, position);
					}
				}
				else
				{
					if(!isNaN(lastPosition) && fillColors && fillColors.length > 0)
					{
						color = fillColors[(i - 1) % fillColors.length];
						alpha = (fillAlphas && fillAlphas.length > 0) ? fillAlphas[(i - 1) % fillAlphas.length] : 1;
						this.graphics.lineStyle(0, 0, 0);
						this.graphics.beginFill(color, alpha);
						this.graphics.drawRect(lastPosition, 0, position - lastPosition, renderer.contentBounds.height);
						this.graphics.endFill();
					}
					
					if(showLines)
					{
						if(nonOriginZero)
						{
							this.graphics.lineStyle(zeroGridLineWeight, zeroGridLineColor);
						}
						else
						{
							this.graphics.lineStyle(lineWeight, lineColor);
						}
						this.graphics.moveTo(position, 0);
						this.graphics.lineTo(position, renderer.contentBounds.height);
					}
				}
				
				lastPosition = position;
			}
		}
	}
}