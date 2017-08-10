package com.yahoo.astra.fl.charts.series
{
	import com.yahoo.astra.fl.charts.legend.LegendItemData;
	
	import fl.core.InvalidationType;
	
	import flash.events.Event;
	//--------------------------------------
	//  Styles
	//--------------------------------------
	
	/**
	 * Indicates the value of the visible property. A value of <code>visible</code> indicates <code>true</code>.
	 * A value of <code>hidden</code> indicates <code>false</code>.
	 */
	[Style(name="visibility", type="String")]

	/**
	 * Functionality common to most series appearing in cartesian charts.
	 * Generally, a <code>CartesianSeries</code> object shouldn't be
	 * instantiated directly. Instead, a subclass with a concrete implementation
	 * should be used.
	 * 
	 * @author Josh Tynjala
	 */
	public class CartesianSeries extends Series implements ILegendItemSeries
	{
	//--------------------------------------
	//  Class Variables
	//--------------------------------------
		/**
		 * @private
		 */
		private static var defaultStyles:Object =
		{
			visibility:"visible"
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
		public function CartesianSeries(data:Object = null)
		{
			super(data);
		}
	
	//--------------------------------------
	//  Properties
	//--------------------------------------
		
		/**
		 * @private
		 * Storage for the horizontalField property.
		 */
		private var _horizontalField:String;
		
		/**
		 * Defines the property to access when determining the x value.
		 */
		public function get horizontalField():String
		{
			return this._horizontalField;
		}
		
		/**
		 * @private
		 */
		public function set horizontalField(value:String):void
		{
			if(this._horizontalField != value)
			{
				this._horizontalField = value;
				this.dispatchEvent(new Event("dataChange"));
				this.invalidate(InvalidationType.DATA);
			}
		}
		
		/**
		 * @private
		 * Storage for the verticalField property.
		 */
		private var _verticalField:String;
		
		/**
		 * Defines the property to access when determining the y value.
		 */
		public function get verticalField():String
		{
			return this._verticalField;
		}
		
		/**
		 * @private
		 */
		public function set verticalField(value:String):void
		{
			if(this._verticalField != value)
			{
				this._verticalField = value;
				this.dispatchEvent(new Event("dataChange"));
				this.invalidate(InvalidationType.DATA);
			}
		}
	
		/**
		 * Indicates whether the series is bound to a primary or secondary axis
		 */
		public var axis:String = "primary";
		
		/**
		 * @private
		 * Storage for showInLegend property
		 */
		private var _showInLegend:Boolean = true;
		
		/**
		 * @copy com.yahoo.astra.fl.charts.series.ILegendItemSeries#showInLegend
		 */
		public function get showInLegend():Boolean
		{
			return this._showInLegend;
		}
		
		/**
		 * @private (setter)
		 */
		public function set showInLegend(value:Boolean):void
		{
			this._showInLegend = value;
		}	
		
		/**
		 * @private (setter)
		 */	
		override public function set visible(value:Boolean):void
		{
			super.visible = value;
			if(this.getStyleValue("visibility") as String == "hidden")
			{
				if(this.visible) super.setStyle("visibility", "visible");
			}
			else
			{
				if(!this.visible) super.setStyle("visibility", "hidden");
			}
		}
		
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
		
		/**
		 * @copy com.yahoo.astra.fl.charts.series.ILegendItemSeries#createLegendItemData()
		 */
		public function createLegendItemData():LegendItemData
		{
			var fillColor:uint = this.getStyleValue("fillColor") != null ? this.getStyleValue("fillColor") as uint : this.getStyleValue("color") as uint;
			var borderColor:uint = this.getStyleValue("borderColor") != null ? this.getStyleValue("borderColor") as uint : this.getStyleValue("color") as uint;
			return new LegendItemData(this.displayName, this.getStyleValue("markerSkin"), 
										fillColor, 
										this.getStyleValue("fillAlpha") as Number, 
										borderColor, 
										this.getStyleValue("borderAlpha") as Number);
		}
		
		/**
		 * @private
		 * @copy fl.core.UIComponent#setStyle()
		 */
		override public function setStyle(style:String, value:Object):void
		{
			super.setStyle(style, value);
			if(style == "visibility")
			{
				this.visible = value != "hidden";
			}
		}		
	}
}