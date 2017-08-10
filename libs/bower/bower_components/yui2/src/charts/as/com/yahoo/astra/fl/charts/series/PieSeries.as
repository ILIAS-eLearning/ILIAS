package com.yahoo.astra.fl.charts.series
{
	import com.yahoo.astra.animation.Animation;
	import com.yahoo.astra.animation.AnimationEvent;
	import com.yahoo.astra.fl.charts.PieChart;
	import com.yahoo.astra.fl.charts.legend.LegendItemData;
	import com.yahoo.astra.fl.charts.skins.RectangleSkin;
	import com.yahoo.astra.utils.GeomUtil;
	import com.yahoo.astra.utils.GraphicsUtil;
	import com.yahoo.astra.utils.NumberUtil;
	
	import fl.core.InvalidationType;
	import fl.core.UIComponent;
	
	import flash.display.Shape;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.geom.Point;
	import flash.text.TextField;
	import flash.text.TextFieldAutoSize;
	import flash.text.TextFormat;
	import flash.text.TextFormatAlign;
		
	//--------------------------------------
	//  Styles
	//--------------------------------------

	/**
     * The colors of the markers in this series.
     *
     * @default [0x729fcf, 0xfcaf3e, 0x73d216, 0xfce94f, 0xad7fa8, 0x3465a4]
     */
    [Style(name="fillColors", type="Array")]

	/**
     * If true, a label is displayed on each marker. The label text is created
     * with the labelFunction property of the series. The default label function
     * sets the label to the percentage value of the item.
     *
     * @default false
     * @see PieSeries#labelFunction
     */
    [Style(name="showLabels", type="Boolean")]

	/**
     * If true, marker labels that overlap previously-created labels will be
     * hidden to improve readability.
     *
     * @default true
     */
    [Style(name="hideOverlappingLabels", type="Boolean")]
    
	/**
	 * Renders data points as a series of pie-like wedges.
	 * 
	 * @author Josh Tynjala
	 */
	public class PieSeries extends Series implements ICategorySeries
	{
		
	//--------------------------------------
	//  Class Variables
	//--------------------------------------
		
		/**
		 * @private
		 */
		private static var defaultStyles:Object = 
		{	
			fillColors:
			[
				0x00b8bf, 0x8dd5e7, 0xc0fff6, 0xffa928, 0xedff9f, 0xd00050,
				0xc6c6c6, 0xc3eafb, 0xfcffad, 0xcfff83, 0x444444, 0x4d95dd,
				0xb8ebff, 0x60558f, 0x737d7e, 0xa64d9a, 0x8e9a9b, 0x803e77
			],
			borderColors:
			[
				0x00b8bf, 0x8dd5e7, 0xc0fff6, 0xffa928, 0xedff9f, 0xd00050,
				0xc6c6c6, 0xc3eafb, 0xfcffad, 0xcfff83, 0x444444, 0x4d95dd,
				0xb8ebff, 0x60558f, 0x737d7e, 0xa64d9a, 0x8e9a9b, 0x803e77
			],			
			fillAlphas: [1.0],
			borderAlphas: [0.0],
			markerSkins: [RectangleSkin],
			showLabels: false,
			hideOverlappingLabels: true
			//see textFormat default style defined in constructor below
			//works around stylemanager global style bug!
		};
		
		/**
		 * @private
		 */
		private static const RENDERER_STYLES:Object = 
		{
			fillColor: "fillColors",
			fillAlpha: "fillAlphas",
			borderColor: "borderColors",
			borderAlpha: "borderAlphas",
			skin: "markerSkins"
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
			return mergeStyles(defaultStyles, Series.getStyleDefinition());
		}
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function PieSeries(data:Object = null)
		{
			super(data);
			//we have to set this as an instance style because textFormat is
			//defined as a global style in StyleManager, and that takes
			//precedence over shared/class styles
			this.setStyle("textFormat", new TextFormat("_sans", 11, 0x000000, true, false, false, "", "", TextFormatAlign.LEFT, 0, 0, 0, 0));
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
		
		/**
		 * @private
		 * The text fields used to display labels over each marker.
		 */
		protected var labels:Array = [];
		
		/**
		 * @private
		 * Holds the labels created by the previous redraw so that they can
		 * be reused.
		 */
		protected var labelsCache:Array;
		
		/**
		 * @private
		 * Storage for the masks that define the shapes of the markers.
		 */
		protected var markerMasks:Array = [];
		
		/**
		 * @private
		 * The Animation instance that controls animation in this series.
		 */
		private var _animation:Animation;
		
		/**
		 * @private
		 */
		private var _previousData:Array = [];
		
		/**
		 * @private
		 * Storage for the dataField property.
		 */
		private var _dataField:String;
		
		/**
		 * The field used to access data for this series.
		 */
		public function get dataField():String
		{
			return this._dataField;
		}
		
		/**
		 * @private
		 */
		public function set dataField(value:String):void
		{
			if(this._dataField != value)
			{
				this._dataField = value;
				this.dispatchEvent(new Event("dataChange"));
				this.invalidate(InvalidationType.DATA);
			}
		}
		
		/**
		 * @private
		 * Storage for the categoryField property.
		 */
		private var _categoryField:String;
		
		/**
		 * @copy com.yahoo.astra.fl.charts.series.ICategorySeries#categoryField
		 */
		public function get categoryField():String
		{
			return this._categoryField;
		}
		
		/**
		 * @private
		 */
		public function set categoryField(value:String):void
		{
			if(this._categoryField != value)
			{
				this._categoryField = value;
				this.dispatchEvent(new Event("dataChange"));
				this.invalidate(InvalidationType.DATA);
			}
		}
		
		/**
		 * @private
		 * Storage for the categoryNames property.
		 */
		private var _categoryNames:Array;
		
		/**
		 * @copy com.yahoo.astra.fl.charts.series.ICategorySeries#categoryNames
		 */
		public function get categoryNames():Array
		{
			return this._categoryNames;
		}
		
		/**
		 * @private
		 */
		public function set categoryNames(value:Array):void
		{
			this._categoryNames = value;
		}
		
		/**
		 * @private
		 * Storage for the labelFunction property.
		 */
		private var _labelFunction:Function = defaultLabelFunction;
		
		/**
		 * A function may be set to determine the text value of the labels.
		 * 
		 * <pre>function labelFunction(item:Object):String</pre>
		 */
		public function get labelFunction():Function
		{
			return this._labelFunction;
		}
		
		/**
		 * @private
		 */
		public function set labelFunction(value:Function):void
		{
			this._labelFunction = value;
			this.invalidate();
		}
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
	
		/**
		 * @inheritDoc
		 */
		override public function clone():ISeries
		{
			var series:PieSeries = new PieSeries();
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
			
			return series;
		}
		
		/**
		 * Converts an item to it's value.
		 */
		public function itemToData(item:Object):Number
		{
			var primaryDataField:String = PieChart(this.chart).seriesToDataField(this);
			if(primaryDataField)
			{
				return Number(item[primaryDataField]);
			}
			return Number(item);
		}
		
		/**
		 * Converts an item to the category in which it is displayed.
		 */
		public function itemToCategory(item:Object, index:int):String
		{
			var primaryCategoryField:String = PieChart(this.chart).seriesToCategoryField(this);
			if(primaryCategoryField)
			{
				return item[primaryCategoryField];
			}
			
			if(this._categoryNames && index >= 0 && index < this._categoryNames.length)
			{
				return this._categoryNames[index];
			}
			return index.toString();
		}
		
		/**
		 * Converts an item's value to its percentage equivilent.
		 */
		public function itemToPercentage(item:Object):Number
		{
			var totalValue:Number = this.calculateTotalValue();
			if(totalValue == 0)
			{
				return 0;
			}
			return 100 * (this.itemToData(item) / totalValue);
		}
		
		/**
		 * @inheritDoc
		 */
		public function createLegendItemData():Array
		{
			var items:Array = [];
			var markerSkins:Array = this.getStyleValue("markerSkins") as Array;
			var fillColors:Array = this.getStyleValue("fillColors") as Array;
			var legendItemCount:int = this.length;
			for(var i:int = 0; i < legendItemCount; i++)
			{
				var item:Object = this.dataProvider[i];
				var categoryName:String = this.itemToCategory(item, i);
				var markerSkin:Object = markerSkins[i % markerSkins.length]
				var fillColor:uint = fillColors[i % fillColors.length];
				var data:LegendItemData = new LegendItemData(categoryName, markerSkin, fillColor, 1, fillColor, 1);
				items.push(data);
			}
			return items;			
		}
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
		
		/**
		 * @private
		 */
		override protected function draw():void
		{
			var stylesInvalid:Boolean = this.isInvalid(InvalidationType.STYLES);
			var sizeInvalid:Boolean = this.isInvalid(InvalidationType.SIZE);
			var dataInvalid:Boolean = this.isInvalid(InvalidationType.DATA);
			
			super.draw();
			
			this.drawMarkers(stylesInvalid, sizeInvalid);
			
			var showLabels:Boolean = this.getStyleValue("showLabels") as Boolean;
			this.createLabelCache();
			if(showLabels)
			{
				this.drawLabels();
			}
			this.clearLabelCache();
			
			this.beginAnimation();
		}
		
		/**
		 * @private
		 * All markers are removed from the display list.
		 */
		override protected function removeAllMarkers():void
		{
			super.removeAllMarkers();
			var markerCount:int = this.markerMasks.length;
			for(var i:int = 0; i < markerCount; i++)
			{
				var mask:Shape = this.markerMasks.pop() as Shape;
				this.removeChild(mask);
			}
		}
		
		/**
		 * @private
		 * Add or remove markers as needed. current markers will be reused.
		 */
		override protected function refreshMarkers():void
		{
			super.refreshMarkers();
			
			var itemCount:int = this.length;
			var difference:int = itemCount - this.markerMasks.length;
			if(difference > 0)
			{
				for(var i:int = 0; i < difference; i++)
				{
					var mask:Shape = new Shape();
					this.addChild(mask);
					this.markerMasks.push(mask);
					
					var marker:Sprite = this.markers[i + (itemCount-difference)] as Sprite;
					marker.mask = mask;
					marker.width = this.width;
					marker.height = this.height;
				}
			}
			else if(difference < 0)
			{
				difference = Math.abs(difference);
				for(i = 0; i < difference; i++)
				{
					mask = this.markerMasks.pop() as Shape;
					this.removeChild(mask);
				}
			}
		}
		
		/**
		 * @private
		 * The default function called to initialize the text on the marker
		 * labels.
		 */
		protected function defaultLabelFunction(item:Object):String
		{
			var percentage:Number = this.itemToPercentage(item);
			return (percentage < 0.01 ? "< 0.01" : NumberUtil.roundToNearest(percentage, 0.01)) + "%";
		}
		
		/**
		 * @private
		 * Draws the markers in this series.
		 */
		protected function drawMarkers(stylesInvalid:Boolean, sizeInvalid:Boolean):void
		{
			var markerCount:int = this.markers.length;
			for(var i:int = 0; i < markerCount; i++)
			{
				var marker:UIComponent = UIComponent(this.markers[i]);
				
				if(stylesInvalid)
				{
					this.copyStylesToRenderer(ISeriesItemRenderer(marker), RENDERER_STYLES);
				}
				
				if(sizeInvalid)
				{
					marker.width = this.width;
					marker.height = this.height;
				}
				//not really required, but we should validate anyway.
				this.validateMarker(ISeriesItemRenderer(marker));
			}
		}
		
		/**
		 * @private
		 * Either creates a new label TextField or retrieves one from the
		 * cache.
		 */
		protected function getLabel():TextField
		{
			var label:TextField;
			if(this.labelsCache.length > 0)
			{
				label = TextField(this.labelsCache.shift());
			}
			else
			{
				label = new TextField();
				label.autoSize = TextFieldAutoSize.LEFT;
				label.selectable = false;
				label.mouseEnabled = false;
				this.addChild(label);
			}
			label.visible = true;
			return label;
		}
		
		/**
		 * @private
		 * Updates the label text and positions the labels.
		 */
		protected function drawLabels():void
		{	
			var textFormat:TextFormat = this.getStyleValue("textFormat") as TextFormat;
			var embedFonts:Boolean = this.getStyleValue("embedFonts") as Boolean;
			var hideOverlappingLabels:Boolean = this.getStyleValue("hideOverlappingLabels") as Boolean;
			
			var angle:Number = 0;
			var valueSum:Number = 0;
			var totalValue:Number = this.calculateTotalValue();
			var markerCount:int = this.markers.length;
			for(var i:int = 0; i < markerCount; i++)
			{
				var label:TextField = this.getLabel();
				this.labels.push(label);
				label.defaultTextFormat = textFormat;
				label.embedFonts = embedFonts;
				label.text = this.labelFunction(this.dataProvider[i]);
				
				var value:Number = this.itemToData(this.dataProvider[i]);
				if(totalValue == 0)
				{
					angle = 360 / this.length;
				}
				else
				{
					angle = 360 * ((valueSum + value / 2) / totalValue);
				}
				valueSum += value;
				var halfWidth:Number = this.width / 2;
				var halfHeight:Number = this.height / 2;
				var radius:Number = Math.min(halfWidth, halfHeight);
				var position:Point = Point.polar(2 * radius / 3, -GeomUtil.degreesToRadians(angle));
				label.x = halfWidth + position.x - label.width / 2;
				label.y = halfHeight + position.y - label.height / 2;
				
				if(hideOverlappingLabels)
				{
					for(var j:int = 0; j < i; j++)
					{
						var previousLabel:TextField = TextField(this.labels[j]);
						if(previousLabel.hitTestObject(label))
						{
							label.visible = false;
						}
					}
				}
			}
		}
		
		/**
		 * Copies a styles from the series to a child through a style map.
		 * 
		 * @see copyStylesToChild()
		 */
		protected function copyStylesToRenderer(child:ISeriesItemRenderer, styleMap:Object):void
		{
			var index:int = this.markers.indexOf(child);
			var childComponent:UIComponent = child as UIComponent;
			for(var n:String in styleMap)
			{
				var styleValues:Array = this.getStyleValue(styleMap[n]) as Array;
				//if it doesn't exist, ignore it and go with the defaults for this series
				if(!styleValues) continue;
				childComponent.setStyle(n, styleValues[index % styleValues.length])
			}
		}
		
	//--------------------------------------
	//  Private Methods
	//--------------------------------------
	
		/**
		 * @private
		 * Sets up the animation for the markers.
		 */
		private function beginAnimation():void
		{
			var itemCount:int = this.length;
			if(!this._previousData || this._previousData.length != this.length)
			{
				this._previousData = [];
				for(var i:int = 0; i < itemCount; i++)
				{
					this._previousData.push(0);
				}
			}
			
			//handle animating all the markers in one fell swoop.
			if(this._animation)
			{
				if(this._animation.active)
				{
					this._animation.pause();
				}
				this._animation.removeEventListener(AnimationEvent.UPDATE, tweenUpdateHandler);
				this._animation.removeEventListener(AnimationEvent.PAUSE, tweenPauseHandler);
				this._animation.removeEventListener(AnimationEvent.COMPLETE, tweenCompleteHandler);
				this._animation = null;
			}
			
			var data:Array = this.dataProviderToArrayOfNumbers();
			
			//don't animate on livepreview!
			if(this.isLivePreview || !this.getStyleValue("animationEnabled"))
			{
				this.renderMarkerMasks(data);
			}
			else
			{
				var animationDuration:int = this.getStyleValue("animationDuration") as int;
				var animationEasingFunction:Function = this.getStyleValue("animationEasingFunction") as Function;
				
				this._animation = new Animation(animationDuration, this._previousData, data);
				this._animation.addEventListener(AnimationEvent.UPDATE, tweenUpdateHandler);
				this._animation.addEventListener(AnimationEvent.PAUSE, tweenPauseHandler);
				this._animation.addEventListener(AnimationEvent.COMPLETE, tweenCompleteHandler);
				this._animation.easingFunction = animationEasingFunction;
				this.renderMarkerMasks(this._previousData);
			}
		}
	
		/**
		 * @private
		 * Determines the total sum of all values in the data provider.
		 */
		private function calculateTotalValue():Number
		{
			var totalValue:Number = 0;
			var itemCount:int = this.length;
			for(var i:int = 0; i < itemCount; i++)
			{
				var currentItem:Object = this.dataProvider[i];
				var value:Number = this.itemToData(currentItem);
				
				if(!isNaN(value))
				{
					totalValue += value;
				}
			}
			return totalValue;
		}
	
		/**
		 * @private
		 * Retreives all the numeric values from the data provider
		 * and places them into an Array so that they may be used
		 * in an animation.
		 */
		private function dataProviderToArrayOfNumbers():Array
		{
			var output:Array = [];
			
			var itemCount:int = this.length;
			for(var i:int = 0; i < itemCount; i++)
			{
				var item:Object = this.dataProvider[i];
				var value:Number = 0;
				if(item != null)
				{
					value = this.itemToData(item);
				}
				output.push(value);
			}
			return output;
		}
	
		/**
		 * @private
		 */
		private function tweenUpdateHandler(event:AnimationEvent):void
		{
			this.renderMarkerMasks(event.parameters as Array);
		}
		
		/**
		 * @private
		 */
		private function tweenCompleteHandler(event:AnimationEvent):void
		{
			this.tweenUpdateHandler(event);
			this.tweenPauseHandler(event);
		}
		
		/**
		 * @private
		 */
		private function tweenPauseHandler(event:AnimationEvent):void
		{
			this._previousData = (event.parameters as Array).concat();
		}
	
		/**
		 * @private
		 */
		private function renderMarkerMasks(data:Array):void
		{
			var values:Array = [];
			var totalValue:Number = 0;
			var itemCount:int = data.length;
			for(var i:int = 0; i < itemCount; i++)
			{
				var value:Number = Number(data[i]);
				
				values.push(value);
				if(!isNaN(value))
				{
					totalValue += value;
				}
			}
			
			var totalAngle:Number = 0;
			var halfWidth:Number = this.width / 2;
			var halfHeight:Number = this.height / 2;
			var radius:Number = Math.min(halfWidth, halfHeight);
			var fillColors:Array = this.getStyleValue("fillColors") as Array;
			
			var angle:Number = 0;
			for(i = 0; i < itemCount; i++)
			{
				value = Number(data[i]);
				if(totalValue == 0)
				{
					angle = 360 / data.length;
				}
				else
				{
					angle = 360 * (value / totalValue);
				}
				
				var mask:Shape = this.markerMasks[i] as Shape;
				mask.graphics.clear();
				mask.graphics.beginFill(0xff0000);
				GraphicsUtil.drawWedge(mask.graphics, halfWidth, halfHeight, totalAngle, angle, radius);
				mask.graphics.endFill();
				totalAngle += angle;
				
				var marker:UIComponent = UIComponent(this.markers[i]);
				marker.drawNow();
			}
		}
		
		/**
		 * @private
		 * Places all the existing labels in a cache so that they may be reused
		 * when we redraw the series.
		 */
		private function createLabelCache():void
		{
			this.labelsCache = this.labels.concat();
			this.labels = [];
		}
		
		/**
		 * @private
		 * If any labels are left in the cache after we've redrawn, they can be
		 * removed from the display list.
		 */
		private function clearLabelCache():void
		{
			var cacheLength:int = this.labelsCache.length;
			for(var i:int = 0; i < cacheLength; i++)
			{
				var label:TextField = TextField(this.labelsCache.shift());
				this.removeChild(label);
			}
		}
		
	}
}