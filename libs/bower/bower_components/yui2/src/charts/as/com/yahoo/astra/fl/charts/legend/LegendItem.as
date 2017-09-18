package com.yahoo.astra.fl.charts.legend
{
	import com.yahoo.astra.fl.charts.events.LegendEvent;
	import com.yahoo.astra.fl.charts.series.ISeriesItemRenderer;
	import com.yahoo.astra.fl.charts.series.SeriesItemRenderer;
	
	import fl.core.InvalidationType;
	import fl.core.UIComponent;
	import fl.events.ComponentEvent;
	
	import flash.display.DisplayObject;
	import flash.display.InteractiveObject;
	import flash.display.Sprite;
	import flash.events.MouseEvent;
	import flash.text.TextField;
	import flash.text.TextFieldAutoSize;
	import flash.text.TextFormat;

	/**
	 * An item displayed in a chart's Legend.
	 * 
	 * @see com.yahoo.astra.fl.charts.legend.Legend
	 * 
	 * @author Josh Tynjala
	 */
	public class LegendItem extends UIComponent
	{
		
	//--------------------------------------
	//  Class Variables
	//--------------------------------------
	
		/**
		 * @private
		 */
		private static var defaultStyles:Object = 
		{
			markerSkin: Sprite,
			horizontalSpacing: 3,
			embedFonts: false
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
			return mergeStyles(defaultStyles, UIComponent.getStyleDefinition());
		}
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function LegendItem()
		{
			super();
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * @private
		 * The marker displayed in this LegendItem.
		 */
		protected var marker:ISeriesItemRenderer;
		
		/**
		 * @private
		 * The label displayed in this LegendItem.
		 */
		protected var textField:TextField;
	
		/**
		 * @private
		 * Storage for the data property.
		 */
		private var _data:LegendItemData;
		
		/**
		 * The data used to display the legend item.
		 */
		public function get data():LegendItemData
		{
			return this._data;
		}
		
		/**
		 * @private
		 */
		public function set data(value:LegendItemData):void
		{
			if(this._data != value)
			{
				this._data = value;
				this.invalidate(InvalidationType.DATA);
			}
		}
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
	
		/**
		 * @private
		 */
		override protected function configUI():void
		{
			super.configUI();
		
			if(!this.marker)
			{
				this.marker = new SeriesItemRenderer();
				InteractiveObject(this.marker).doubleClickEnabled = true;
				this.marker.addEventListener(MouseEvent.CLICK, markerMouseEventHandler);
				this.marker.addEventListener(MouseEvent.DOUBLE_CLICK, markerMouseEventHandler);
				this.marker.addEventListener(MouseEvent.ROLL_OVER, markerMouseEventHandler);
				this.marker.addEventListener(MouseEvent.ROLL_OUT, markerMouseEventHandler);
				this.addChild(DisplayObject(this.marker));
			}
			
			if(!this.textField)
			{
				this.textField = new TextField();
				this.textField.autoSize = TextFieldAutoSize.LEFT;
				this.addChild(this.textField);
			}
		}
		
		/**
		 * @private
		 */
		override protected function draw():void
		{
			var oldWidth:Number = this.width;
			var oldHeight:Number = this.height;
			
			var dataInvalid:Boolean = this.isInvalid(InvalidationType.DATA);
			var stylesInvalid:Boolean = this.isInvalid(InvalidationType.STYLES);
			
			if(stylesInvalid)
			{
				if(this.marker)
				{
					UIComponent(this.marker).setStyle("skin", this.data.markerSkin);
					UIComponent(this.marker).setStyle("fillColor", this.data.fillColor);
					UIComponent(this.marker).setStyle("borderColor", this.data.borderColor);
					UIComponent(this.marker).setStyle("fillAlpha", this.data.fillAlpha);
					UIComponent(this.marker).setStyle("borderAlpha", this.data.borderAlpha);		
				}
				
				var textFormat:TextFormat = this.getStyleValue("textFormat") as TextFormat;
				var embedFonts:Boolean = this.getStyleValue("embedFonts") as Boolean;
				this.textField.setTextFormat(textFormat); //set format for existing text
				this.textField.defaultTextFormat = textFormat; //set format for future text
				this.textField.embedFonts = embedFonts;
			}
			
			if(dataInvalid)
			{
				this.textField.text = this.data.label ? this.data.label : " "; //space used for height calculation if no data
			}
			
			//position children
			var horizontalSpacing:Number = this.getStyleValue("horizontalSpacing") as Number;
			var xPosition:Number = 0;
			if(this.marker)
			{
				var marker:UIComponent = UIComponent(this.marker);
				marker.width = marker.height = this.textField.textHeight;
				marker.y = (this.textField.height - this.textField.textHeight) / 2;
				marker.drawNow();
				xPosition = marker.width + horizontalSpacing;
			}
			this.textField.x = xPosition;
			
			this._width = this.textField.x + this.textField.width;
			this._height = this.textField.height;
			
			if(oldWidth != this._width || oldHeight != this._height)
			{
				this.dispatchEvent(new ComponentEvent(ComponentEvent.RESIZE));
			}
			
			super.draw();
		}
		
		/**
		 * @private
		 * Dispatch events when the user interacts with the marker.
		 */
		protected function markerMouseEventHandler(event:MouseEvent):void
		{
			var type:String = LegendEvent.LEGEND_MARKER_CLICK;
			switch(event.type)
			{
				case MouseEvent.DOUBLE_CLICK:
					type = LegendEvent.LEGEND_MARKER_DOUBLE_CLICK;
					break;
				case MouseEvent.ROLL_OVER:
					type = LegendEvent.LEGEND_MARKER_ROLL_OVER;
					break;
				case MouseEvent.ROLL_OUT:
					type = LegendEvent.LEGEND_MARKER_ROLL_OUT;
					break;
			}
			
			this.dispatchEvent(new LegendEvent(type, this.parent.getChildIndex(this) + 1, true, false));
		}
		
	}
}
