/**
 * @type {number}
 */
const KEY_LEFT = 37;

/**
 * @type {number}
 */
const KEY_UP = 38;

/**
 * @type {number}
 */
const KEY_RIGHT = 39;

/**
 * @type {number}
 */
const KEY_DOWN = 40;

class DataTable {
  /**
   * @type {jQuery}
   */
  #jquery;

  /**
   * @type {actionId: string, rowId: string}
   */
  #signalConstants;

  /**
   * @type {HTMLDivElement}
   */
  #component;

  /**
   * @type {HTMLTableElement}
   */
  #table;

  /**
   * @type {array<string, {async: bool, urlBuilder: URLBuilder, urlTokens: Map}>}
   */
  #actionsRegistry;

  /**
   * @param {jQuery} jquery
   * @param {string} optActionId
   * @param {string} optRowId
   * @param {string} tableId
   * @throws {Error} if DOM element is missing
   */
  constructor(jquery, optActionId, optRowId, componentId) {
    this.#component = document.getElementById(componentId);
    if (this.#component === null) {
      throw new Error(`Could not find a DataTable for id '${componentId}'.`);
    }
    this.#table = this.#component.getElementsByTagName('table').item(0);
    if (this.#table === null) {
      throw new Error('There is no <table> in the component\'s HTML.');
    }
    this.#jquery = jquery;
    this.#signalConstants = {
      actionId: optActionId,
      rowId: optRowId,
    };
    this.#actionsRegistry = {};

