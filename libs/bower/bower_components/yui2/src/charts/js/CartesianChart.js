/**
 * CartesianChart class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class CartesianChart
 * @uses YAHOO.widget.Chart
 * @constructor
 * @param type {String} The char type. May be "line", "column", or "bar"
 * @param containerId {HTMLElement} Container element for the Flash Player instance.
 * @param dataSource {YAHOO.util.DataSource} DataSource instance.
 * @param attributes {object} (optional) Object literal of configuration values.
 */
 YAHOO.widget.CartesianChart = function(type, containerId, dataSource, attributes)
{
	YAHOO.widget.CartesianChart.superclass.constructor.call(this, type, containerId, dataSource, attributes);
};

YAHOO.lang.extend(YAHOO.widget.CartesianChart, YAHOO.widget.Chart,
{
	/**
	 * Stores a reference to the xAxis labelFunction created by
	 * YAHOO.widget.Chart.createProxyFunction()
	 * @property _xAxisLabelFunctions
	 * @type String
	 * @private
	 */
	_xAxisLabelFunctions: [],
	
	/**
	 * Stores a reference to the yAxis labelFunctions created by
	 * YAHOO.widget.Chart.createProxyFunction()
	 * @property _yAxisLabelFunctions
	 * @type Array
	 * @private
	 */
	_yAxisLabelFunctions: [],
	
	destroy: function()
	{
		//remove proxy functions
		this._removeAxisFunctions(this._xAxisLabelFunctions);
		this._removeAxisFunctions(this._yAxisLabelFunctions);
		
		//call last
		YAHOO.widget.CartesianChart.superclass.destroy.call(this);
	},
	
	/**
	 * Initializes the attributes.
	 *
	 * @method _initAttributes
	 * @private
	 */
	_initAttributes: function(attributes)
	{	
		YAHOO.widget.CartesianChart.superclass._initAttributes.call(this, attributes);
		
		/**
		 * @attribute xField
		 * @description The field in each item that corresponds to a value on the x axis.
		 * @type String
		 */
		this.setAttributeConfig("xField",
		{
			validator: YAHOO.lang.isString,
			method: this._setXField,
			getter: this._getXField
		});

		/**
		 * @attribute yField
		 * @description The field in each item that corresponds to a value on the x axis.
		 * @type String
		 */
		this.setAttributeConfig("yField",
		{
			validator: YAHOO.lang.isString,
			method: this._setYField,
			getter: this._getYField
		});

		/**
		 * @attribute xAxis
		 * @description A custom configuration for the horizontal x axis.
		 * @type Axis
		 */
		this.setAttributeConfig("xAxis",
		{
			method: this._setXAxis
		});
		
		/**
		 * @attribute xAxes
		 * @description Custom configurations for the horizontal x axes.
		 * @type Array
		 */		
		this.setAttributeConfig("xAxes",
		{
			method: this._setXAxes
		});	

		/**
		 * @attribute yAxis
		 * @description A custom configuration for the vertical y axis.
		 * @type Axis
		 */
		this.setAttributeConfig("yAxis",
		{
			method: this._setYAxis
		});
		
		/**
		 * @attribute yAxes
		 * @description Custom configurations for the vertical y axes.
		 * @type Array
		 */		
		this.setAttributeConfig("yAxes",
		{
			method: this._setYAxes
		});	
		
		/**
		 * @attribute constrainViewport
		 * @description Determines whether the viewport is constrained to prevent series data from overflow.
		 * @type Boolean
		 */
		this.setAttributeConfig("constrainViewport",
		{
			method: this._setConstrainViewport
		});	
	},

	/**
	 * Getter for the xField attribute.
	 *
	 * @method _getXField
	 * @private
	 */
	_getXField: function()
	{
		return this._swf.getHorizontalField();
	},

	/**
	 * Setter for the xField attribute.
	 *
	 * @method _setXField
	 * @private
	 */
	_setXField: function(value)
	{
		this._swf.setHorizontalField(value);
	},

	/**
	 * Getter for the yField attribute.
	 *
	 * @method _getYField
	 * @private
	 */
	_getYField: function()
	{
		return this._swf.getVerticalField();
	},

	/**
	 * Setter for the yField attribute.
	 *
	 * @method _setYField
	 * @private
	 */
	_setYField: function(value)
	{
		this._swf.setVerticalField(value);
	},
	
	/**
	 * Receives an axis object, creates a proxy function for 
	 * the labelFunction and returns the updated object. 
	 *
	 * @method _getClonedAxis
	 * @private
	 */
	_getClonedAxis: function(value)
	{
		var clonedAxis = {};
		for(var prop in value)
		{
			if(prop == "labelFunction")
			{
				if(value.labelFunction && value.labelFunction !== null)
				{
					clonedAxis.labelFunction = YAHOO.widget.Chart.getFunctionReference(value.labelFunction);
				}
			}
			else
			{
				clonedAxis[prop] = value[prop];
			}
		}
		return clonedAxis;
	},
	
	/**
	 * Removes axis functions contained in an array
	 * 
	 * @method _removeAxisFunctions
	 * @private
	 */
	_removeAxisFunctions: function(axisFunctions)
	{
		if(axisFunctions && axisFunctions.length > 0)
		{
			var len = axisFunctions.length;
			for(var i = 0; i < len; i++)
			{
				if(axisFunctions[i] !== null)
				{
					YAHOO.widget.Chart.removeProxyFunction(axisFunctions[i]);
				}
			}
			axisFunctions = [];
		}
	},	
	
	/**
	 * Setter for the xAxis attribute.
	 *
	 * @method _setXAxis
	 * @private
	 */
	_setXAxis: function(value)
	{
		if(value.position != "bottom" && value.position != "top") value.position = "bottom";
		this._removeAxisFunctions(this._xAxisLabelFunctions);
		value = this._getClonedAxis(value);
		this._xAxisLabelFunctions.push(value.labelFunction);
		this._swf.setHorizontalAxis(value);
	},
	
	/**
	 * Setter for the xAxes attribute
	 *
	 * @method _setXAxes
	 * @private
	 */
	_setXAxes: function(value)
	{
		this._removeAxisFunctions(this._xAxisLabelFunctions);
		var len = value.length;
		for(var i = 0; i < len; i++)
		{
			if(value[i].position == "left") value[i].position = "bottom";
			value[i] = this._getClonedAxis(value[i]);
			if(value[i].labelFunction) this._xAxisLabelFunctions.push(value[i].labelFunction);
			this._swf.setHorizontalAxis(value[i]);
		}
	},

	/**
	 * Setter for the yAxis attribute.
	 *
	 * @method _setYAxis
	 * @private
	 */
	_setYAxis: function(value)
	{
		this._removeAxisFunctions(this._yAxisLabelFunctions);
		value = this._getClonedAxis(value);
		this._yAxisLabelFunctions.push(value.labelFunction);		
		this._swf.setVerticalAxis(value);
	},
	
	/**
	 * Setter for the yAxes attribute.
	 *
	 * @method _setYAxes
	 * @private
	 */	
	_setYAxes: function(value)
	{
		this._removeAxisFunctions(this._yAxisLabelFunctions);
		var len = value.length;
		for(var i = 0; i < len; i++)
		{
			value[i] = this._getClonedAxis(value[i]);
			if(value[i].labelFunction) this._yAxisLabelFunctions.push(value[i].labelFunction);
			this._swf.setVerticalAxis(value[i]);
		}		
	},
	
	/**
	 * Setter for the constrainViewport attribute
	 *
	 * @method _setConstrainViewport
	 * @private
	 */
	_setConstrainViewport: function(value)
	{
		this._swf.setConstrainViewport(value);
	},
	
	/**
	 * Sets the style object for a single series based on its index
	 * 
	 * @method setSeriesStylesByIndex
	 * @param index {Number} The position within the series definition to apply the style
	 * @param style {object} Style object to be applied to the selected series
	 */
	setSeriesStylesByIndex:function(index, style)
	{
		style = YAHOO.lang.JSON.stringify(style);
		if(this._swf && this._swf.setSeriesStylesByIndex) this._swf.setSeriesStylesByIndex(index, style);
	}
});