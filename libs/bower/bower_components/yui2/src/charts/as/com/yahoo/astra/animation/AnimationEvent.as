package com.yahoo.astra.animation
{
	import flash.events.Event;

	/**
	 * The AnimationEvent class represents events that are broadcast by the com.yahoo.astra.animation.Animation class.
	 *
	 * @see com.yahoo.astra.animation.Animation
	 * 
	 * @author Josh Tynjala
	 */
	public class AnimationEvent extends Event
	{
		
	//--------------------------------------
	//  Static Properties
	//--------------------------------------
	
		/**
		 * Indicates that the animation has started playing.
		 *  <p>The properties of the event object have the following values:</p>
		 *  <table class="innertable">
		 *     <tr><th>Property</th><th>Value</th></tr>
		 *     <tr><td><code>bubbles</code></td><td>false</td></tr>
		 *     <tr><td><code>cancelable</code></td><td>false</td></tr>
		 *     <tr><td><code>currentTarget</code></td><td>The object that defines the 
		 *       event listener that handles the event. For example, if you use 
		 *       <code>myButton.addEventListener()</code> to register an event listener, 
		 *       <code>myButton</code> is the value of the <code>currentTarget</code> property.</td></tr>
		 *     <tr><td><code>target</code></td><td>The object that dispatched the event; 
		 *       it is not always the object listening for the event. 
		 *       Use the <code>currentTarget</code> property to always access the 
		 *       object listening for the event.</td></tr>
		 *     <tr><td><code>parameters</code></td><td>The values of the properties controlled by the animation,
		 *		 when the event occurred.</td></tr>
		 *  </table>
		 *
		 * @eventType animationStart
		 */
		public static const START:String = "animationStart";

		/**
		 * Indicates that the animation has changed and the screen has been updated.
		 *  <p>The properties of the event object have the following values:</p>
		 *  <table class="innertable">
		 *     <tr><th>Property</th><th>Value</th></tr>
		 *     <tr><td><code>bubbles</code></td><td>false</td></tr>
		 *     <tr><td><code>cancelable</code></td><td>false</td></tr>
		 *     <tr><td><code>currentTarget</code></td><td>The object that defines the 
		 *       event listener that handles the event. For example, if you use 
		 *       <code>myButton.addEventListener()</code> to register an event listener, 
		 *       <code>myButton</code> is the value of the <code>currentTarget</code> property.</td></tr>
		 *     <tr><td><code>target</code></td><td>The object that dispatched the event; 
		 *       it is not always the object listening for the event. 
		 *       Use the <code>currentTarget</code> property to always access the 
		 *       object listening for the event.</td></tr>
		 *     <tr><td><code>parameters</code></td><td>The values of the properties controlled by the animation,
		 *		 when the event occurred.</td></tr>
		 *  </table>
		 *
		 * @eventType animationUpdate
		 */
		public static const UPDATE:String = "animationUpdate";

		/**
		 * Indicates that the animation has reached the end and finished.
		 *  <p>The properties of the event object have the following values:</p>
		 *  <table class="innertable">
		 *     <tr><th>Property</th><th>Value</th></tr>
		 *     <tr><td><code>bubbles</code></td><td>false</td></tr>
		 *     <tr><td><code>cancelable</code></td><td>false</td></tr>
		 *     <tr><td><code>currentTarget</code></td><td>The object that defines the 
		 *       event listener that handles the event. For example, if you use 
		 *       <code>myButton.addEventListener()</code> to register an event listener, 
		 *       <code>myButton</code> is the value of the <code>currentTarget</code> property.</td></tr>
		 *     <tr><td><code>target</code></td><td>The object that dispatched the event; 
		 *       it is not always the object listening for the event. 
		 *       Use the <code>currentTarget</code> property to always access the 
		 *       object listening for the event.</td></tr>
		 *     <tr><td><code>parameters</code></td><td>The values of the properties controlled by the animation,
		 *		 when the event occurred.</td></tr>
		 *  </table>
		 *
		 * @eventType animationComplete
		 */
		public static const COMPLETE:String = "animationComplete";
		
		/**
		 * Indicates that the animation has been paused.
		 *  <p>The properties of the event object have the following values:</p>
		 *  <table class="innertable">
		 *     <tr><th>Property</th><th>Value</th></tr>
		 *     <tr><td><code>bubbles</code></td><td>false</td></tr>
		 *     <tr><td><code>cancelable</code></td><td>false</td></tr>
		 *     <tr><td><code>currentTarget</code></td><td>The object that defines the 
		 *       event listener that handles the event. For example, if you use 
		 *       <code>myButton.addEventListener()</code> to register an event listener, 
		 *       <code>myButton</code> is the value of the <code>currentTarget</code> property.</td></tr>
		 *     <tr><td><code>target</code></td><td>The object that dispatched the event; 
		 *       it is not always the object listening for the event. 
		 *       Use the <code>currentTarget</code> property to always access the 
		 *       object listening for the event.</td></tr>
		 *     <tr><td><code>parameters</code></td><td>The values of the properties controlled by the animation,
		 *		 when the event occurred.</td></tr>
		 *  </table>
		 *
		 * @eventType animationPause
		 */
		public static const PAUSE:String = "animationPause";
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 *
		 * @param type			The event type; indicates the action that caused the event.
		 * @param parameters	The current values of the properties controlled by the animation.
		 */    
		public function AnimationEvent(type:String, parameters:Object)
		{
			super(type, false, false);
			this.parameters = parameters;
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * The values of the properties controlled by the animation, when the event occurred.
		 */
		public var parameters:Object;
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
	
		/**
		 * @private
		 */
		override public function clone():Event
		{
			return new AnimationEvent(this.type, this.parameters);
		}
	}
}