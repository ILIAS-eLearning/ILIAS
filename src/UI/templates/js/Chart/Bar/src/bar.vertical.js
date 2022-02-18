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

function determineToolTipYLabels(preferences, axisLabels, tooltips) {
  preferences.plugins.tooltip.callbacks.label = function(context) {
    var label = context.dataset.label + ": ";
    // Replace tooltip labels with custom values if defined
    if (tooltips[context.label] && tooltips[context.label][context.dataset.label]) {
      label = label + tooltips[context.label][context.dataset.label];
    }
    // If no custom tooltips are defined and no range is used as data point, use axis label
    else if (axisLabels[context.raw.y - context.chart.scales.y.start]) {
      label = label + axisLabels[context.raw.y - context.chart.scales.y.min];
    }
    // Use default tooltip value as fallback
    else {
      label = label + context.formattedValue;
    }
    return label;
  }
}

export default vertical;