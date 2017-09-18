package com.yahoo.astra.utils
{
	/**
	 * Utility functions for common geometric operations.
	 * 
	 * @author Josh Tynjala
	 */
	public class GeomUtil
	{
		/**
		 * Converts an angle from radians to degrees.
		 * 
		 * @param radians		The angle in radians
		 * @return				The angle in degrees
		 */
		public static function radiansToDegrees(radians:Number):Number
		{
			return radians * 180 / Math.PI;
		}
		
		/**
		 * Converts an angle from degrees to radians.
		 * 
		 * @param degrees		The angle in degrees
		 * @return				The angle in radians
		 */
		public static function degreesToRadians(degrees:Number):Number
		{
			return degrees * Math.PI / 180;
		}

	}
}