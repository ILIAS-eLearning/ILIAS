package com.yahoo.astra.fl.charts.axes
{	
	import com.yahoo.astra.utils.GeomUtil;
	import com.yahoo.astra.utils.NumberUtil;
	import com.yahoo.astra.display.BitmapText;
	
	import com.yahoo.astra.utils.DynamicRegistration;
	
	import fl.core.InvalidationType;
	import fl.core.UIComponent;
	
	import flash.geom.Point;
	import flash.geom.Rectangle;
	import flash.text.TextField;
	import flash.text.TextFieldAutoSize;
	import flash.text.TextFormat;
	import flash.text.TextFormatAlign;
	
	//--------------------------------------
	//  Styles
	//--------------------------------------
    
    //-- Axis
    
	/**
	 * If false, the axis is not drawn. Titles, labels, ticks, and grid
	 * lines may still be drawn, however, so you must specifically hide each
	 * item if nothing should be drawn.
	 * 
	 * @default true
	 */
	[Style(name="showAxis", type="Boolean")]
    
	/**
	 * The line weight, in pixels, for the axis.
	 * 
	 * @default 1
	 */
	[Style(name="axisWeight", type="int")]
    
	/**
	 * The line color for the axis.
	 * 
	 * @default #888a85
	 */
	[Style(name="axisColor", type="uint")]
    
    //-- Labels
    
	/**
	 * If true, labels will be displayed on the axis.
	 * 
	 * @default true
	 */
	[Style(name="showLabels", type="Boolean")]
    
	/**
	 * The distance, in pixels, between a label and the axis.
	 * 
	 * @default 2
	 */
	[Style(name="labelDistance", type="Number")]
    
	/**
	 * The distance, in pixels, between a title and the axis labels.
	 * 
	 * @default 2
	 */
	[Style(name="titleDistance", type="Number")]
	
	/** 
	 * If true, labels that overlap previously drawn labels on the axis will be
	 * hidden. The first and last labels on the axis will always be drawn.
	 * 
	 * @default true
	 */
	[Style(name="hideOverlappingLabels", type="Boolean")]
    
	/** 
	 * The angle, in degrees, of the labels on the axis. May be a value
	 * between <code>-90</code> and <code>90</code>. 
	 * 
	 * @default 0
	 */
	[Style(name="labelRotation", type="Number")]
	
	/** 
	 * The angle, in degrees, of the title on the axis. May be a value
	 * between <code>-90</code> and <code>90</code>. 
	 * 
	 * @default 0
	 */
	[Style(name="titleRotation", type="Number")]	
	
	//-- Ticks
    
	/**
	 * If true, ticks will be displayed on the axis.
	 * 
	 * @default true
	 */
	[Style(name="showTicks", type="Boolean")]
    
	/**
	 * The line weight, in pixels, for the ticks on the axis.
	 * 
	 * @default 1
	 */
	[Style(name="tickWeight", type="int")]
    
	/**
	 * The line color for the ticks on the axis.
	 * 
	 * @default #888a85
	 */
	[Style(name="tickColor", type="uint")]
    
	/**
	 * The length, in pixels, of the ticks on the axis.
	 * 
	 * @default 4
	 */
	[Style(name="tickLength", type="Number")]
	
	/**
	 * The position of the ticks on the axis.
	 * 
	 * @default "cross"
	 * @see TickPosition
	 */
	[Style(name="tickPosition", type="String")]
    
    //-- Minor ticks
    
	/**
	 * If true, ticks will be displayed on the axis at minor positions.
	 * 
	 * @default true
	 */
	[Style(name="showMinorTicks", type="Boolean")]
	
	/**
	 * The line weight, in pixels, for the minor ticks on the axis.
	 * 
	 * @default 1
	 */
	[Style(name="minorTickWeight", type="int")]
    
	/**
	 * The line color for the minor ticks on the axis.
	 * 
	 * @default #888a85
	 */
	[Style(name="minorTickColor", type="uint")]
    
	/**
	 * The length of the minor ticks on the axis.
	 * 
	 * @default 3
	 */
	[Style(name="minorTickLength", type="Number")]
	
	/**
	 * The position of the minor ticks on the axis.
	 * 
	 * @default "outside"
	 * @see com.yahoo.astra.fl.charts.TickPosition
	 */
	[Style(name="minorTickPosition", type="String")]
	
	//-- Title
	
	/**
	 * If true, the axis title will be displayed.
	 * 
	 * @default 2
	 */
	[Style(name="showTitle", type="Boolean")]
	
	/**
	 * The TextFormat object to use to render the axis title label.
     *
     * @default TextFormat("_sans", 11, 0x000000, false, false, false, '', '', TextFormatAlign.LEFT, 0, 0, 0, 0)
	 */
	[Style(name="titleTextFormat", type="TextFormat")]

	/**
	 * The base axis renderer for a cartesian chart.
	 * 
     * <p>This class is not meant to be instantiated directly! It is an abstract base class.</p>
     *
	 * @see com.yahoo.astra.fl.charts.CartesianChart
	 * @author Josh Tynjala
	 */
	public class DefaultAxisRenderer extends UIComponent implements ICartesianAxisRenderer
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
			
			//labels
			showLabels: true,
			labelDistance: 2,
			embedFonts: false,
			hideOverlappingLabels: true,
			labelRotation: 0,
			titleRotation: 0,
			titleDistance: 2,
			
			//ticks
			showTicks: true,
			tickWeight: 1,
			tickColor: 0x888a85,
			tickLength: 4,
			tickPosition: TickPosition.CROSS,
			
			//minor ticks
			showMinorTicks: true,
			minorTickWeight: 1,
			minorTickColor: 0x888a85,
			minorTickLength: 3,
			minorTickPosition: TickPosition.OUTSIDE,
			
			//title
			showTitle: true,
			titleTextFormat: new TextFormat("_sans", 11, 0x000000, false, false, false, "", "", TextFormatAlign.LEFT, 0, 0, 0, 0)
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
		public function DefaultAxisRenderer()
		{
			super();
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------

		/**
		 * @private
		 * Placeholder for position
		 */
		private var _position:String = "bottom";
		
		/**
		 * @copy com.yahoo.astra.fl.charts.axes.ICartesianAxisRenderer#position
		 */
		public function get position():String
		{
			return _position;
		}
		
		/**
		 * @private (setter)
		 */
		public function set position(value:String):void
		{
			_position = value;
		}		
		/**
		 * @private
		 * Storage for the TextFields used for labels on this axis.
		 */
		protected var labelTextFields:Array = [];
		
		/**
		 * @private
		 * A cache to allow the reuse of TextFields when redrawing the renderer.
		 */
		private var _labelCache:Array;
		
		/**
		 * @private
		 * The TextField used to display the axis title.
		 */
		protected var titleTextField:BitmapText;
		
		/**
		 * @inheritDoc
		 */
		public function get length():Number
		{
			if(this.orientation == AxisOrientation.VERTICAL)
			{
				return this.contentBounds.height;
			}
			return this.contentBounds.width;
		}
		
		/**
		 * @private
		 * Storage for the orientation property.
		 */
		private var _orientation:String = AxisOrientation.VERTICAL;
		
		/**
		 * @inheritDoc
		 */
		public function get orientation():String
		{
			return this._orientation;
		}
		
		/**
		 * @private
		 */
		public function set orientation(value:String):void
		{
			if(this._orientation != value)
			{
				this._orientation = value;
				this.invalidate();
			}
		}
		
		/**
		 * @private
		 * Storage for the contentBounds property.
		 */
		protected var _contentBounds:Rectangle = new Rectangle();
		
		/**
		 * @inheritDoc
		 */
		public function get contentBounds():Rectangle
		{
			return this._contentBounds;
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
			this.invalidate(InvalidationType.DATA);
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
			this.invalidate(InvalidationType.DATA);
		}
		
		/**
		 * @private
		 * Storage for the title property.
		 */
		private var _title:String = "";
		
		/**
		 * @inheritDoc
		 */
		public function get title():String
		{
			return this._title;
		}
		
		/**
		 * @private
		 */
		public function set title(value:String):void
		{
			if(this._title != value)
			{
				this._title = value ? value : "";
				this.invalidate();
			}
		}
		
		private var _outerTickOffset:Number = 0;
		
		public function get outerTickOffset():Number
		{
			return _outerTickOffset;	
		}
		
		public function set outerTickOffset(value:Number):void
		{
			_outerTickOffset = value;
		}
		
		/**
		 * @private
		 * Storage for the majorUnitSetByUser
		 */
		private var _majorUnitSetByUser:Boolean = false;
		
		/**
		 * Indicates whether the major unit is user-defined or generated by the axis.
		 */
		public function get majorUnitSetByUser():Boolean
		{
			return this._majorUnitSetByUser;
		}
		
		/**
		 * @private (setter)
		 */
		public function set majorUnitSetByUser(value:Boolean):void
		{
			this._majorUnitSetByUser = value;
		}
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
	
		/**
		 * @inheritDoc
		 */
		public function updateAxis():void
		{
			var showLabels:Boolean = this.getStyleValue("showLabels") as Boolean;
			var labelDistance:Number = this.getStyleValue("labelDistance") as Number;
			var textFormat:TextFormat = this.getStyleValue("textFormat") as TextFormat;
			var labelRotation:Number = this.getStyleValue("labelRotation") as Number;
			var embedFonts:Boolean = this.getStyleValue("embedFonts") as Boolean;
			labelRotation = Math.max(-90, Math.min(labelRotation, 90));
			
			this.createCache();
			this.updateLabels(this.ticks, showLabels, textFormat, labelDistance, labelRotation, embedFonts);
			this.clearCache();
			
			this.updateTitle();
			this.draw();
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
			
			if(!this.titleTextField)
			{
				this.titleTextField = new BitmapText();
				this.titleTextField.autoSize = TextFieldAutoSize.LEFT;
				this.addChild(this.titleTextField);
			}
		}
		
		/**
		 * @private
		 */
		override protected function draw():void
		{
			this.graphics.clear();
			
			this.positionTitle();
			
			var showTicks:Boolean = this.getStyleValue("showTicks") as Boolean;
			var showMinorTicks:Boolean = this.getStyleValue("showMinorTicks") as Boolean;
			var filteredMinorTicks:Array = this.minorTicks.concat();
			if(showMinorTicks && showTicks)
			{
				//filter out minor ticks that appear at the same position
				//as major ticks.
				filteredMinorTicks = filteredMinorTicks.filter(function(item:AxisData, index:int, source:Array):Boolean
				{
					return !this.ticks.some(function(item2:AxisData, index2:int, source2:Array):Boolean
					{
						//using fuzzyEquals because we may encounter rounding errors
						return NumberUtil.fuzzyEquals(item.position, item2.position, 10);
					});
				}, this);
			}
			
			this.drawAxis();
			
			var showLabels:Boolean = this.getStyleValue("showLabels") as Boolean;
			var labelDistance:Number = this.getStyleValue("labelDistance") as Number;
			var textFormat:TextFormat = this.getStyleValue("textFormat") as TextFormat;
			var labelRotation:Number = this.getStyleValue("labelRotation") as Number;
			var embedFonts:Boolean = this.getStyleValue("embedFonts") as Boolean;
			labelRotation = Math.max(-90, Math.min(labelRotation, 90));
			this.positionLabels(this.ticks, showLabels, labelDistance, labelRotation, embedFonts);
			
			var tickPosition:String = this.getStyleValue("tickPosition") as String;
			var tickLength:Number = this.getStyleValue("tickLength") as Number;
			var tickWeight:int = this.getStyleValue("tickWeight") as int;
			var tickColor:uint = this.getStyleValue("tickColor") as uint;
			this.drawTicks(this.ticks, showTicks, tickPosition, tickLength, tickWeight, tickColor);
			
			var minorTickPosition:String = this.getStyleValue("minorTickPosition") as String;
			var minorTickLength:Number = this.getStyleValue("minorTickLength") as Number;
			var minorTickWeight:int = this.getStyleValue("minorTickWeight") as int;
			var minorTickColor:uint = this.getStyleValue("minorTickColor") as uint;
			this.drawTicks(filteredMinorTicks, showMinorTicks, minorTickPosition, minorTickLength, minorTickWeight, minorTickColor);
			
			super.draw();	
		}
		
		/**
		 * @private
		 * Updates the title text and styles.
		 */
		protected function updateTitle():void
		{
			var showTitle:Boolean = this.getStyleValue("showTitle") as Boolean;
			if(!showTitle)
			{
				this.titleTextField.text = "";
			}
			else
			{
				var textFormat:TextFormat = this.getStyleValue("titleTextFormat") as TextFormat;
				var embedFonts:Boolean = this.getStyleValue("embedFonts") as Boolean;
				this.titleTextField.defaultTextFormat = textFormat;
				this.titleTextField.embedFonts = embedFonts;
				this.titleTextField.text = this.title;
			}
		}
		
		/**
		 * @private
		 * Positions the title along the axis.
		 */
		protected function positionTitle():void
		{
		}
	
		/**
		 * @private
		 * Draws the axis origin line.
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
		}
		
		/**
		 * @private
		 * Draws a set of ticks on the axis.
		 */
		protected function drawTicks(data:Array, showTicks:Boolean, tickPosition:String,
			tickLength:Number, tickWeight:Number, tickColor:uint):void
		{
		}
		
		/**
		 * @private
		 * Saves the label TextFields so that they may be reused.
		 */
		protected function createCache():void
		{
			this._labelCache = this.labelTextFields.concat();
			this.labelTextFields = [];
		}
		
		/**
		 * @private
		 * Removes unused label TextFields.
		 */
		protected function clearCache():void
		{
			var cacheLength:int = this._labelCache.length;
			for(var i:int = 0; i < cacheLength; i++)
			{
				var label:BitmapText = BitmapText(this._labelCache.shift());
				this.removeChild(label);
			}
		}
				
		/**
		 * @private
		 * Creates the labels, sets their text and styles them. Positions the labels too.
		 */
		protected function updateLabels(data:Array, showLabels:Boolean, textFormat:TextFormat, labelDistance:Number, labelRotation:Number, embedFonts:Boolean):void
		{
			if(!showLabels)
			{
				return;
			}
			
			var dataCount:int = data.length;
			for(var i:int = 0; i < dataCount; i++)
			{
				var axisData:AxisData = AxisData(data[i]);
				var position:Number = axisData.position;
				if(isNaN(position))
				{
					//skip bad positions
					continue;
				}
				
				var label:BitmapText = this.getLabel();
				label.defaultTextFormat = textFormat;
				label.embedFonts = embedFonts;
				label.rotation = 0;
				label.text = axisData.label;
				this.labelTextFields.push(label);
			}
			this.positionLabels(data, showLabels, labelDistance, labelRotation, embedFonts);
		}
		
		/**
		 * @private
		 * Positions a set of labels on the axis.
		 */
		protected function positionLabels(labels:Array, showLabels:Boolean, labelDistance:Number, labelRotation:Number, embedFonts:Boolean):void
		{
			var labelCount:int = this.labelTextFields.length;
			for(var i:int = 0; i < labelCount; i++)
			{
				var label:BitmapText = BitmapText(this.labelTextFields[i]);
				label.rotation = 0;
				var axisData:AxisData = AxisData(this.ticks[i]);
				var position:Number = axisData.position;
			
				if(this.orientation == AxisOrientation.VERTICAL)
				{
					position += this.contentBounds.y;
					if(showLabels)
					{
						label.x = this.contentBounds.x - labelDistance - this.outerTickOffset - label.width;
						label.y = position - label.height/2;
					}
					
					if(labelRotation == 0)
					{
						//do nothing. already ideally positioned
					}
					else if(labelRotation < 90 && labelRotation > 0)
					{
						label.x -= (label.height * labelRotation / 180);
						DynamicRegistration.rotate(label, new Point(label.width, label.height / 2), labelRotation);
					}
					else if(labelRotation > -90 && labelRotation < 0)
					{
						label.x -= (label.height * Math.abs(labelRotation) / 180);
						DynamicRegistration.rotate(label, new Point(label.width, label.height / 2), labelRotation);
					}
					else if(labelRotation == -90)
					{
						label.y -= label.width / 2;
						label.x -= (label.height * Math.abs(labelRotation) / 180);
						DynamicRegistration.rotate(label, new Point(label.width, label.height / 2), labelRotation);
					}
					else //90
					{
						label.y += label.width / 2;
						label.x -= (label.height * Math.abs(labelRotation) / 180);
						DynamicRegistration.rotate(label, new Point(label.width, label.height / 2), labelRotation);
					}

				}
				else //horizontal
				{
					position += this.contentBounds.x;
					if(showLabels)
					{
						label.y = this.contentBounds.y + this.contentBounds.height + labelDistance + this.outerTickOffset;
					}
					
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
		 * Either creates a new label TextField or retrieves one from the cache.
		 */
		protected function getLabel():BitmapText
		{
			if(this._labelCache.length > 0)
			{
				return BitmapText(this._labelCache.shift());
			}
			var labelRotation:Number = this.getStyleValue("labelRotation") as Number;
			var label:BitmapText = new BitmapText();
			label.selectable = false;
			label.autoSize = TextFieldAutoSize.LEFT;
			this.addChild(label);
			return label;
		}
		
		/**
		 * @private
		 * If labels overlap, some may need to be hidden.
		 */
		protected function handleOverlappingLabels():void
		{
		}		
	}
}