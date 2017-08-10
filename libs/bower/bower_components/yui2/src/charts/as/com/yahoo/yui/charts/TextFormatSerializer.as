package com.yahoo.yui.charts
{
	import flash.text.Font;
	import flash.text.TextFormat;
	
	public class TextFormatSerializer
	{
		public static function writeTextFormat(format:TextFormat):Object
		{
			var output:Object = {};
			output.name = format.font;
			output.bold = format.bold;
			output.italic = format.italic;
			output.underline = format.underline;
			output.size = format.size;
			output.color = format.color;
			
			return output;
		}
		
		public static function readTextFormat(input:Object):TextFormat
		{
			var format:TextFormat = new TextFormat();
			if(input.name)
			{
				format.font = parseFontName(input.name);
			}
			else format.font = "Verdana";
			format.bold = input.bold;
			format.italic = input.italic;
			format.underline = input.underline;
			if(input.size != null)
			{
				format.size = input.size;
			}
			if(input.color != null)
			{
				format.color = input.color;
			}
			else format.color = 0x000000;
			
			return format;
		}
		
		
		private static var fontNameToFind:String;
		
		private static function parseFontName(value:String):String
		{
			var availableFonts:Array = Font.enumerateFonts(true);
			var names:Array = value.split(/\s*,\s*/);
		
			//please note: Flash Player has an undocumented feature
			//where "name1,name2,name3" notation is accepted by TextFormat.font.
			//but since it isn't documented, I don't dare use it.
			
			var nameCount:int = names.length;
			for(var i:int = 0; i < nameCount; i++)
			{
				var name:String = String(names[i]);
				fontNameToFind = name.toLowerCase();
				if(fontNameToFind == "sans-serif")
				{
					return "_sans";
				}
				else if(fontNameToFind == "serif")
				{
					return "_serif"
				}
				else if(availableFonts.filter(fontNameFilter).length > 0)
				{
					return name;
				}
			}
			//worst case scenario, return _serif
			return "_serif";
		}
		
		private static function fontNameFilter(item:Font, index:int, array:Array):Boolean
		{
			
			return item.fontName.toLowerCase() == fontNameToFind;
		}
	}
}