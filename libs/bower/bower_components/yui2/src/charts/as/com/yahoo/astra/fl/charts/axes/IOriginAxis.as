package com.yahoo.astra.fl.charts.axes
{
	/**
	 * An axis with an origin.
	 * 
	 * @author Josh Tynjala
	 */
	public interface IOriginAxis extends IAxis
	{
		/**
		 * Returns the value of the origin. This is not the position of the
		 * origin. To get the origin's position, pass the origin value to
		 * valueToLocal().
		 * 
		 * Note: This value may not be the true origin value. It may be a
		 * minimum or maximum value if the actual origin is not visible.
		 * 
		 * @see IAxis#valueToLocal()
		 */
		function get origin():Object;
	}
}