package com.yahoo.yui.charts
{
	import com.yahoo.astra.fl.charts.axes.CategoryAxis;
	import com.yahoo.astra.fl.charts.axes.IAxis;
	import com.yahoo.astra.fl.charts.axes.NumericAxis;
	import com.yahoo.astra.fl.charts.axes.TimeAxis;
	import com.yahoo.astra.utils.JavaScriptUtil;
	
	import flash.utils.Dictionary;
	import flash.utils.getDefinitionByName;
	import flash.utils.getQualifiedClassName;
	
	public class AxisSerializer
	{
		
	//--------------------------------------
	//  Class Properties
	//--------------------------------------
	
		private static var shortNameToType:Object = {};
		shortNameToType.numeric = NumericAxis;
		shortNameToType.category = CategoryAxis;
		shortNameToType.time = TimeAxis;
		
		private static var typeToShortName:Dictionary = new Dictionary(true);
		typeToShortName[NumericAxis] = "numeric";
		typeToShortName[CategoryAxis] = "category";
		typeToShortName[TimeAxis] = "time";
		
	//--------------------------------------
	//  Static Methods
	//--------------------------------------
		
		public static function getShortName(input:Object):String
		{
			if(!input) return null;
			
			if(input is String)
			{
				input = getDefinitionByName(input as String);
			}
			var shortName:String = shortNameToType[input];
			return shortName;
		}
		
		public static function getType(shortName:String):Class
		{
			return shortNameToType[shortName];
		}
		
		public static function writeAxis(input:IAxis):Object
		{
			var axis:Object = {type: getShortName(getQualifiedClassName(input))};
			return axis;
		}
		
		public static function readAxis(input:Object):IAxis
		{
			var AxisType:Class = AxisSerializer.getType(input.type);
			var axis:IAxis = new AxisType();
			axis.title = input.title;
			axis.reverse = input.reverse;
			if(input.labelFunction)
			{
				axis.labelFunction = JavaScriptUtil.createCallbackFunction(input.labelFunction).callback;
			}

			if(input.position != null)
			{
				axis.position = input.position;
			}
						
			if(axis is NumericAxis)
			{
				var numericAxis:NumericAxis = NumericAxis(axis);
				if(input.minimum != null && !isNaN(input.minimum))
				{
					numericAxis.minimum = input.minimum;
				}
				if(input.maximum != null && !isNaN(input.maximum))
				{
					numericAxis.maximum = input.maximum;
				}
				if(input.majorUnit != null && !isNaN(input.majorUnit))
				{
					numericAxis.majorUnit = input.majorUnit;
				}
				if(input.minorUnit != null && !isNaN(input.minorUnit))
				{
					numericAxis.minorUnit = input.minorUnit;
				}
				if(input.numLabels != null && !isNaN(input.numLabels))
				{
					numericAxis.numLabels = input.numLabels;
				}
				if(input.roundMajorUnit != null)
				{
					numericAxis.roundMajorUnit = input.roundMajorUnit;
				}
				if(input.order != null)
				{
					numericAxis.order = input.order;
				}

				numericAxis.snapToUnits = input.snapToUnits;
				numericAxis.alwaysShowZero = input.alwaysShowZero;
				numericAxis.scale = input.scale;
				numericAxis.stackingEnabled = input.stackingEnabled;
				numericAxis.calculateByLabelSize = input.calculateByLabelSize;
				numericAxis.adjustMaximumByMajorUnit = input.adjustMaximumByMajorUnit;
				numericAxis.adjustMinimumByMajorUnit = input.adjustMinimumByMajorUnit;
			}
			else if(axis is TimeAxis)
			{
				var timeAxis:TimeAxis = TimeAxis(axis);
				if(input.minimum != null && !isNaN(input.minimum))
				{
					timeAxis.minimum = input.minimum;
				}
				if(input.maximum != null && !isNaN(input.maximum))
				{
					timeAxis.maximum = input.maximum;
				}
				if(input.majorUnit != null && !isNaN(input.majorUnit))
				{
					timeAxis.majorUnit = input.majorUnit;
				}
				if(input.majorTimeUnit != null)
				{
					timeAxis.majorTimeUnit = input.majorTimeUnit;
				}
				if(input.minorUnit != null && !isNaN(input.minorUnit))
				{
					timeAxis.minorUnit = input.minorUnit;
				}
				if(input.minorTimeUnit != null)
				{
					timeAxis.minorTimeUnit = input.minorTimeUnit;
				}
				if(input.numLabels != null && !isNaN(input.numLabels))
				{
					timeAxis.numLabels = input.numLabels;
				}
				timeAxis.snapToUnits = input.snapToUnits;
				timeAxis.stackingEnabled = input.stackingEnabled;
				timeAxis.calculateByLabelSize = input.calculateByLabelSize;
			}
			else if(axis is CategoryAxis)
			{
				var categoryAxis:CategoryAxis = CategoryAxis(axis);
				if(input.categoryNames != null)
				{
					categoryAxis.categoryNames = input.categoryNames;
				}
				if(input.numLabels != null && !isNaN(input.numLabels))
				{
					categoryAxis.numLabels = input.numLabels;
				}
				if(input.calculateCategoryCount)
				{
					categoryAxis.calculateCategoryCount = input.calculateCategoryCount;
				}
			}
			return axis;
		}
	}
}