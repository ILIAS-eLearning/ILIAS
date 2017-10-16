/**
 * A type of axis that displays items in categories.
 *
 * @namespace YAHOO.widget
 * @class CategoryAxis
 * @constructor
 */
YAHOO.widget.CategoryAxis = function()
{
	YAHOO.widget.CategoryAxis.superclass.constructor.call(this);
};

YAHOO.lang.extend(YAHOO.widget.CategoryAxis, YAHOO.widget.Axis,
{
	type: "category",
	
	/**
	 * A list of category names to display along this axis.
	 *
	 * @property categoryNames
	 * @type Array
	 */
	categoryNames: null,
	
	/**
	 * Indicates whether or not to calculate the number of categories (ticks and labels)
	 * when there is not enough room to display all labels on the axis. If set to true, the axis 
	 * will determine the number of categories to plot. If not, all categories will be plotted.
	 *
	 * @property calculateCategoryCount
	 * @type Boolean
	 */
	calculateCategoryCount: false 
});