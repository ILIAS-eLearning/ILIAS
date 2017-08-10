package com.yahoo.astra.layout.modes
{
	/**
	 * Constraint values available in BorderLayout.
	 * 
	 * @see BorderLayout
	 * @author Josh Tynjala
	 */
	public class BorderConstraints
	{
		
	//--------------------------------------
	//  Static Properties
	//--------------------------------------
	
		/**
		 * The target will be constrained to the top edge. Its width will be
		 * altered to fit to the width of the container. Its height will
		 * remain unchanged.
		 * 
		 * <p>Consider the <code>TOP</code> constraint to work like a page's
		 * header. It appears above all other constrained children, with no other
		 * children appearing to the left or right. Multiple <code>TOP</code> constraints
		 * will be arranged vertically from the top down in the order that they
		 * were registered as clients of the BorderLayout algorithm.</p>
		 */
		public static const TOP:String = "top";
	
		/**
		 * The target will be constrained to the bottom edge. Its width will be
		 * altered to fit to the width of the container. Its height will
		 * remain unchanged.
		 * 
		 * <p>Consider the <code>BOTTOM</code> constraint to work like a page's
		 * footer. It appears below all other constrained children, with no other
		 * children appearing to the left or right. Multiple <code>BOTTOM</code> constraints
		 * will be arranged vertically from the bottom up in the order that they
		 * were registered as clients of the BorderLayout algorithm.</p>
		 */
		public static const BOTTOM:String = "bottom";
		
		/**
		 * The target will be constrained to the left edge. It will appear
		 * below any items constrained to the top and above items constrained to
		 * the bottom. Its height will be altered to fill the remaining height
		 * of the container (after the TOP and BOTTOM constraints are measured),
		 * and its width will remain unchanged.
		 * 
		 * <p>Consinder the <code>LEFT</code> constraint to work like a page's
		 * sidebar that is aligned to the left. It appears to the left of all other
		 * children, with only the top and bottom constraints taking precendence.
		 * Multiple <code>LEFT</code> constraints will be arranged horizontally
		 * from left to right in the order that they were registered as clients
		 * of the BorderLayout algorithm.</p>
		 */
		public static const LEFT:String = "left";
		
		/**
		 * The target will be constrained to the right edge. It will appear
		 * below any items constrained to the top and above items constrained to
		 * the bottom. Its height will be altered to fill the remaining height
		 * of the container (after the TOP and BOTTOM constraints are measured),
		 * and its width will remain unchanged.
		 * 
		 * <p>Consinder the <code>RIGHT</code> constraint to work like a page's
		 * sidebar that is aligned to the right. It appears to the right of all other
		 * children, with only the top and bottom constraints taking precendence.
		 * Multiple <code>RIGHT</code> constraints will be arranged horizontally
		 * from right to left in the order that they were registered as clients
		 * of the BorderLayout algorithm.</p>
		 */
		public static const RIGHT:String = "right";
		
		/**
		 * The target will be constrained to the center of the container. It
		 * will appear between all other constrained children. Its height will be
		 * altered to fill the remaining height of the container (after the TOP
		 * and BOTTOM constraints are measured) and its width will be altered to
		 * fill the remaining width of the container (after the LEFT and RIGHT
		 * constraints are measured).
		 * 
		 * <p>Consider the <code>CENTER</code> constraint to work like a page's
		 * primary content. It appears in between all other constraints and changes
		 * size to fill the remaining area (after all other constraints are
		 * measured). Multiple <code>CENTER</code> constraints will be arranged
		 * vertically from top down starting from the bottom edge of the
		 * <code>TOP</code> constraints to the top edge of any <code>BOTTOM</code>
		 * constraints.</p>
		 */
		public static const CENTER:String = "center";
	}
}