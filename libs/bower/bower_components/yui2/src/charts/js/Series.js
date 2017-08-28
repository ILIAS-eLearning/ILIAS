 /**
 * Functionality common to most series. Generally, a <code>Series</code> 
 * object shouldn't be instantiated directly. Instead, a subclass with a 
 * concrete implementation should be used.
 *
 * @namespace YAHOO.widget
 * @class Series
 * @constructor
 */
YAHOO.widget.Series = function() {};

YAHOO.widget.Series.prototype = 
{
	/**
	 * The type of series.
	 *
	 * @property type
	 * @type String
	 */
	type: null,
	
	/**
	 * The human-readable name of the series.
	 *
	 * @property displayName
	 * @type String
	 */
	displayName: null
};

/**
 * Functionality common to most series appearing in cartesian charts.
 * Generally, a <code>CartesianSeries</code> object shouldn't be
 * instantiated directly. Instead, a subclass with a concrete implementation
 * should be used.
 *
 * @namespace YAHOO.widget
 * @class CartesianSeries
 * @uses YAHOO.widget.Series
 * @constructor
 */
YAHOO.widget.CartesianSeries = function() 
{
	YAHOO.widget.CartesianSeries.superclass.constructor.call(this);
};

YAHOO.lang.extend(YAHOO.widget.CartesianSeries, YAHOO.widget.Series,
{
	/**
	 * The field used to access the x-axis value from the items from the data source.
	 *
	 * @property xField
	 * @type String
	 */
	xField: null,
	
	/**
	 * The field used to access the y-axis value from the items from the data source.
	 *
	 * @property yField
	 * @type String
	 */
	yField: null,
	
	/**
	 * Indicates which axis the series will bind to
	 *
	 * @property axis
	 * @type String
	 */
	axis: "primary",
	
	/**
	 * When a Legend is present, indicates whether the series will show in the legend.
	 * 
	 * @property showInLegend
	 * @type Boolean
	 */
	showInLegend: true
});

/**
 * ColumnSeries class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class ColumnSeries
 * @uses YAHOO.widget.CartesianSeries
 * @constructor
 */
YAHOO.widget.ColumnSeries = function() 
{
	YAHOO.widget.ColumnSeries.superclass.constructor.call(this);
};

YAHOO.lang.extend(YAHOO.widget.ColumnSeries, YAHOO.widget.CartesianSeries,
{
	type: "column"
});

/**
 * LineSeries class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class LineSeries
 * @uses YAHOO.widget.CartesianSeries
 * @constructor
 */
YAHOO.widget.LineSeries = function() 
{
	YAHOO.widget.LineSeries.superclass.constructor.call(this);
};

YAHOO.lang.extend(YAHOO.widget.LineSeries, YAHOO.widget.CartesianSeries,
{
	type: "line"
});


/**
 * BarSeries class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class BarSeries
 * @uses YAHOO.widget.CartesianSeries
 * @constructor
 */
YAHOO.widget.BarSeries = function() 
{
	YAHOO.widget.BarSeries.superclass.constructor.call(this);
};

YAHOO.lang.extend(YAHOO.widget.BarSeries, YAHOO.widget.CartesianSeries,
{
	type: "bar"
});


/**
 * PieSeries class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class PieSeries
 * @uses YAHOO.widget.Series
 * @constructor
 */
YAHOO.widget.PieSeries = function() 
{
	YAHOO.widget.PieSeries.superclass.constructor.call(this);
};

YAHOO.lang.extend(YAHOO.widget.PieSeries, YAHOO.widget.Series,
{
	type: "pie",
	
	/**
	 * The field used to access the data value from the items from the data source.
	 *
	 * @property dataField
	 * @type String
	 */
	dataField: null,
	
	/**
	 * The field used to access the category value from the items from the data source.
	 *
	 * @property categoryField
	 * @type String
	 */
	categoryField: null,

	/**
	 * A string reference to the globally-accessible function that may be called to
	 * determine each of the label values for this series. Also accepts function references.
	 *
	 * @property labelFunction
	 * @type String
	 */
	labelFunction: null
});

/**
 * StackedBarSeries class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class StackedBarSeries
 * @uses YAHOO.widget.CartesianSeries
 * @constructor
 */
YAHOO.widget.StackedBarSeries = function() 
{
	YAHOO.widget.StackedBarSeries.superclass.constructor.call(this);
};

YAHOO.lang.extend(YAHOO.widget.StackedBarSeries, YAHOO.widget.CartesianSeries,
{
	type: "stackbar"
});

/**
 * StackedColumnSeries class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class StackedColumnSeries
 * @uses YAHOO.widget.CartesianSeries
 * @constructor
 */
YAHOO.widget.StackedColumnSeries = function() 
{
	YAHOO.widget.StackedColumnSeries.superclass.constructor.call(this);
};

YAHOO.lang.extend(YAHOO.widget.StackedColumnSeries, YAHOO.widget.CartesianSeries,
{
	type: "stackcolumn"
});