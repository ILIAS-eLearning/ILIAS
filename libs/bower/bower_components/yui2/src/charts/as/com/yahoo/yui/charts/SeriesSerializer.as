package com.yahoo.yui.charts
{
	import com.yahoo.astra.fl.charts.series.*;
	import com.yahoo.astra.utils.JavaScriptUtil;
	
	import flash.utils.Dictionary;
	import flash.utils.getDefinitionByName;
	import flash.utils.getQualifiedClassName;
	
	public class SeriesSerializer
	{
		
	//--------------------------------------
	//  Static Properties
	//--------------------------------------
	
		private static var shortNameToSeriesTypeHash:Object = {};
		shortNameToSeriesTypeHash[ChartSerializer.BAR] = BarSeries;
		shortNameToSeriesTypeHash[ChartSerializer.COLUMN] = ColumnSeries;
		shortNameToSeriesTypeHash[ChartSerializer.LINE] = LineSeries;
		shortNameToSeriesTypeHash[ChartSerializer.PIE] = PieSeries;
		shortNameToSeriesTypeHash[ChartSerializer.STACK_BAR] = StackedBarSeries; 
		shortNameToSeriesTypeHash[ChartSerializer.STACK_COLUMN] = StackedColumnSeries;
		
		private static var seriesTypeToShortNameHash:Dictionary = new Dictionary(true);
		seriesTypeToShortNameHash[BarSeries] = ChartSerializer.BAR;
		seriesTypeToShortNameHash[ColumnSeries] = ChartSerializer.COLUMN;
		seriesTypeToShortNameHash[LineSeries] = ChartSerializer.LINE;
		seriesTypeToShortNameHash[PieSeries] = ChartSerializer.PIE;
		seriesTypeToShortNameHash[StackedBarSeries] = ChartSerializer.STACK_BAR;
		seriesTypeToShortNameHash[StackedColumnSeries] = ChartSerializer.STACK_COLUMN;
	
	//--------------------------------------
	//  Static Methods
	//--------------------------------------
		
		/**
		 * Returns a series class based on a string reference.
		 * 
		 * @param name Reference from which to derive a class.
		 * @return Series Class
		 */
		public static function shortNameToSeriesType(name:String):Class
		{
			return shortNameToSeriesTypeHash[name];
		}
		
		/**
		 * Returns a series string reference based on a class.
		 *
		 * @param type Series class from which to derive a string reference.
		 * @return Reference to the series.
		 */
		public static function seriesTypeToShortName(type:Class):String
		{
			return seriesTypeToShortNameHash[type];
		}
		
		/**
		 * Returns the properties of a series.
		 *
		 * @param input Series from which to retrieve the properties.
		 * @return Properties of the Series.
		 */
		public static function writeSeries(input:ISeries):Object
		{
			if(!input)
			{
				return null;
			}
			
			var type:String = seriesTypeToShortNameHash[getDefinitionByName(getQualifiedClassName(input))];
			var series:Object = {type: type, data: input.dataProvider, displayName: input.displayName};
			if(input is CartesianSeries)
			{
				var cartesianSeries:CartesianSeries = CartesianSeries(input);
				series.yField = cartesianSeries.verticalField;
				series.xField = cartesianSeries.horizontalField;
				series.axis = cartesianSeries.axis;
			}
			else if(input is PieSeries)
			{
				series.dataField = PieSeries(input).dataField;
				series.categoryField = PieSeries(input).categoryField;
			}
			return series;
		}
		
		/**
		 * Creates or updates a series based on an object of series properties.
		 *
		 * @param input Properties to be applied to the series.
		 * @param series Series to be updated
		 * @return New or updated series.
		 */
		public static function readSeries(input:Object, series:ISeries = null):ISeries
		{
			if(!input || !input.type)
			{
				return null;
			}
			
			if(!series)
			{
				var SeriesType:Class = shortNameToSeriesTypeHash[input.type];
				series = new SeriesType()
			}
			
			if(input.dataTipFunction)
			{
				series.dataTipFunction = getDataTipFunction(input.dataTipFunction);
			}
			
			if(input.legendLabelFunction)
			{
				series.legendLabelFunction = JavaScriptUtil.createCallbackFunction(input.legendLabelFunction).callback;
			}
						
			series.dataProvider = input.dataProvider;
			series.displayName = input.displayName;
			if(series is CartesianSeries)
			{
				var cartesianSeries:CartesianSeries = CartesianSeries(series);
				cartesianSeries.verticalField = input.yField;
				cartesianSeries.horizontalField = input.xField;
				cartesianSeries.showInLegend = input.showInLegend == false ? false : true;	
				
				if(input.axis)
				{
					cartesianSeries.axis = input.axis;
				}			
			}
			else if(series is PieSeries)
			{
				PieSeries(series).dataField = input.dataField;
				PieSeries(series).categoryField = input.categoryField;
				if(input.hasOwnProperty("labelFunction"))
				{
					PieSeries(series).labelFunction = JavaScriptUtil.createCallbackFunction(input.labelFunction).callback;
				}
			}
			return series;
		}
		
		/**
		 * @private
		 */
		private static function getDataTipFunction(value:String):Function
		{
			var delegate:Object = {dataTipFunction: JavaScriptUtil.createCallbackFunction(value).callback};
			delegate.callback = function(item:Object, index:int, series:ISeries):String
			{
				return delegate.dataTipFunction(item, index, SeriesSerializer.writeSeries(series));
			}
			
			return delegate.callback;
		}		
	}
}