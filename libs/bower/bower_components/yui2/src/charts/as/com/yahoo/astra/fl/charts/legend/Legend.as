package com.yahoo.astra.fl.charts.legend
{
	import com.yahoo.astra.fl.utils.UIComponentUtil;
	import com.yahoo.astra.layout.modes.FlowLayout;
	
	import fl.core.InvalidationType;
	import fl.core.UIComponent;
	import fl.events.ComponentEvent;
	
	import flash.display.DisplayObject;
	import flash.geom.Rectangle;

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
     * The spacing that separates the each legend item.
     *
     * @default 6
     */
    [Style(name="gap", type="Number")]
	
	/**
     * The DisplayObject subclass used to display the background.
     */
    [Style(name="backgroundSkin", type="Class")]
	
	/**
	 * Indicates whether embedded font outlines are used to render the text
	 * field. If this value is true, Flash Player renders the text field by
	 * using embedded font outlines. If this value is false, Flash Player
	 * renders the text field by using device fonts.
	 * 
	 * If you set the embedFonts property to true for a text field, you must
	 * specify a font for that text by using the font property of a TextFormat
	 * object that is applied to the text field. If the specified font is not
	 * embedded in the SWF file, the text is not displayed.
	 * 
	 * @default false
     */
    [Style(name="embedFonts", type="Boolean")]
	
	/**
	 * Provides a visual reference for the series in a Chart component.
	 * 
	 * @see com.yahoo.astra.fl.charts.Chart
	 * @see com.yahoo.astra.fl.charts.legend.LegendItem
	 * 
	 * @author Josh Tynjala
	 */
	public class Legend extends UIComponent implements ILegend
	{	
		
	//--------------------------------------
	//  Static Variables
	//--------------------------------------
	
		/**
		 * @private
		 */
		private static var defaultStyles:Object = 
		{
			backgroundSkin: "ChartLegendBackground",
			contentPadding: 6,
			direction: "vertical",
			gap: 6,
			embedFonts: false
		};
		
		/**
		 * @private
		 * Styles to pass to the LegendItems
		 */
		private static const ITEM_STYLES:Object =
		{
			textFormat: "textFormat",
			embedFonts: "embedFonts"
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
			return mergeStyles(defaultStyles, UIComponent.getStyleDefinition());
		}
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function Legend()
		{
			super();
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * @private
		 * The background skin.
		 */
		protected var background:DisplayObject;
	
		/**
		 * @private
		 * The legend items displayed in this Legend.
		 */
		protected var legendItems:Array = [];
		
		/**
		 * @private
		 * Caches LegendItems for reuse when redrawing the Legend.
		 */
		private var _legendItemCache:Array;
		
		/**
		 * @private
		 * Storage for the dataProvider property.
		 */
		private var _dataProvider:Array = [];
		
		/**
		 * @inheritDoc
		 */
		public function get dataProvider():Array
		{
			return this._dataProvider;
		}
		
		/**
		 * @private
		 */
		public function set dataProvider(value:Array):void
		{
			this._dataProvider = value;
			this.invalidate(InvalidationType.DATA);
		}
		
		
		/**
		 * @private
		 * Placeholder for maxWidth
		 */
		private var _maxWidth:Number = 0;
		
		/**
		 * @copy com.yahoo.astra.fl.charts.legend.ILegend#maxWidth
		 */
		public function get maxWidth():Number
		{
			return _maxWidth;
		}
		
		/** 
		 * @private (setter)
		 */
		public function set maxWidth(value:Number):void
		{
			_maxWidth = value;
		}
		
		/**
		 * @private
		 * Placeholder for maxHeight 
		 */
		private var _maxHeight:Number = 0;
		
		/** 
		 * @copy com.yahoo.astra.fl.charts.legend.ILegend#maxHeight
		 */
		public function get maxHeight():Number
		{
			return _maxHeight;
		}
		
		/** 
		 * @private (setter)
		 */
		public function set maxHeight(value:Number):void
		{
			_maxHeight = value;
		}				
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
		
		/**
		 * @private
		 */
		override protected function draw():void
		{
			var dataInvalid:Boolean = this.isInvalid(InvalidationType.DATA);
			var stylesInvalid:Boolean = this.isInvalid(InvalidationType.STYLES);
			
			super.draw();
			
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
			}
			
			if(dataInvalid && this.dataProvider)
			{
				this.createCache();
				this.updateLegendItems();
				this.clearCache();
			}
			
			this.layoutItems();
			
			if(this.background)
			{
				this.background.width = this._width;
				this.background.height = this._height;
				
				if(this.background is UIComponent)
				{
					UIComponent(this.background).drawNow();
				}
			}
		}
		
		/**
		 * @private
		 * Loops through the data provider and displays a LegendItem
		 * for each item.
		 */
		protected function updateLegendItems():void
		{
			var itemCount:int = this.dataProvider.length;
			for(var i:int = 0; i < itemCount; i++)
			{
				var legendItem:LegendItem = this.getItem();
				legendItem.data = LegendItemData(dataProvider[i]);
				this.copyStylesToChild(legendItem, ITEM_STYLES);
				legendItem.drawNow();
				this.legendItems.push(legendItem);
			}
		}
		
		/**
		 * @private
		 * Standard renderer caching system.
		 */
		protected function createCache():void
		{
			this._legendItemCache = this.legendItems.concat();
			this.legendItems = [];
		}
		
		/**
		 * @private
		 * Either returns an old renderer from the cache or creates a new one.
		 */
		protected function getItem():LegendItem
		{
			if(this._legendItemCache.length > 0)
			{
				return this._legendItemCache.shift() as LegendItem;
			}
			var legendItem:LegendItem = new LegendItem();
			this.addChild(legendItem);
			return legendItem;
		}
		
		/**
		 * @private
		 * Clears any unused renderers from the cache.
		 */
		protected function clearCache():void
		{
			var cacheLength:int = this._legendItemCache.length;
			for(var i:int = 0; i < cacheLength; i++)
			{
				var legendItem:LegendItem = this._legendItemCache.pop() as LegendItem;
				this.removeChild(legendItem);
			}
		}
		
		/**
		 * @private
		 * Positions the LegendItems.
		 */
		protected function layoutItems():void
		{
			var oldWidth:Number = this._width;
			var oldHeight:Number = this._height;
			
			var contentPadding:Number = this.getStyleValue("contentPadding") as Number;
			var direction:String = this.getStyleValue("direction") as String;

			var gap:Number = this.getStyleValue("gap") as Number;	
			var bounds:Rectangle;
			
			var layout:FlowLayout = new FlowLayout();
			layout.verticalGap = layout.horizontalGap = gap;
			layout.direction = direction;
			layout.paddingTop = layout.paddingRight = layout.paddingBottom = layout.paddingLeft = contentPadding;
			if(layout.direction == "vertical") 
			{
				layout.verticalAlign = "middle";
				bounds = layout.layoutObjects(this.legendItems, new Rectangle(0, 0, this.width, this.maxHeight));
			}
			if(layout.direction == "horizontal") 
			{
				layout.horizontalAlign = "center";
				bounds = layout.layoutObjects(this.legendItems, new Rectangle(0, 0, this.maxWidth, this.height));
			}
					
			this._width = bounds.width;
			this._height = bounds.height;
			
			//if the size has changed, dispatch a resize event
			if(this._width != oldWidth || this._height != oldHeight)
			{
				this.dispatchEvent(new ComponentEvent(ComponentEvent.RESIZE));
			}
		}		
	}
}