    this.#component.addEventListener('keydown', (event) => this.navigateCellsWithArrowKeys(event));
  }

  /**
   * @param {string} actionId
   * @param {bool} async
   * @param {URLBuilder} urlBuilder
   * @param {Map} urlTokens
   * @return {void}
   */
  registerAction(actionId, async, urlBuilder, urlTokens) {
    this.#actionsRegistry[actionId] = {
      async,
      urlBuilder,
      urlTokens,
    };
  }

  /**
   * @param {bool} state
   * @return {void}
   */
  selectAll(state) {
    const cols = this.#table.getElementsByClassName('c-table-data__row-selector');
    const selectorAll = this.#table.getElementsByClassName('c-table-data__selection_all').item(0);
    const selectorNone = this.#table.getElementsByClassName('c-table-data__selection_none').item(0);

    for (let i = 0; i < cols.length; i += 1) {
      const col = cols[i];
      col.checked = state;
    }

    if (state) {
      selectorAll.style.display = 'none';
      selectorNone.style.display = 'block';
    } else {
      selectorAll.style.display = 'block';
      selectorNone.style.display = 'none';
    }
  }

  /**
   * @param {string} tableId
   * @return {string[]}
   */
  collectSelectedRowIds() {
    const rows = this.#table.getElementsByClassName('c-table-data__row-selector');
    const ret = [];

    rows.forEach(
      (chk) => {
        if (chk.checked) {
          ret.push(chk.value);
        }
      },
    );
    return ret;
  }

  /**
   * @param {array} signalData
   * @return {void}
   */
  doMultiAction(signalData) {
    this.doAction(signalData, this.collectSelectedRowIds());
  }

  /**
   * @param {array} signalData
   * @return {void}
   */
  doSingleAction(signalData) {
    const rowId = signalData.options[this.#signalConstants.rowId];
    this.doAction(signalData, [rowId]);
  }

  /**
   * @param {HTMLElement} originator
   * @return {void}
   */
  doActionForAll(originator) {
    const modalContent = originator.parentNode.parentNode;
    const modalClose = modalContent.getElementsByClassName('close').item(0);
    const selectedAction = modalContent
      .getElementsByClassName('modal-body')[0]
      .getElementsByTagName('select')[0].value;

    if (selectedAction in this.#actionsRegistry) {
      const k = this.#signalConstants.actionId;
      const signalData = { options: {} };
      signalData.options[k] = selectedAction;

      modalClose.click();
      this.doAction(signalData, ['ALL_OBJECTS']);
    }
  }

  /**
   * @param {array} signalData
   * @param {string[]} rowIds
   * @return {void}
   */
  doAction(signalData, rowIds) {
    const actId = signalData.options[this.#signalConstants.actionId];
    const action = this.#actionsRegistry[actId];
    const token = action.urlTokens.values().next().value;
    action.urlBuilder.writeParameter(token, rowIds);
    const target = decodeURI(action.urlBuilder.getUrl().toString());

    if (!action.async) {
      window.location.href = target;
    } else {
      this.asyncAction(target);
    }
  }

  /**
   * @param {string} url
   * @return void
   */
  asyncAction(target) {
    const responseContainer = this.#component.getElementsByClassName('c-table-data__async').item(0);
    const responseContent = responseContainer.getElementsByClassName('c-table-data__response').item(0);
    this.#jquery.ajax({
      url: target,
      dataType: 'html',
    }).done(
      (html) => {
        if(this.#jquery(html).first().hasClass('modal')) {
          this.#jquery(html).modal({backdrop: false});
        } else {
          responseContainer.querySelector('.modal-header > button').addEventListener(
            'click',
            () => {
              responseContainer.style.display = 'none';
            },
          );
          responseContent.innerHTML = html;
          responseContainer.style.display = 'block';
        }

      },
    );
  }

  /**
   * @param {KeyboardEvent} event
   * @return {void}
   */
  navigateCellsWithArrowKeys(event) {
    if (!(event.which === KEY_LEFT
            || event.which === KEY_UP
            || event.which === KEY_RIGHT
            || event.which === KEY_DOWN
    )) {
      return;
    }

    const cell = event.target.closest('td, th');
    const row = cell.closest('tr');

    let { cellIndex } = cell;
    let { rowIndex } = row;

    switch (event.which) {
      case KEY_LEFT:
        cellIndex -= 1;
        break;
      case KEY_RIGHT:
        cellIndex += 1;
        break;
      case KEY_UP:
        rowIndex -= 1;
        break;
      case KEY_DOWN:
        rowIndex += 1;
        break;
    }

    if (rowIndex < 0 || cellIndex < 0
            || rowIndex >= this.#table.rows.length
            || cellIndex >= row.cells.length
    ) {
      return;
    }
    this.focusCell(cell, rowIndex, cellIndex);
  }

  /**
   * @param {HTMLTableCellElement} cell
   * @param {number} rowIndex
   * @param {number} cellIndex
   * @return {void}
   */
  focusCell(cell, rowIndex, cellIndex) {
    const nextCell = this.#table.rows[rowIndex].cells[cellIndex];
    nextCell.focus();
    cell.setAttribute('tabindex', -1);
    nextCell.setAttribute('tabindex', 0);
  }
}

class DataTableFactory {
  /**
    * @type {jQuery}
    */
  #jquery;

  /**
   * @type {Array<string, DataTable>}
   */
  #instances = [];

  /**
   * @param {jQuery} jquery
   */
  constructor(jquery) {
    this.#jquery = jquery;
  }

  /**
   * @param {string} tableId
   * @param {string} optActionId
   * @param {string} optRowId
   * @return {void}
   * @throws {Error} if the table was already initialized.
   */
  init(tableId, optActionId, optRowId) {
    if (this.#instances[tableId] !== undefined) {
      throw new Error(`DataTable with id '${tableId}' has already been initialized.`);
    }

    this.#instances[tableId] = new DataTable(
      this.#jquery,
      optActionId,
      optRowId,
      tableId,
    );
  }

  /**
   * @param {string} tableId
   * @return {DataTable|null}
   */
  get(tableId) {
    return this.#instances[tableId] ?? null;
  }
}

il.UI = il.UI || {};
il.UI.table = il.UI.table || {};
/* eslint  no-undef:0 */
il.UI.table.data = new DataTableFactory($);
