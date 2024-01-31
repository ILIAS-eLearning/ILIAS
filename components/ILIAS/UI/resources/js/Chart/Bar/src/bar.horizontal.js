var horizontal = function() {

  var init = function(element, preferences, series, xLabels, tooltips){
    determineXLabels(preferences, xLabels);
    determineToolTipXLabels(preferences, xLabels, tooltips);

    var chart = new Chart(document.getElementById(element.id), {
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

function determineToolTipXLabels(preferences, axisLabels, tooltips) {
  preferences.plugins.tooltip.callbacks.label = function(context) {
    var label = context.dataset.label + ": ";
    // Replace tooltip labels with custom values if defined
    if (tooltips[context.label] && tooltips[context.label][context.dataset.label]) {
      label = label + tooltips[context.label][context.dataset.label];
    }
    // If no custom tooltips are defined and no range is used as data point, use axis label
    else if (axisLabels[context.raw.x - context.chart.scales.x.start]) {
      label = label + axisLabels[context.raw.x - context.chart.scales.x.min];
    }
    // Use default tooltip value as fallback
    else {
      label = label + context.formattedValue;
    }
    return label;
  }
}

export default horizontal;