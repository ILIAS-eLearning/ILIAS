package com.yahoo.astra.fl.charts.events
{
	import flash.events.Event;

	/**
	 * Events related to a chart's legend.
	 * 
	 * @see com.yahoo.astra.fl.charts.Legend
	 * 
	 * @author Josh Tynjala
	 */
	public class LegendEvent extends Event
	{
		
	//--------------------------------------
	//  Static Properties
	//--------------------------------------
	
		/**
         * Defines the value of the <code>type</code> property of an <code>legendMarkerClick</code> 
		 * event object. 
         *
         * @eventType legendMarkerClick
		 */
		public static const LEGEND_MARKER_CLICK:String = "legendMarkerClick";
	
		/**
         * Defines the value of the <code>type</code> property of an <code>legendMarkerDoubleClick</code> 
		 * event object. 
         *
         * @eventType legendMarkerDoubleClick
		 */
		public static const LEGEND_MARKER_DOUBLE_CLICK:String = "legendMarkerDoubleClick";
	
		/**
         * Defines the value of the <code>type</code> property of an <code>legendMarkerRollOver</code> 
		 * event object. 
         *
         * @eventType legendMarkerRollOver
		 */
		public static const LEGEND_MARKER_ROLL_OVER:String = "legendMarkerRollOver";
	
		/**
         * Defines the value of the <code>type</code> property of an <code>legendMarkerRollOut</code> 
		 * event object. 
         *
         * @eventType legendMarkerRollOut
		 */
		public static const LEGEND_MARKER_ROLL_OUT:String = "legendMarkerRollOut";
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function LegendEvent(type:String, index:int, bubbles:Boolean=false, cancelable:Boolean=false)
		{
			super(type, bubbles, cancelable);
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * The index of the item in the legend.
		 */
		public var index:int;
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
	
		/**
		 * @private
		 */
		override public function clone():Event
		{
			return new LegendEvent(LegendEvent.LEGEND_MARKER_CLICK, this.index, this.bubbles, this.cancelable);
		}
		
	}
}