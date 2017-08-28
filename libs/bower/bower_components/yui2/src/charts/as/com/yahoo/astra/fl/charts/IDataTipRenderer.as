package com.yahoo.astra.fl.charts
{
	import flash.events.IEventDispatcher;
	
	/**
	 * A renderer for a mouse-over datatip on a chart.
	 * 
	 * <p>Important: Must be a subclass of <code>DisplayObject</code></p>
	 * 
	 * @see flash.display.DisplayObject
	 * @author Josh Tynjala
	 */
	public interface IDataTipRenderer extends IEventDispatcher
	{
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * The text that appears in the data tip's label.
		 */
		function get text():String;
		
		/**
		 * @private
		 */
		function set text(value:String):void;
		
		/**
		 * The data for the item that this data tip represents.
		 * Custom implementations of <code>IDataTipRenderer</code>
		 * may use this property to render additional information for
		 * the user.
		 */
		function get data():Object;
		
		/**
		 * @private
		 */
		function set data(value:Object):void;
	}
}