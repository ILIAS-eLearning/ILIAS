/**
 * A type of axis whose units are measured in numeric values.
 *
 * @namespace YAHOO.widget
 * @class NumericAxis
 * @extends YAHOO.widget.Axis
 * @constructor
 */
YAHOO.widget.NumericAxis = function()
{
	YAHOO.widget.NumericAxis.superclass.constructor.call(this);
};

YAHOO.lang.extend(YAHOO.widget.NumericAxis, YAHOO.widget.Axis,
{
	type: "numeric",
	
	/**
	 * The minimum value drawn by the axis. If not set explicitly, the axis minimum
	 * will be calculated automatically.
	 *
	 * @property minimum
	 * @type Number
	 */
	minimum: NaN,
	
	/**
	 * The maximum value drawn by the axis. If not set explicitly, the axis maximum
	 * will be calculated automatically.
	 *
	 * @property maximum
	 * @type Number
	 */
	maximum: NaN,
	
	/**
	 * The spacing between major intervals on this axis.
	 *
	 * @property majorUnit
	 * @type Number
	 */
	majorUnit: NaN,

	/**
	 * The spacing between minor intervals on this axis.
	 *
	 * @property minorUnit
	 * @type Number
	 */
	minorUnit: NaN,
	
	/**
	 * If true, the labels, ticks, gridlines, and other objects will snap to
	 * the nearest major or minor unit. If false, their position will be based
	 * on the minimum value.
	 *
	 * @property snapToUnits
	 * @type Boolean
	 */
	snapToUnits: true,
	
	/**
	 * Series that are stackable will only stack when this value is set to true.
	 *
	 * @property stackingEnabled
	 * @type Boolean
	 */
	stackingEnabled: false,

	/**
	 * If true, and the bounds are calculated automatically, either the minimum or
	 * maximum will be set to zero.
	 *
	 * @property alwaysShowZero
	 * @type Boolean
	 */
	alwaysShowZero: true,

	/**
	 * The scaling algorithm to use on this axis. May be "linear" or "logarithmic".
	 *
	 * @property scale
	 * @type String
	 */
	scale: "linear",
	
	/**
	 * Indicates whether to round the major unit.
	 * 
	 * @property roundMajorUnit
	 * @type Boolean
	 */
	roundMajorUnit: true, 
	
	/**
	 * Indicates whether to factor in the size of the labels when calculating a major unit.
	 *
	 * @property calculateByLabelSize
	 * @type Boolean
	 */
	calculateByLabelSize: true,
	
	/**
	 * Indicates the position of the axis relative to the chart
	 *
	 * @property position
	 * @type String
	 */
	position:"left",
	
	/**
	 * Indicates whether to extend maximum beyond data's maximum to the nearest 
	 * majorUnit.
	 *
	 * @property adjustMaximumByMajorUnit
	 * @type Boolean
	 */
	adjustMaximumByMajorUnit:true,
	
	/**
	 * Indicates whether to extend the minimum beyond data's minimum to the nearest
	 * majorUnit.
	 *
	 * @property adjustMinimumByMajorUnit
	 * @type Boolean
	 */
	adjustMinimumByMajorUnit:true
});