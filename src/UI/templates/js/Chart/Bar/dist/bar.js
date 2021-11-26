var horizontal = function() {

  var init = function(id, preferences, series, xLabels, tooltips){
    determineXLabels(preferences, xLabels);
    determineToolTipXLabels(preferences, xLabels, tooltips);

    var chart = new Chart(document.getElementById(id), {
      type: 'bar',
      data: series,
      options: preferences,
    });
  };

  return {
    init: init
  };
}

var vertical = function() {

  var init = function(id, preferences, series, yLabels, tooltips){
    determineYLabels(preferences, yLabels);
    determineToolTipYLabels(preferences, yLabels, tooltips);

    var chart = new Chart(document.getElementById(id), {
      type: 'bar',
      data: series,
      options: preferences,
    });
  };

  return {
    init: init
  };
}

function determineXLabels(preferences, xLabels) {
  // Replace labels on x-axes with custom values if defined. If not, use default numeric values.
  if (Object.keys(xLabels).length != 0) {
    preferences.scales.x.ticks.callback = function (value, index, values) {
      return xLabels[index];
    }
  } else {
    preferences.scales.x.ticks.callback = function (value, index, values) {
      return value;
    }
  }
}

function determineYLabels(preferences, yLabels) {
  // Replace labels on x-axes with custom values if defined. If not, use default numeric values.
  if (Object.keys(yLabels).length != 0) {
    preferences.scales.y.ticks.callback = function (value, index, values) {
      return yLabels[index];
    }
  } else {
    preferences.scales.y.ticks.callback = function (value, index, values) {
      return value;
    }
  }
}

function determineToolTipXLabels(preferences, axisLabels, tooltips) {
  preferences.plugins.tooltip.callbacks.label = function(context) {
    var label = context.dataset.label + ": ";
    // Replace tooltip labels with custom values if defined
    if (tooltips[context.datasetIndex] && tooltips[context.datasetIndex][context.dataIndex]) {
      label = label + tooltips[context.datasetIndex][context.dataIndex];
    }
    // If no custom tooltips are defined and no range is used as data point, use axis label
    else if (axisLabels[context.raw - context.chart.scales.x.start]) {
      label = label + axisLabels[context.raw - context.chart.scales.x.min];
    }
    // Use default tooltip value as fallback
    else {
      label = label + context.formattedValue;
    }
    return label;
  }
}

function determineToolTipYLabels(preferences, axisLabels, tooltips) {
  preferences.plugins.tooltip.callbacks.label = function(context) {
    var label = context.dataset.label + ": ";
    // Replace tooltip labels with custom values if defined
    if (tooltips[context.datasetIndex] && tooltips[context.datasetIndex][context.dataIndex]) {
      label = label + tooltips[context.datasetIndex][context.dataIndex];
    }
    // If no custom tooltips are defined and no range is used as data point, use axis label
    else if (axisLabels[context.raw - context.chart.scales.y.start]) {
      label = label + axisLabels[context.raw - context.chart.scales.y.min];
    }
    // Use default tooltip value as fallback
    else {
      label = label + context.formattedValue;
    }
    return label;
  }
}


il = il || {};
il.UI = il.UI || {};
il.UI.chart = il.UI.chart || {};
il.UI.chart.bar = il.UI.chart.bar || {};

il.UI.chart.bar.horizontal = horizontal();
il.UI.chart.bar.vertical = vertical();
