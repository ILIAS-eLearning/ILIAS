package com.yahoo.astra.fl.charts
{
	import com.yahoo.astra.fl.utils.UIComponentUtil;
	
	import fl.core.InvalidationType;
	import fl.core.UIComponent;
	
	import flash.display.DisplayObject;
	import flash.text.TextField;
	import flash.text.TextFieldAutoSize;
	import flash.text.TextFormat;

	//--------------------------------------
	//  Styles
	//--------------------------------------
	
	/**
     * The padding that separates the border of the component from its contents,
     * in pixels.
     *
     * @default 6
     */
    [Style(name="contentPadding", type="Number")]
	
	/**
     * The DisplayObject subclass used to display the background.
     */
    [Style(name="backgroundSkin", type="Class")]
    
	/**
	 * The default renderer for mouse-over data tips.
	 * 
	 * @author Josh Tynjala
	 */
	public class DataTipRenderer extends UIComponent implements IDataTipRenderer
	{
		
	//--------------------------------------
	//  Class Variables
	//--------------------------------------
	
		/**
		 * @private
		 */
		private static var defaultStyles:Object = 
		{
			contentPadding: 6,
			backgroundSkin: "ChartDataTipBackground",
			embedFonts: false
		}
		
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
		public function DataTipRenderer()
		{
			super();
			this.mouseEnabled = false;
			this.mouseChildren = false;
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
		
		/**
		 * @private
		 */
		protected var label:TextField;
		
		/**
		 * @private
		 */
		protected var background:DisplayObject;
		
		/**
		 * @private
		 * Storage for the text property.
		 */
		private var _text:String = "";
		
		/**
		 * @copy com.yahoo.astra.fl.charts.IDataTipRenderer#text
		 */
		public function get text():String
		{
			return this._text;
		}
		
		/**
		 * @private
		 */
		public function set text(value:String):void
		{
			if(value == null) value = "";
			if(this._text != value)
			{
				this._text = value;
				this.invalidate(InvalidationType.DATA);
			}
		}
		
		/**
		 * @private
		 * Storage for the data property.
		 */
		private var _data:Object;
		
		/**
		 * @copy com.yahoo.astra.fl.charts.IDataTipRenderer#data
		 */
		public function get data():Object
		{
			return this._data;
		}
		
		/**
		 * @private
		 */
		public function set data(value:Object):void
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
			this.label = new TextField();
			this.label.autoSize = TextFieldAutoSize.LEFT;
			this.label.selectable = false;
			this.addChild(this.label);
		}
	
		/**
		 * @private
		 */
		override protected function draw():void
		{
			var stylesInvalid:Boolean = this.isInvalid(InvalidationType.STYLES);
			var dataInvalid:Boolean = this.isInvalid(InvalidationType.DATA);
			
			var contentPadding:Number = this.getStyleValue("contentPadding") as Number;
				
			if(stylesInvalid)
			{
				if(this.background)
				{
					this.removeChild(this.background);
					this.background = null;
				}
				var skinClass:Object = this.getStyleValue("backgroundSkin");
				this.background = UIComponentUtil.getDisplayObjectInstance(this, skinClass);
				this.addChildAt(this.background, 0);
				
				var format:TextFormat = this.getStyleValue("textFormat") as TextFormat;
				var embedFonts:Boolean = this.getStyleValue("embedFonts") as Boolean;
				this.label.defaultTextFormat = format;
				this.label.embedFonts = embedFonts;
				
				this.label.x = contentPadding;
				this.label.y = contentPadding;
			}
			
			if(dataInvalid)
			{
				this.label.text = this.text;
			}
			
			//the datatip sizes itself!
			this._width = this.label.width + 2 * contentPadding;
			this._height = this.label.height + 2 * contentPadding;
			
			if(this.background)
			{
				this.background.width = this._width;
				this.background.height = this._height;
				
				if(this.background is UIComponent)
				{
					UIComponent(this.background).drawNow();
				}
			}
			
			super.draw();
		}
	
	}
}