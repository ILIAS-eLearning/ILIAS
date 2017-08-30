package com.yahoo.astra.fl.charts.axes
{
	import com.yahoo.astra.display.BitmapText;
	import com.yahoo.astra.utils.DynamicRegistration;	
	import flash.geom.Point;

	/**
	 * The default horizontal axis renderer for a cartesian chart.
	 * 
	 * @see com.yahoo.astra.fl.charts.CartesianChart
	 * @author Tripp Bridges
	 */
	public class HorizontalAxisRenderer extends DefaultAxisRenderer implements ICartesianAxisRenderer
	{

	//--------------------------------------
	//  Constructor
	//--------------------------------------	

		/**
		 * Constructor
		 */
		public function HorizontalAxisRenderer()
		{
			super();
			this.orientation = AxisOrientation.HORIZONTAL
		}
		
	//--------------------------------------
	// Properties
	//--------------------------------------
		/**
	 	 * @private
	 	 * Placeholder for position.
	 	 */		
		private var _position:String = "left";
		
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
				this.titleTextField.x = this.contentBounds.x + (this.contentBounds.width/2);
				
				if(this.position != "top")
				{
					this.titleTextField.y = this.y + this.height - this.titleTextField.height;
				}
				if(titleRotation < 0)
				{
					this.titleTextField.y += this.titleTextField.contentWidth * Math.sin(Math.abs(titleRotation)*Math.PI/180);
				}
				this.titleTextField.x -= this.titleTextField.width/2;						
			}
		}
		
		/**
		 * @private
		 * Draws the axis origin line.
		 */
		override protected function drawAxis():void
		{
			super.drawAxis();
			var horizontalY:Number = this.position == "top" ? this.contentBounds.y : this.contentBounds.y + this.contentBounds.height;
			var horizontalStart:Number = this.contentBounds.x;
			var horizontalEnd:Number = this.contentBounds.x + this.contentBounds.width;
			this.graphics.moveTo(horizontalStart, horizontalY);
			this.graphics.lineTo(horizontalEnd, horizontalY);
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
			var dataCount:int = data.length;
			var axisPosition:Number = this.position == "top" ? this.contentBounds.y : this.contentBounds.y + this.contentBounds.height;
			if(this.position == "top") tickLength *= -1;			
			for(var i:int = 0; i < dataCount; i++)
			{
				var axisData:AxisData = AxisData(data[i]);
				if(isNaN(axisData.position))
				{
					//skip bad positions
					continue;
				}
				
				var position:Number = axisData.position;
				position += this.contentBounds.x;
				
				switch(tickPosition)
				{
					case TickPosition.OUTSIDE:
						this.graphics.moveTo(position, axisPosition);
						this.graphics.lineTo(position, axisPosition + tickLength);
						break;
						
					case TickPosition.INSIDE:
						this.graphics.moveTo(position, axisPosition - tickLength);
						this.graphics.lineTo(position, axisPosition);
						break;

					default: //CROSS
						this.graphics.moveTo(position, axisPosition - tickLength / 2);
						this.graphics.lineTo(position, axisPosition + tickLength / 2);
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
			if(!showLabels)	return;		
			var labelCount:int = this.labelTextFields.length;
			for(var i:int = 0; i < labelCount; i++)
			{
				var label:BitmapText = BitmapText(this.labelTextFields[i]);
				label.rotation = 0;
				var axisData:AxisData = AxisData(this.ticks[i]);
				var position:Number = axisData.position;
				position += this.contentBounds.x;
				var absRotation:Number = Math.abs(labelRotation);	
				label.x = position;
				var xRegistration:Number;
				var yRegistration:Number = 0;
				if(this.position == "top")
				{
					label.y = this.contentBounds.y - labelDistance - this.outerTickOffset;
					if(labelRotation > 0)
					{
						label.rotation = labelRotation;
						label.x -= Math.cos(labelRotation * Math.PI/180) * label.contentWidth;
						label.x += Math.sin(labelRotation * Math.PI/180) * label.contentHeight/2;
						label.y -= Math.sin(labelRotation * Math.PI/180) * label.contentWidth;
						label.y -= Math.cos(labelRotation * Math.PI/180) * label.contentHeight * (1 - labelRotation/90);
					}
					else if(labelRotation < 0)
					{
						label.y -= Math.cos(Math.abs(labelRotation) * Math.PI/180) * label.contentHeight * (1 - absRotation/90);
						label.x -= Math.sin(Math.abs(labelRotation) * Math.PI/180) * label.contentHeight/2;
						label.rotation = labelRotation;
					}
					else
					{
						label.y -= label.height;
						label.x = position - label.width / 2;
					}
				}
				else
				{
					label.y = this.contentBounds.y + this.contentBounds.height + labelDistance + this.outerTickOffset;
					if(labelRotation > 0)
					{
						label.x = position;
						label.y -= (label.height * labelRotation / 180);
						DynamicRegistration.rotate(label, new Point(0, label.height / 2), labelRotation);
					}
					else if(labelRotation < 0)
					{
						label.x = position - label.width;
						label.y -= (label.height * Math.abs(labelRotation) / 180);
						DynamicRegistration.rotate(label, new Point(label.width, label.height / 2), labelRotation);
					}
					else //labelRotation == 0
					{
						label.x = position - label.width / 2;
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
					var maxWidth:Number;
					if(labelRotation >= 0)
					{
						diff = Math.abs(label.x - lastVisibleLabel.x);
						maxWidth = lastVisibleLabel.rotationWidth;
						if(labelRotation == 90)
						{
							idealDistance = lastVisibleLabel.textField.textHeight;
						}
						else if(labelRotation == 0)
						{
							idealDistance = lastVisibleLabel.textField.textWidth;
						}
						else
						{
							idealDistance = lastVisibleLabel.textField.textHeight / (Math.sin((Math.abs(labelRotation))*Math.PI/180));
							idealDistance = Math.min(idealDistance, lastVisibleLabel.width);
						}
					}
					else
					{
						diff = (lastVisibleLabel.x + lastVisibleLabel.width) - (label.x + label.width);
						maxWidth = label.rotationWidth;
						if(labelRotation == 90)
						{
							idealDistance = label.textField.textHeight;
						}
						else if(labelRotation == 0)
						{
							idealDistance = label.textField.textWidth;
						}
						else
						{
							idealDistance = label.textField.textHeight / (Math.sin((Math.abs(labelRotation))*Math.PI/180));
							idealDistance = Math.min(idealDistance, label.width);
						}							
					}
					if(idealDistance > diff)
					{						
						label.visible = false; 
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