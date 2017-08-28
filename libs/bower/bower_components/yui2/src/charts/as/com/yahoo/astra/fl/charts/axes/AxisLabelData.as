package com.yahoo.astra.fl.charts.axes
{	
	/**
	 * Dimension data shared by axes of the same type
	 */
	public class AxisLabelData
	{	
		/**
		 * Constructor
		 */
		public function AxisLabelData()
		{
		}
		
		/**
		 * Maximum number of labels that can appear on a given axis
		 */
		public var maxLabels:Number = 2;
		
		/**
		 * Height of the largest possible axis label based on series data, rotation and text formatting
		 */
		public var maxLabelHeight:Number = 0;
		
		/**
		 * Width of the largest possible axis label based on the series data, rotation and text formatting
		 */
		public var maxLabelWidth:Number = 0;
		
		/**
		 * @private
		 * Placeholder for the leftLabelOffset
		 */
		private var _leftLabelOffset:Number = 0;
		
		/**
		 * Maximum possible overflow on the left side of the axis
		 */
		public function get leftLabelOffset():Number
		{
			return _leftLabelOffset;
		}
		
		/**
		 * @private (setter)
		 */
		public function set leftLabelOffset(value:Number):void
		{
			_leftLabelOffset = Math.max(_leftLabelOffset, value);
		}
		
		/**
		 * @private
		 * Placeholder for the rightLabelOffset
		 */	
		private var _rightLabelOffset:Number = 0;
		
		/**
		 * Maximum possible overflow on the right side of the axis
		 */
		public function get rightLabelOffset():Number
		{
			return _rightLabelOffset;
		}
		
		/**
		 * @private (setter)
		 */
		public function set rightLabelOffset(value:Number):void
		{
			_rightLabelOffset = Math.max(_rightLabelOffset, value);
		}
		
		/**
		 * @private 
		 * Placeholder for the topLabelOffset
		 */
		private var _topLabelOffset:Number = 0;
		
		/**
		 * Maximum possible overflow on the top side of the axis
		 */
		public function get topLabelOffset():Number
		{
			return _topLabelOffset;
		}
		
		/**
		 * @private (setter)
		 */
		public function set topLabelOffset(value:Number):void
		{
			_topLabelOffset = Math.max(_topLabelOffset, value);
		}
		
		/**
		 * @private
		 * Placeholder for the bottomLabelOffset
		 */
		private var _bottomLabelOffset:Number = 0;
		
		/**
		 * Maximum possible overflow on the bottom side of the axis
		 */
		public function get bottomLabelOffset():Number
		{
			return _bottomLabelOffset;
		}
		
		/**
		 * @private (setter)
		 */
		public function set bottomLabelOffset(value:Number):void
		{
			_bottomLabelOffset = Math.max(_bottomLabelOffset, value);
		}
		
	}
}

