package com.yahoo.astra.layout
{
	import com.yahoo.astra.layout.events.LayoutEvent;
	
	import flash.display.DisplayObject;
	import flash.events.Event;
	import flash.events.TextEvent;
	import flash.text.TextField;
	import flash.utils.Dictionary;
	import flash.utils.getDefinitionByName;
	import flash.utils.getQualifiedClassName;
	
	/**
	 * Generic layout manager for DisplayObjects.
	 * 
	 * @see flash.display.DisplayObject
	 * @see com.yahoo.astra.layout.ILayoutContainer
	 * 
	 * @author Josh Tynjala
	 */
	public class LayoutManager
	{
		
	//--------------------------------------
	//  Static Properties
	//--------------------------------------
	
		/**
		 * @private
		 * The classes registered with invalidating events.
		 */
		private static var classes:Array = [];
		
		/**
		 * @private
		 * A hash of class references to an Array of invalidating events for each class.
		 */
		private static var classToEvents:Dictionary = new Dictionary(true);
		
	//--------------------------------------
	//  Static Methods
	//--------------------------------------
	
		/**
		 * @private
		 */
		private static function initialize():void
		{
			//ILayoutContainer will always be available.
			registerInvalidatingEvents(ILayoutContainer, [LayoutEvent.LAYOUT_CHANGE]);
			
			//catch when a TextField's text changes (in case it has autoSize enabled)
			registerInvalidatingEvents(TextField, [Event.CHANGE]);
		}
		initialize();
		
		/**
		 * Allows users to specify events that invalidate the parent layout container
		 * when fired by instances of a specific class.
		 * 
		 * <p>For example, if <code>fl.core.UIComponent</code> fires the <code>resize</code>
		 * event when it is inside a layout container, the layout container will
		 * refresh its layout.</p>
		 * 
		 * @param source		The class that will fire invalidating events (ie. TextField)
		 * @param events		An Array of event constants (ie. Event.CHANGE)
		 * 
		 * @example The following code demonstrates how to specify a set of invalidating events:
		 * <listing version="3.0">
		 * LayoutManager.registerInvalidatingEvents( UIComponent, [ComponentEvent.RESIZE, ComponentEvent.MOVE] );
		 * </listing>
		 */
		public static function registerInvalidatingEvents(source:Class, events:Array):void
		{
			if(classes.indexOf(source) >= 0)
			{
				var savedEvents:Array = classToEvents[source];
				events = events.concat(savedEvents);
			}
			else
			{
				classes.push(source);
			}
			classToEvents[source] = events;
		}
		
		/**
		 * Determines if a particular DisplayObject's type has been registered
		 * with invalidating events.
		 */
		public static function hasInvalidatingEvents(target:DisplayObject):Boolean
		{
			var targetType:Class = getDefinitionByName(getQualifiedClassName(target)) as Class;
			return classes.indexOf(targetType) >= 0;
		}
		
		/**
		 * Called by an ILayoutContainer implementation when a child is added.
		 * If the child is an instance of a class with registered events, the
		 * layout system will listen for those events.
		 * 
		 * @see ILayoutContainer
		 */
		public static function registerContainerChild(child:DisplayObject):void
		{
			for each(var registeredClass:Class in classes)
			{
				if(child is registeredClass)
				{
					var events:Array = classToEvents[registeredClass];
					for each(var eventName:String in events)
					{
						//weak listener so that the layout system won't stop GC.
						child.addEventListener(eventName, invalidatingEventHandler, false, 0, true);
					}
				}
			}
		}
		
		/**
		 * Called by a layout container when a child is removed. If the child is
		 * an instance of a class with registered events, the layout system will
		 * stop listening to those events.
		 * 
		 * @see ILayoutContainer
		 */
		public static function unregisterContainerChild(child:DisplayObject):void
		{
			for each(var registeredClass:Class in classes)
			{
				if(child is registeredClass)
				{
					var events:Array = classToEvents[registeredClass];
					for each(var eventName:String in events)
					{
						child.removeEventListener(eventName, invalidatingEventHandler);
					}
				}
			}
		}
		
		/**
		 * If a DisplayObject that is placed into a layout container doesn't
		 * fire events for size changes, calling this function will allow
		 * its size to properly affect its parent layout.
		 * 
		 * @param target		The display object to resize
		 * @param width			The new width of the display object
		 * @param height		The new height of the display object
		 * 
		 * @example The following code demonstrates how to resize a
		 * DisplayObject that doesn't fire an event when it resizes:
		 * <listing version="3.0">
		 * LayoutManager.resize( mySprite, 100, 34 );
		 * </listing>
		 */
		public static function resize(target:DisplayObject, width:Number, height:Number):void
		{
			target.width = width;
			target.height = height;
			invalidateParentLayout(target);
		}
		
		/**
		 * Similar to <code>LayoutManager.resize()</code>, this function may be called
		 * to update any property of a DisplayObject and notify its parent layout
		 * container to refresh if no event normally indicates this is needed.
		 * 
		 * @param target		The display object whose property will be changed
		 * @param property		The name of the property.
		 * @param value			The value of the property
		 * 
		 * @example The following code demonstrates how to change a
		 * DisplayObject's property when that DisplayObject doesn't fire an
		 * event when the property changes:
		 * <listing version="3.0">
		 * LayoutManager.update(mySprite, "transform", new Transform());
		 * </listing>
		 */
		public static function update(target:DisplayObject, property:String, value:Object):void
		{
			if(!target.hasOwnProperty(property)) return;
			
			target[property] = value;
			invalidateParentLayout(target);
		}
		
		/**
		 * If the target's parent is a layout container, that parent will be
		 * informed that it needs to update the layout.
		 */
		public static function invalidateParentLayout(target:DisplayObject):void
		{
			var parent:ILayoutContainer = target.parent as ILayoutContainer;
			if(!parent) return;
			parent.invalidateLayout();
		}
		
		/**
		 * @private
		 * 
		 * Generic event handler for invalidating events. If the target's parent
		 * is a layout container, that parent will be informed that its layout
		 * needs to be updated. Any standard event is supported.
		 */
		private static function invalidatingEventHandler(event:Event):void
		{
			var child:DisplayObject = DisplayObject(event.currentTarget);
			invalidateParentLayout(child); 
		}

	}
}