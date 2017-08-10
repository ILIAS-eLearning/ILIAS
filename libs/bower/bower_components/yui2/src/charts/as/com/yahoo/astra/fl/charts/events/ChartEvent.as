package com.yahoo.astra.fl.charts.events
{
	import flash.events.Event;
	import flash.display.DisplayObject;
	import com.yahoo.astra.fl.charts.series.ISeries;
	import com.yahoo.astra.fl.charts.series.ISeriesItemRenderer;

	/**
	 * Events related to items appearing in a chart.
	 * 
	 * @author Josh Tynjala
	 */
	public class ChartEvent extends Event
	{
		
	//--------------------------------------
	//  Static Constants
	//--------------------------------------
	
		/**
         * Defines the value of the <code>type</code> property of an <code>itemRollOver</code> 
		 * event object. 
         *
         * @eventType itemRollOver
		 */
		public static const ITEM_ROLL_OVER:String = "itemRollOver";
		
		/**
         * Defines the value of the <code>type</code> property of an <code>itemRollOut</code> 
		 * event object. 
         *
         * @eventType itemRollOut
		 */
		public static const ITEM_ROLL_OUT:String = "itemRollOut";
		
		/**
         * Defines the value of the <code>type</code> property of an <code>itemClick</code> 
		 * event object. 
         *
         * @eventType itemClick
		 */
		public static const ITEM_CLICK:String = "itemClick";
		
		/**
         * Defines the value of the <code>type</code> property of an <code>itemDoubleClick</code> 
		 * event object. 
         *
         * @eventType itemDoubleClick
		 */
		public static const ITEM_DOUBLE_CLICK:String = "itemDoubleClick";
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function ChartEvent(type:String, index:int, item:Object, itemRenderer:ISeriesItemRenderer, series:ISeries)
		{
			super(type, true, false);
			this.index = index;
			this.item = item;
			this.itemRenderer = itemRenderer;
			this.series = series;
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * @private
		 */
		private var _index:int;
		
		/**
		 * The series index for the item related to this event.
		 */
		public function get index():int
		{
			return this._index;
		}
		
		/**
		 * @private
		 */
		public function set index(value:int):void
		{
			this._index = value;
		}
	
		/**
		 * @private
		 */
		private var _item:Object;
		
		/**
		 * The data for the item related to this event.
		 */
		public function get item():Object
		{
			return this._item;
		}
		
		/**
		 * @private
		 */
		public function set item(value:Object):void
		{
			this._item = value;
		}
	
		/**
		 * @private
		 */
		private var _itemRenderer:ISeriesItemRenderer;
		
		/**
		 * The ISeriesItemRenderer displaying the item on the chart.
		 */
		public function get itemRenderer():ISeriesItemRenderer
		{
			return this._itemRenderer;
		}
		
		/**
		 * @private
		 */
		public function set itemRenderer(value:ISeriesItemRenderer):void
		{
			this._itemRenderer = value;
		}
	
		/**
		 * @private
		 */
		private var _series:ISeries;
		
		/**
		 * The ISeries containing the item on the chart.
		 */
		public function get series():ISeries
		{
			return this._series;
		}
		
		/**
		 * @private
		 */
		public function set series(value:ISeries):void
		{
			this._series = value;
		}
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
		
		/**
		 * @private
		 */
		override public function clone():Event
		{
			return new ChartEvent(this.type, this.index, this.item, this.itemRenderer, this.series);
		}
	}
}