package com.yahoo.astra.layout.events
{
	import flash.events.Event;

	/**
	 * Events associated with ILayoutContainer objects.
	 * 
	 * @see ILayoutContainer
	 * @author Josh Tynjala
	 */
	public class LayoutEvent extends Event
	{
		
	//--------------------------------------
	//  Static Properties
	//--------------------------------------
	
		/**
		 * The <code>LayoutEvent.LAYOUT_CHANGE</code> event type constant indicates that
		 * the layout of an ILayoutContainer needs to be redrawn.
		 * 
		 * @eventType layoutChange
		 */
		public static const LAYOUT_CHANGE:String = "layoutChange";
	
	//--------------------------------------
	//  Constructor
	//--------------------------------------
		
		/**
		 * Constructor.
		 */
		public function LayoutEvent(type:String, bubbles:Boolean=false, cancelable:Boolean=false)
		{
			super(type, bubbles, cancelable);
		}
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
	
		/**
		 * @private
		 */
		override public function clone():Event
		{
			return new LayoutEvent(this.type, this.bubbles, this.cancelable);
		}
		
	}
}