il = il || {};
il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

il.UI.table.presentation = (function () {
  const clsCollapsed = 'collapsed';
  const clsExpanded = 'expanded';

  const expandRow = function (id) {
    const row = document.getElementById(id);
    row.querySelector('.il-table-presentation-row-controls-expander').style.display = 'none';
    row.querySelector('.il-table-presentation-row-controls-collapser').style.display = 'block';
    row.querySelector('.il-table-presentation-row-expanded').style.display = 'block';
    row.querySelector('.il-table-presentation-row-header-fields').style.display = 'none';
    row.classList.remove(clsCollapsed);
    row.classList.add(clsExpanded);
  };

  const collapseRow = function (id) {
    const row = document.getElementById(id);
    row.querySelector('.il-table-presentation-row-controls-expander').style.display = 'block';
    row.querySelector('.il-table-presentation-row-controls-collapser').style.display = 'none';
    row.querySelector('.il-table-presentation-row-expanded').style.display = 'none';
    row.querySelector('.il-table-presentation-row-header-fields').style.display = 'block';
    row.classList.remove(clsExpanded);
    row.classList.add(clsCollapsed);
  };

  const toggleRow = function (id) {
    const row = document.getElementById(id);
    const elements = [
      row.querySelector('.il-table-presentation-row-controls-expander'),
      row.querySelector('.il-table-presentation-row-controls-collapser'),
      row.querySelector('.il-table-presentation-row-expanded'),
      row.querySelector('.il-table-presentation-row-header-fields'),
    ];
    let i = 0;
    for (i; i < elements.length; i += 1) {
      const el = elements[i];
      const mode = (el.style.display === 'none') ? 'block' : 'none';
      el.style.display = mode;
    }

    if (row.classList.contains(clsExpanded)) {
      row.classList.remove(clsExpanded);
      row.classList.add(clsCollapsed);
    } else {
      row.classList.remove(clsCollapsed);
      row.classList.add(clsExpanded);
    }
  };
  const expandAll = function (id, signalData) {
    const rows = document.querySelectorAll(`#${id} .il-table-presentation-row`);
    const expanders = document.querySelectorAll(
      `#${id} .il-table-presentation-viewcontrols a.glyph`,
    );
    if (signalData.options.expand) {
      rows.forEach((row) => this.expandRow(row.id));
      expanders[0].style.display = 'none';
      expanders[1].style.display = 'block';
    } else {
      rows.forEach((row) => this.collapseRow(row.id));
      expanders[0].style.display = 'block';
      expanders[1].style.display = 'none';
    }
  };

  return {
    expandRow,
    collapseRow,
    toggleRow,
    expandAll,
  };
}());
