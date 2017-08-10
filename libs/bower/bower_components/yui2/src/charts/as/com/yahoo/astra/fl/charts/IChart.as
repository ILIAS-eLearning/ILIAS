package com.yahoo.astra.fl.charts
{
	import com.yahoo.astra.fl.charts.axes.IAxis;
	import com.yahoo.astra.fl.charts.series.ISeries;
	
	import flash.events.IEventDispatcher;
	import flash.geom.Point;
	
	/**
	 * Methods and properties expected to be defined by all charts.
	 * 
	 * @author Josh Tynjala
	 */
	public interface IChart extends IEventDispatcher
	{
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * The data to be displayed by the chart. Accepted data types include
		 * all of the following:
		 * 
		 * <ul>
		 * 	<li>An ISeries instance with its own data provider.</li>
		 * 	<li>An Array containing ISeries instances</li>
		 * 	<li>An Array containing Numbers.</li>
		 * 	<li>An Array containing complex objects.</li>
		 * 	<li>An XMLList</li>
		 * 	<li>An Array containing Arrays of Numbers or complex objects.</li>
		 * </ul>
		 * 
		 * <p>Note: When complex objects or XML is used in the data provider,
		 * developers must define "fields" used to access data used by the chart.
		 * For instance, CartesianChart exposes <code>horizontalField</code> and
		 * <code>verticalField</code> properties. PieChart exposes <code>dataField</code>
		 * and <code>categoryField</code> properties.
		 * 
		 * <p>The chart will automatically convert the input data to an Array of
		 * ISeries objects. Don't access <code>dataProvider</code> if you intend
		 * to retreive the data in its original form.
		 * 
		 * @see com.yahoo.astra.fl.charts.series.ISeries
		 */
		function get dataProvider():Object;
		
		/**
		 * @private
		 */
		function set dataProvider(value:Object):void;
		
	//--------------------------------------
	//  Methods
	//--------------------------------------
	
		/**
		 * Calculates the position of a data point along the axis.
		 * 
		 * @param series		The series in which the data appears.
		 * @param itemIndex		The index of the item within the series.
		 * @return				The display position in pixels on the axis
		 */
		function itemToPosition(series:ISeries, itemIndex:int):Point;
		
		/**
		 * Retreives the value of an item on one of the chart's axes.
		 * 
		 * @param series		The series in which the item appears.
		 * @param itemIndex		The index of the item within the series.
		 * @param axis			The axis for which to extract the value.
		 * @return				The value of the item on the axis. Most likely a field on the axis.
		 */
		function itemToAxisValue(series:ISeries, itemIndex:int, axis:IAxis, stack:Boolean = true):Object;
	}
}
