package com.yahoo.yui
{
	import flash.external.ExternalInterface;

	public class YUILogger
	{	
		public static function log(message:Object, category:String = "info"):void
		{
			if(ExternalInterface.available)
			{
				ExternalInterface.call("YAHOO.log", message.toString(), category);
			}
		}
		
	}
	
}
