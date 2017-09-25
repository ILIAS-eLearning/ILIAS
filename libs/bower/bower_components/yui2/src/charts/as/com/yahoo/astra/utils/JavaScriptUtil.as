package com.yahoo.astra.utils
{
	import flash.external.ExternalInterface;
	
	/**
	 * Utility functions for working with JavaScript and ExternalInterface.
	 * 
	 * @author Josh Tynjala
	 */
	public class JavaScriptUtil
	{
		/**
		 * Creates an ActionScript delegate for JavaScript functions that need
		 * to be called from components.
		 * 
		 * <p>Example: A List's labelFunction.</p>
		 *
		 * @param functionName		The name of the globally-accessible JavaScript function to call.
		 */
		public static function createCallbackFunction(functionName:String):Object
		{
			var delegate:Object = {functionName: functionName};
			delegate.callback = function(...rest:Array):String
			{	
				//we need to pass the variables like regular parameters
				rest.unshift(delegate.functionName);
				
				//OMG, this is confusing as heck, but clever, no?
				//apply() is deliciously awesome.
				return ExternalInterface.call.apply(ExternalInterface, rest);
			}
			return delegate;
		}
	}
}