package com.yahoo.astra.fl.utils
{
	import com.yahoo.astra.utils.InstanceFactory;
	
	import fl.core.UIComponent;
	import fl.managers.StyleManager;
	
	import flash.display.DisplayObject;
	import flash.utils.getDefinitionByName;
	import flash.utils.getQualifiedClassName;
	import flash.utils.getQualifiedSuperclassName;
	
	/**
	 * Utility functions for use with UIComponents.
	 * 
	 * @author Josh Tynjala
	 */
	public class UIComponentUtil
	{
		/**
		 * Using an input, such as a component style, tries to convert the input
		 * to a DisplayObject.
		 * 
		 * <p>Possible inputs include Class, a String representatation of a
		 * fully-qualified class name, Function, any existing instance of
		 * a DisplayObject, or InstanceFactory.</p>
		 * 
		 * @see com.yahoo.astra.utils.InstanceFactory
		 * 
		 * @param target	the parent of the new instance
		 * @param input		the object to convert to a DisplayObject instance
		 */
		public static function getDisplayObjectInstance(target:DisplayObject, input:Object):DisplayObject
		{
			if(input is InstanceFactory)
			{
				return InstanceFactory(input).createInstance() as DisplayObject;
			}
			//added Function as a special case because functions can be used with the new keyword
			else if(input is Class || input is Function)
			{ 
				return (new input()) as DisplayObject; 
			}
			else if(input is DisplayObject)
			{
				(input as DisplayObject).x = 0;
				(input as DisplayObject).y = 0;
				return input as DisplayObject;
			}

			var classDef:Object = null;
			try
			{
				classDef = getDefinitionByName(input.toString());
			}
			catch(e:Error)
			{
				try
				{
					classDef = target.loaderInfo.applicationDomain.getDefinition(input.toString()) as Object;
				}
				catch (e:Error)
				{
					// Nothing
				}
			}
			if(classDef == null)
			{
				return null;
			}
			return (new classDef()) as DisplayObject;
		}
		
		/**
		 * Gets the class of an object. If the object is a DisplayObject,
		 * may retrieve the class from the containing app domain.
		 * 
		 * @param target		A Class or a fully qualified class name (String).
		 */
		public static function getClassDefinition(target:Object):Class
		{
			if(target is Class)
			{ 
				return target as Class;
			}
			try
			{
				return getDefinitionByName(getQualifiedClassName(target)) as Class;
			}
			catch (e:Error)
			{
				if(target is DisplayObject)
				{
					try
					{
						return target.loaderInfo.applicationDomain.getDefinition(getQualifiedClassName(target)) as Class;
					}
					catch(e:Error)
					{
						//nothing
					}
				}
			}
			return null;
		}
		
		/**
		 * Works like getStyleValue() on UIComponent, except it makes component
		 * and shared styles available globally rather than just in the component's
		 * class.
		 * 
		 * @param target		the component for which to retrieve the style value
		 * @param styleName		the name of the style to retrieve
		 */
		public static function getStyleValue(target:UIComponent, styleName:String):Object
		{
			var value:Object = target.getStyle(styleName);
			value = value ? value : StyleManager.getComponentStyle(target, styleName);
			if(value)
			{
				return value;
			}
			var classDef:Class = UIComponentUtil.getClassDefinition(target);
			var defaultStyles:Object;
			
			//borrowed from fl.managers.StyleManager
			// Walk the inheritance chain looking for a default styles object.
			while(defaultStyles == null)
			{
				// Trick the strict compiler.
				if(classDef["getStyleDefinition"] != null)
				{
					defaultStyles = classDef["getStyleDefinition"]();
					break;
				}
				try
				{
					classDef = target.loaderInfo.applicationDomain.getDefinition(getQualifiedSuperclassName(classDef)) as Class;
				}
				catch(err:Error)
				{
					try
					{
						classDef = getDefinitionByName(getQualifiedSuperclassName(classDef)) as Class;
					}
					catch (e:Error)
					{
						defaultStyles = UIComponent.getStyleDefinition();
						break;
					}
				}
			}
				
			if(defaultStyles.hasOwnProperty(styleName))
			{
				return defaultStyles[styleName];
			}
			return null;
		}

	}
}