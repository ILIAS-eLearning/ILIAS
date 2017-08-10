/**
 * PieChart class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class PieChart
 * @uses YAHOO.widget.Chart
 * @constructor
 * @param containerId {HTMLElement} Container element for the Flash Player instance.
 * @param dataSource {YAHOO.util.DataSource} DataSource instance.
 * @param attributes {object} (optional) Object literal of configuration values.
 */
YAHOO.widget.PieChart = function(containerId, dataSource, attributes)
{
	YAHOO.widget.PieChart.superclass.constructor.call(this, "pie", containerId, dataSource, attributes);
};

YAHOO.lang.extend(YAHOO.widget.PieChart, YAHOO.widget.Chart,
{
	/**
	 * Initializes the attributes.
	 *
	 * @method _initAttributes
	 * @private
	 */
	_initAttributes: function(attributes)
	{	
		YAHOO.widget.PieChart.superclass._initAttributes.call(this, attributes);
		
		/**
		 * @attribute dataField
		 * @description The field in each item that corresponds to the data value.
		 * @type String
		 */
		this.setAttributeConfig("dataField",
		{
			validator: YAHOO.lang.isString,
			method: this._setDataField,
			getter: this._getDataField
		});
   
		/**
		 * @attribute categoryField
		 * @description The field in each item that corresponds to the category value.
		 * @type String
		 */
		this.setAttributeConfig("categoryField",
		{
			validator: YAHOO.lang.isString,
			method: this._setCategoryField,
			getter: this._getCategoryField
		});
	},

	/**
	 * Getter for the dataField attribute.
	 *
	 * @method _getDataField
	 * @private
	 */
	_getDataField: function()
	{
		return this._swf.getDataField();
	},

	/**
	 * Setter for the dataField attribute.
	 *
	 * @method _setDataField
	 * @private
	 */
	_setDataField: function(value)
	{
		this._swf.setDataField(value);
	},

	/**
	 * Getter for the categoryField attribute.
	 *
	 * @method _getCategoryField
	 * @private
	 */
	_getCategoryField: function()
	{
		return this._swf.getCategoryField();
	},

	/**
	 * Setter for the categoryField attribute.
	 *
	 * @method _setCategoryField
	 * @private
	 */
	_setCategoryField: function(value)
	{
		this._swf.setCategoryField(value);
	}
});