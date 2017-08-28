package com.yahoo.astra.fl.charts.events
{
	import flash.events.Event;
	import flash.display.DisplayObject;
	/**
	 * Events related to the Axis in a chart.
	 * 
	 * @author Tripp
	 */
	public class AxisEvent extends Event
	{
		
	//--------------------------------------
	//  Static Constants
	//--------------------------------------
		/**
		 * Defines the value of <code>type</code> property of an <code>axisReady</code>
		 * event object.
		 *
		 * @eventType axisReady
		 */
		public static const AXIS_READY:String = "axisReady";
		
		/**
		 * Defines the value of <code>type</code> property of an <code>axisFailed</code>
		 * event object.
		 * 
		 * @eventType axisFailed
		 */
		public static const AXIS_FAILED:String = "axisFailed";
		
		//--------------------------------------
		//  Constructor
		//--------------------------------------

			/**
			 * Constructor.
			 */
			public function AxisEvent(type:String)
			{
				super(type, true, false);
			}

		//--------------------------------------
		//  Public Methods
		//--------------------------------------

			/**
			 * @private
			 */
			override public function clone():Event
			{
				return new AxisEvent(this.type);
			}
	}		
}