package com.yahoo.astra.layout.modes
{
	import flash.events.IEventDispatcher;
	import flash.geom.Rectangle;
	
	/**
	 * Defines the properties and functions required
	 * for layout modes used by ILayoutContainer.
	 * 
	 * @author Josh Tynjala
	 */
	public interface ILayoutMode extends IEventDispatcher
	{
		
	//--------------------------------------
	//  Methods
	//--------------------------------------
	
		/**
		 * The DisplayObjects in the input parameter will be positioned and sized
		 * based on a specified rectangle. There is no requirement that the
		 * display objects remain entirely within the rectangle.
		 * 
		 * <p>Returns the actual rectangular region in which the laid out
		 * children will appear. This may be larger or smaller than the
		 * suggested rectangle. This returned value is expected to be used by
		 * container components to determine if scrollbars or other navigation
		 * controls are needed.</p>
		 * 
		 * @param displayObjects	An Array of DisplayObjects to be laid out.
		 * @param bounds			The rectangular region in which the display objects should be placed.
		 * @return					The actual region in which the display objects are contained.
		 */
		function layoutObjects(displayObjects:Array, bounds:Rectangle):Rectangle;
	}
}