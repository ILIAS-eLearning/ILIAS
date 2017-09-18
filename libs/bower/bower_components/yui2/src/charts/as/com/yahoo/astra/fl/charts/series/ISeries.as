package com.yahoo.astra.fl.charts.series
{
	import com.yahoo.astra.fl.charts.Chart;
	import com.yahoo.astra.fl.charts.IChart;
	
	import flash.events.IEventDispatcher;
	
	//--------------------------------------
	//  Events
	//--------------------------------------

	/**
	 * Dispatched when the data property for an ISeries changes.
	 */
	[Event(name="dataChange", type="flash.events.Event")]
	
	/**
	 * A renderer for a series displayed on a chart.
	 * 
	 * <p>Important: Must be a subclass of DisplayObject</p>
	 * 
	 * @see flash.display.DisplayObject
	 * @author Josh Tynjala
	 */
	public interface ISeries extends IEventDispatcher
	{
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
		
		/**
		 * The chart in which this series appears.
		 */
		function get chart():Object;
		
		/**
		 * @private
		 */
		function set chart(value:Object):void;
		
		/**
		 * The data provider for this series. Accepts <code>Array</code> or <code>XMLList</code> objects.
		 */
		function get dataProvider():Object;
		
		/**
		 * @private
		 */
		function set dataProvider(value:Object):void;
		
		/**
		 * The name of the series as it appears to the user.
		 */
		function get displayName():String;
		
		/**
		 * @private
		 */
		function set displayName(value:String):void;
		
		/**
		 * The number of items in the series.
		 */
		function get length():int;
		
		/**
		 * DataTip Function for series
		 */
		function get dataTipFunction():Function;
		
		/**
		 * @private (setter)
		 */
		function set dataTipFunction(value:Function):void;		
		
		/**
		 * If defined, the chart will call the input function to determine the text displayed in 
		 * in the chart's legend.
		 */
		function get legendLabelFunction():Function;
		
		/**
		 * @private (setter)
		 */
		function set legendLabelFunction(value:Function):void;
		
	//--------------------------------------
	//  Methods
	//--------------------------------------
		
		/**
		 * Creates a copy of the ISeries object.
		 * 
		 * @return a new ISeries object
		 */
		function clone():ISeries;
		
		/**
		 * Returns the index of an item renderer.
		 * 
		 * @param renderer The renderer whose index is to be returned.
		 * @return The index of the renderer.
		 */
		function itemRendererToIndex(renderer:ISeriesItemRenderer):int;
		
		/**
		 * Converts an item to its corresponding item renderer.
		 * 
		 * @param item The item from the dataProvider to be converted to a renderer.
		 * @return The renderer that corresponds to the item.
		 */
		function itemToItemRenderer(item:Object):ISeriesItemRenderer;
	}
}
