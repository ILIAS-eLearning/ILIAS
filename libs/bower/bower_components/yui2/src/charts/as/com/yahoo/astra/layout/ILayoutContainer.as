package com.yahoo.astra.layout
{
	import com.yahoo.astra.layout.modes.ILayoutMode;
	
	import flash.display.DisplayObject;
	import flash.events.IEventDispatcher;
	
	/**
	 * Defines properties and methods required for layout containers
	 * to work with LayoutManager.
	 * 
	 * <p>Implementations must be a subclass of DisplayObjectContainer.</p>
	 * 
	 * @see LayoutManager
	 * @see LayoutContainer
	 * @see flash.display.DisplayObjectContainer
	 * 
	 * @author Josh Tynjala
	 */
	public interface ILayoutContainer extends IEventDispatcher
	{
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * The width of the content displayed by the layout container.
		 */
		function get contentWidth():Number;
		
		/**
		 * The height of the content displayed by the layout container.
		 */
		function get contentHeight():Number;
			
		/**
		 * The layout algorithm used to display children of the layout container.
		 * 
		 * @see modes/package-detail.html Available Layout Modes (com.yahoo.astra.layout.modes)
		 */
		function get layoutMode():ILayoutMode;
		
		/**
		 * @private
		 */
		function set layoutMode(value:ILayoutMode):void;
		
	//--------------------------------------
	//  Methods
	//--------------------------------------
		
		/**
		 * Informs the layout container that it should update the layout of its
		 * children.
		 */
		function invalidateLayout():void;
		
		/**
		 * Immediately updates the layout of the container's children.
		 */
		function validateLayout():void;
	}
}