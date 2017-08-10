/**
 * LineChart class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class LineChart
 * @uses YAHOO.widget.CartesianChart
 * @constructor
 * @param containerId {HTMLElement} Container element for the Flash Player instance.
 * @param dataSource {YAHOO.util.DataSource} DataSource instance.
 * @param attributes {object} (optional) Object literal of configuration values.
 */
YAHOO.widget.LineChart = function(containerId, dataSource, attributes)
{
	YAHOO.widget.LineChart.superclass.constructor.call(this, "line", containerId, dataSource, attributes);
};

YAHOO.lang.extend(YAHOO.widget.LineChart, YAHOO.widget.CartesianChart);

/**
 * ColumnChart class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class ColumnChart
 * @uses YAHOO.widget.CartesianChart
 * @constructor
 * @param containerId {HTMLElement} Container element for the Flash Player instance.
 * @param dataSource {YAHOO.util.DataSource} DataSource instance.
 * @param attributes {object} (optional) Object literal of configuration values.
 */
YAHOO.widget.ColumnChart = function(containerId, dataSource, attributes)
{
	YAHOO.widget.ColumnChart.superclass.constructor.call(this, "column", containerId, dataSource, attributes);
};

YAHOO.lang.extend(YAHOO.widget.ColumnChart, YAHOO.widget.CartesianChart);

/**
 * BarChart class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class BarChart
 * @uses YAHOO.widget.CartesianChart
 * @constructor
 * @param containerId {HTMLElement} Container element for the Flash Player instance.
 * @param dataSource {YAHOO.util.DataSource} DataSource instance.
 * @param attributes {object} (optional) Object literal of configuration values.
 */
YAHOO.widget.BarChart = function(containerId, dataSource, attributes)
{
	YAHOO.widget.BarChart.superclass.constructor.call(this, "bar", containerId, dataSource, attributes);
};

YAHOO.lang.extend(YAHOO.widget.BarChart, YAHOO.widget.CartesianChart);

/**
 * StackedColumnChart class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class StackedColumnChart
 * @uses YAHOO.widget.CartesianChart
 * @constructor
 * @param containerId {HTMLElement} Container element for the Flash Player instance.
 * @param dataSource {YAHOO.util.DataSource} DataSource instance.
 * @param attributes {object} (optional) Object literal of configuration values.
 */
YAHOO.widget.StackedColumnChart = function(containerId, dataSource, attributes)
{
	YAHOO.widget.StackedColumnChart.superclass.constructor.call(this, "stackcolumn", containerId, dataSource, attributes);
};

YAHOO.lang.extend(YAHOO.widget.StackedColumnChart, YAHOO.widget.CartesianChart);

/**
 * StackedBarChart class for the YUI Charts widget.
 *
 * @namespace YAHOO.widget
 * @class StackedBarChart
 * @uses YAHOO.widget.CartesianChart
 * @constructor
 * @param containerId {HTMLElement} Container element for the Flash Player instance.
 * @param dataSource {YAHOO.util.DataSource} DataSource instance.
 * @param attributes {object} (optional) Object literal of configuration values.
 */
YAHOO.widget.StackedBarChart = function(containerId, dataSource, attributes)
{
	YAHOO.widget.StackedBarChart.superclass.constructor.call(this, "stackbar", containerId, dataSource, attributes);
};

YAHOO.lang.extend(YAHOO.widget.StackedBarChart, YAHOO.widget.CartesianChart);