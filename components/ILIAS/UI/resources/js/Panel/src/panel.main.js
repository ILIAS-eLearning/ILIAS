const panel = function () {
  const performAsync = function (action) {
    if (action !== null) {
      fetch(action, {
        method: 'GET',
      });
    }
  };

  const performSignal = function (id, signal) {
    const b = document.getElementById(id);
    if (signal !== null) {
      // eslint-disable-next-line no-undef
      $(b).trigger(signal.signal_id, {
        id: signal.signal_id,
        event: signal.event,
        triggerer: b,
        options: signal.options,
      });
    }
  };

  const showAndHideElementsForCollapse = function (id, type) {
    const p = document.getElementById(id).closest('.panel-expandable');
    p.querySelector('[data-collapse-button-visibility]').dataset.collapseButtonVisibility = '0';
    p.querySelector('[data-expand-button-visibility]').dataset.expandButtonVisibility = '1';
    p.querySelector('.panel-viewcontrols').dataset.vcExpanded = '0';
    if (type === 'standard') {
      p.querySelector('.panel-body').dataset.bodyExpanded = '0';
    } else if (type === 'listing') {
      p.querySelector('.panel-listing-body').dataset.bodyExpanded = '0';
    }
  };

  const onCollapseCmdAction = function (event, id, type, action) {
    showAndHideElementsForCollapse(id, type);
    performAsync(action);
  };

  const onCollapseCmdSignal = function (event, id, type, signal) {
    showAndHideElementsForCollapse(id, type);
    performSignal(id, signal);
  };

  const showAndHideElementsForExpand = function (id, type) {
    const p = document.getElementById(id).closest('.panel-expandable');
    p.querySelector('[data-expand-button-visibility]').dataset.expandButtonVisibility = '0';
    p.querySelector('[data-collapse-button-visibility]').dataset.collapseButtonVisibility = '1';
    p.querySelector('.panel-viewcontrols').dataset.vcExpanded = '1';
    if (type === 'standard') {
      p.querySelector('.panel-body').dataset.bodyExpanded = '1';
    } else if (type === 'listing') {
      p.querySelector('.panel-listing-body').dataset.bodyExpanded = '1';
    }
  };

  const onExpandCmdAction = function (event, id, type, action) {
    showAndHideElementsForExpand(id, type);
    performAsync(action);
  };

  const onExpandCmdSignal = function (event, id, type, signal) {
    showAndHideElementsForExpand(id, type);
    performSignal(id, signal);
  };

  /**
     * Public interface
     */
  return {
    onCollapseCmdAction,
    onCollapseCmdSignal,
    onExpandCmdAction,
    onExpandCmdSignal,
  };
};

export default panel;
