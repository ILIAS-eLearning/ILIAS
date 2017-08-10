package com.yahoo.yui.charts
{
	import com.yahoo.astra.fl.charts.*;
	
	import flash.utils.Dictionary;
	import flash.utils.getDefinitionByName;
	
	public class ChartSerializer
	{
		
	//--------------------------------------
	//  Static Properties
	//--------------------------------------
		
		public static const BAR:String = "bar";
		public static const COLUMN:String = "column";
		public static const LINE:String = "line";
		public static const PIE:String = "pie";
		public static const STACK_BAR:String = "stackbar";
		public static const STACK_COLUMN:String = "stackcolumn";
	
		private static var shortNameToType:Object = {};
		shortNameToType[BAR] = BarChart;
		shortNameToType[ChartSerializer.COLUMN] = ColumnChart;
		shortNameToType[LINE] = LineChart;
		shortNameToType[PIE] = PieChart;
		shortNameToType[STACK_BAR] = StackedBarChart; 
		shortNameToType[STACK_COLUMN] = StackedColumnChart;
		
		private static var typeToShortName:Dictionary = new Dictionary(true);
		typeToShortName[BarChart] = BAR;
		typeToShortName[ColumnChart] = COLUMN;
		typeToShortName[LineChart] = LINE;
		typeToShortName[PieChart] = PIE;
		typeToShortName[StackedBarChart] = STACK_BAR;
		typeToShortName[StackedColumnChart] = STACK_COLUMN;
		
	//--------------------------------------
	//  Static Methods
	//--------------------------------------
		
		public static function getShortName(input:Object):String
		{
			if(!input)
			{
				return null;
			}
			
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
	}
}