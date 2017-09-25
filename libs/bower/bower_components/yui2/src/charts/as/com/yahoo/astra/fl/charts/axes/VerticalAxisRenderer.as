package com.yahoo.astra.fl.charts.axes
{
	import com.yahoo.astra.display.BitmapText;
	import com.yahoo.astra.utils.DynamicRegistration;	
	import flash.geom.Point;

	/**
	 * The default vertical axis renderer for a cartesian chart.
	 * 
	 * @see com.yahoo.astra.fl.charts.CartesianChart
	 * @author Tripp Bridges
	 */
	public class VerticalAxisRenderer extends DefaultAxisRenderer implements ICartesianAxisRenderer
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------	
		public function VerticalAxisRenderer()
		{
			super();
			this.orientation = AxisOrientation.VERTICAL;
		}			

	//--------------------------------------
	//  Protected Methods
	//--------------------------------------

		/**
		 * @private
		 * Positions the title along the axis.
		 */
		override protected function positionTitle():void
		{
			var showTitle:Boolean = this.getStyleValue("showTitle") as Boolean;
			this.titleTextField.visible = showTitle;
			if(showTitle)
			{
				var titleRotation:Number = this.getStyleValue("titleRotation") as Number;
				titleRotation = Math.max(-90, Math.min(titleRotation, 90));

				this.titleTextField.rotation = titleRotation;
				if(this.position == "right")
				{
					this.titleTextField.y = this.contentBounds.y + (this.contentBounds.height) / 2;
					this.titleTextField.x = this.width - this.titleTextField.width;
				}
				else
				{
					this.titleTextField.y = this.contentBounds.y + (this.contentBounds.height) / 2;
					this.titleTextField.x = 0;
				}
				if(titleRotation > 0)
				{	
					this.titleTextField.x += this.titleTextField.contentHeight * Math.sin(titleRotation * Math.PI/180);
					this.titleTextField.y -= this.titleTextField.height/2;
				}
				else if(titleRotation < 0)
				{							
					this.titleTextField.y += this.titleTextField.height/2;
				}
				else
				{
					this.titleTextField.y -= this.titleTextField.height/2;
				}
			}
		}

		/**
		 * @private
		 * Draws the axis origin line.
		 */
		override protected function drawAxis():void
		{
			super.drawAxis();
			var verticalX:Number = this.contentBounds.x;
			if(this.position == "right")
			{
				verticalX = this.contentBounds.x + this.contentBounds.width;
			}
			var verticalStart:Number = this.contentBounds.y;
			var verticalEnd:Number = this.contentBounds.y + this.contentBounds.height;
			this.graphics.moveTo(verticalX, verticalStart);
			this.graphics.lineTo(verticalX, verticalEnd);
		}	
		
		/**
		 * @private
		 * Draws a set of ticks on the axis.
		 */
		override protected function drawTicks(data:Array, showTicks:Boolean, tickPosition:String,
			tickLength:Number, tickWeight:Number, tickColor:uint):void
		{
			if(!showTicks)
			{
				return;
			}
			
			this.graphics.lineStyle(tickWeight, tickColor);

			var axisPosition:Number = this.position == "right" ? this.contentBounds.x + this.contentBounds.width : this.contentBounds.x;
			if(this.position == "right") tickLength *= -1;
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
				position += this.contentBounds.y;

				switch(tickPosition)
				{
					case TickPosition.OUTSIDE:
						this.graphics.moveTo(axisPosition - tickLength, position);
						this.graphics.lineTo(axisPosition, position);
						break;
					
					case TickPosition.INSIDE:
						this.graphics.moveTo(axisPosition, position);
						this.graphics.lineTo(axisPosition + tickLength, position);
						break;
					
					default: //CROSS
						this.graphics.moveTo(axisPosition - tickLength / 2, position);
						this.graphics.lineTo(axisPosition + tickLength / 2, position);
						break;
				}
			}
		}
		
		/**
		 * @private
		 * Positions a set of labels on the axis.
		 */
		override protected function positionLabels(labels:Array, showLabels:Boolean, labelDistance:Number, labelRotation:Number, embedFonts:Boolean):void
		{
			if(!showLabels) return;
			var labelCount:int = this.labelTextFields.length;
			for(var i:int = 0; i < labelCount; i++)
			{
				var label:BitmapText = BitmapText(this.labelTextFields[i]);
				label.rotation = 0;
				var axisData:AxisData = AxisData(this.ticks[i]);
				var position:Number = axisData.position;
				position += this.contentBounds.y;		
				var absRotation:Number = Math.abs(labelRotation);
				var xRegistration:Number;
				label.y = position;

				if(this.position == "left")
				{
					label.x = this.contentBounds.x - labelDistance - this.outerTickOffset;
					xRegistration = label.width - Math.min(label.height/2, Math.sin(absRotation * Math.PI/180) * label.height/4);
					if(absRotation > 0 && absRotation < 90)
					{
						label.y -= label.height/2;
						label.x -= label.width;
						DynamicRegistration.rotate(label, new Point(xRegistration, label.height / 2), labelRotation);
					}
					else if(labelRotation == 90)
					{
						label.rotation = labelRotation;
						label.y -= label.height/2;
					}
					else if(labelRotation == -90)
					{
						label.rotation = labelRotation;
						label.x -= label.width;
						label.y += label.height/2;
					}
					else
					{
						label.x -= label.width;
						label.y -= label.height/2;
					}
				}
				else
				{
					label.x = this.contentBounds.x + this.contentBounds.width + labelDistance + this.outerTickOffset;
					xRegistration = Math.min(label.height/2, Math.sin(absRotation * Math.PI/180) * label.height/4);			
					if(absRotation > 0 && absRotation < 90)
					{
						label.y -= label.height/2;
						DynamicRegistration.rotate(label, new Point(xRegistration, label.height / 2), labelRotation);
					}
					else if(labelRotation == 90)
					{
						label.rotation = labelRotation;
						label.y -= label.height/2;
						label.x += label.width; 						
					}
					else if(labelRotation == -90)
					{
						DynamicRegistration.rotate(label, new Point(xRegistration, label.height / 2), labelRotation);
						label.y += label.height/2;
					}	
					else
					{
						label.y -= label.height/2;
					}						
				}
				
				label.x = Math.round(label.x);
				label.y = Math.round(label.y);
				this.handleOverlappingLabels();
			}
		}
		
		/**
		 * @private
		 * If labels overlap, some may need to be hidden.
		 */
		override protected function handleOverlappingLabels():void
		{
			var showLabels:Boolean = this.getStyleValue("showLabels");
			var hideOverlappingLabels:Boolean = this.getStyleValue("hideOverlappingLabels");
			if(!showLabels || !hideOverlappingLabels)
			{
				return;
			}
			var labelRotation:Number = this.getStyleValue("labelRotation") as Number;
			var lastVisibleLabel:BitmapText;
 			var labelCount:int = this.labelTextFields.length;
			for(var i:int = 0; i < labelCount; i++)
			{
				var idealDistance:Number;
				var index:int = labelRotation >= 0 ? i : (labelCount - i - 1);
				var label:BitmapText = BitmapText(this.labelTextFields[index]);
				label.visible = true;
				if(lastVisibleLabel)
				{
					var diff:Number;
					var offset:Point;
					offset = new Point(0, 0);
					var radians:Number = Math.abs(labelRotation) * Math.PI/180;
					if(lastVisibleLabel.y > label.y)
					{	
						if(Math.abs(labelRotation) == 90)
						{
							idealDistance = label.textField.textWidth;
						}
						else if(labelRotation == 0)
						{
							idealDistance = label.textField.textHeight;
						}
						else
						{
							idealDistance = (label.textField.textHeight / (Math.cos((Math.abs(labelRotation))*Math.PI/180)));
							idealDistance = Math.min(idealDistance, label.height);
						}
						if((label.y + label.height + idealDistance) > (lastVisibleLabel.y + lastVisibleLabel.height))
						{
							label.visible = false;								
						}
					}
					else
					{
						if(Math.abs(labelRotation) == 90)
						{
							idealDistance = lastVisibleLabel.textField.textWidth;
						}
						else if(labelRotation == 0)
						{
							idealDistance = lastVisibleLabel.textField.textHeight;
						}
						else
						{
							idealDistance = (lastVisibleLabel.textField.textHeight / (Math.cos((Math.abs(labelRotation))*Math.PI/180)));
							idealDistance = Math.min(idealDistance, lastVisibleLabel.height);
						}							
						if((lastVisibleLabel.y + lastVisibleLabel.height + idealDistance) > (label.y + label.height)) 
						{
							label.visible = false;
						}		
					}
				}
				if(label.visible)
				{
					lastVisibleLabel = label;
				}  
			}
		}		
			
				
	}
}