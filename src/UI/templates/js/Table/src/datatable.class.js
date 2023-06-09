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

export default class DataTable {
  /**
   * @type {jQuery}
   */
  #jquery;

  /**
   * @type {Params}
   */
  #params;

  /**
   * @type {{type: {url: string, signal: string}, opt: {mainkey: string, id: string}}
   */
  #actionsConstants;

  /**
   * @type {HTMLDivElement}
   */
  #component;

  /**
   * @type {HTMLTableElement}
   */
  #table;

  /**
   * @type {array<string, array>}
   */
  #actionsRegistry;

  /**
   * @param {jQuery} jquery
   * @param {Params} params
   * @param {string} typeURL
   * @param {string} typeSignal
   * @param {string} optOptions
   * @param {string} optId
   * @param {string} tableId
   * @throws {Error} if DOM element is missing
   */
  constructor(jquery, params, typeURL, typeSignal, optOptions, optId, componentId) {
    this.#component = document.getElementById(componentId);
    if (this.#component === null) {
      throw new Error(`Could not find a DataTable for id '${componentId}'.`);
    }
    this.#table = this.#component.getElementsByTagName('table').item(0);
    if (this.#table === null) {
      throw new Error(`There is no <table> in the component's HTML.`);
    }
    this.#jquery = jquery;
    this.#params = params;
    this.#actionsConstants = {
      type: {
        url: typeURL,
        signal: typeSignal,
      },
      opt: {
        mainkey: optOptions,
        id: optId,
      },
    };
    this.#actionsRegistry = {};

    this.#component.addEventListener('keydown', (event) => this.navigateCellsWithArrowKeys(event));
  }

  /**
   * @param {string} actionId
   * @param {string} type 'SIGNAL' | 'URL'
   * @param {mixed} target
   * @param {string} parameterName
   * @return {void}
   */
  registerAction(actionId, type, target, parameterName) {
    if (type !== this.#actionsConstants.type.url
        && type !== this.#actionsConstants.type.signal) {
      throw new Error('Action must be of type {this.#actionsConstants.type.url} or {this.#actionsConstants.type.signal}.');
    }
    this.#actionsRegistry[actionId] = {
      type,
      target,
      param: parameterName,
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

    cols.forEach(
      (col) => { col.checked = state; },
    );

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
    const cols = this.#table.getElementsByClassName('c-table-data__row-selector');
    const ret = [];

    cols.forEach(
      (col) => {
        if (col.checked) {
          ret.push(col.value);
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
      const signalData = { options: { action: selectedAction } };
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
    const actId = signalData.options.action;
    const action = this.#actionsRegistry[actId];
    let target;

    if (action.type === this.#actionsConstants.type.url) {
      target = this.#params.amendParameterToUrl(action.target, action.param, rowIds);
      window.location.href = target;
    }
    if (action.type === this.#actionsConstants.type.signal) {
      target = this.#params.amendParameterToSignal(action.target, action.param, rowIds);
      const opts = {};
      opts[this.#actionsConstants.opt.id] = target.id;
      opts[this.#actionsConstants.opt.mainkey] = target.options;
      this.#jquery(`#${this.#component.id}`).trigger(target.id, opts);
    }
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
      default:
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
