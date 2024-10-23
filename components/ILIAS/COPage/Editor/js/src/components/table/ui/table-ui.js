import ACTIONS from '../actions/table-action-types.js';
import PAGE_ACTIONS from '../../page/actions/page-action-types.js';
import TinyWrapper from '../../paragraph/ui/tiny-wrapper.js';
import ParagraphUI from '../../paragraph/ui/paragraph-ui.js';
import TINY_CB from '../../paragraph/ui/tiny-wrapper-cb-types.js';

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ******************************************************************** */

/**
 * table ui
 */
export default class TableUI {
  /**
   * @type {boolean}
   */
  // debug = true;

  /**
   * Model
   * @type {Model}
   */
  // page_model = {};

  /**
   * UI model
   * @type {Object}
   */
  // uiModel = {};

  /**
   * @type {Client}
   */
  // client;

  /**
   * @type {Dispatcher}
   */
  // dispatcher;

  /**
   * @type {ActionFactory}
   */
  // actionFactory;

  /**
   * @type {ToolSlate}
   */
  // toolSlate;

  /**
   * @type {TinyWrapper}
   */
  // tinyWrapper;

  /**
   * @type {pageModifier}
   */
  // pageModifier;

  /**
   * @type {ParagraphUI}
   */
  // paragraphUI;

  /**
   * @type {TableModel}
   */
  // tableModel;

  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   * @param {ActionFactory} actionFactory
   * @param {PageModel} page_model
   * @param {ToolSlate} toolSlate
   * @param {pageModifier} pageModifier
   * @param {ParagraphUI} paragraphUI
   * @param {TableModel} tableModel
   */
  constructor(client, dispatcher, actionFactory, page_model, toolSlate, pageModifier, paragraphUI, tableModel) {
    this.debug = true;
    this.page_model = {};
    this.uiModel = {};

    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.page_model = page_model;
    this.toolSlate = toolSlate;
    this.pageModifier = pageModifier;
    this.paragraphUI = paragraphUI;
    this.tinyWrapper = paragraphUI.tinyWrapper;
    this.autoSave = paragraphUI.autoSave;
    this.tableModel = tableModel;
    this.in_data_table = false;
    this.in_table = false;
    this.head_selection_initialised = false;
  }

  //
  // Initialisation
  //

  /**
   * @param message
   */
  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }

  /**
   */
  init(uiModel) {
    this.log('table-ui.init');

    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    const pageModel = this.page_model;

    this.uiModel = uiModel;
    const t = this;

    if (uiModel.initialComponent === 'DataTable') {
      this.in_data_table = true;
      pageModel.setCurrentPageComponent('DataTable', uiModel.initialPCId, '');
    }

    if (uiModel.initialComponent === 'Table') {
      this.in_table = true;
      pageModel.setCurrentPageComponent('Table', uiModel.initialPCId, '');
    }

    if (!this.in_data_table && !this.in_table) {
      return;
    }

    // init wrapper in paragraphui
    // this.paragraphUI.initTinyWrapper();

    // init menu in paragraphui
    // this.initMenu();
    this.initCellEditing();
    this.initDropdowns();
    this.autoSave.addOnAutoSave(() => {
      if (pageModel.getCurrentPCName() === 'Table') {
        dispatch.dispatch(action.table().editor().autoSave());
      }
    });

    this.initWrapperCallbacks();
    this.refreshUIFromModelState(pageModel, this.tableModel);
  }

  /**
   */
  reInit() {
    this.initCellEditing();
    this.initDropdowns();
  }

  /**
   * Init add buttons
   */
  initHeadSelection() {
    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    const { tableModel } = this;
    const selector = "[data-copg-ed-type='data-table-head'],[data-copg-ed-type='data-column-head'],[data-copg-ed-type='data-row-head']";

    // init add buttons
    document.querySelectorAll(selector).forEach((head) => {
      const { caption } = head.dataset;
      const nr = parseInt(head.dataset.nr) - 1;
      const headType = head.dataset.copgEdType;
      if (headType !== 'data-table-head') {
        head.innerHTML = caption;
      }
      if (!this.head_selection_initialised) {
        head.addEventListener('click', (event) => {
          if (tableModel.getState() !== tableModel.STATE_CELLS
            && tableModel.getState() !== tableModel.STATE_MERGE) {
            return;
          }
          event.stopPropagation();
          event.preventDefault();
          document.getSelection().removeAllRanges();
          const expand = (event.shiftKey || event.ctrlKey || event.metaKey);
          if (headType === 'data-row-head') {
            dispatch.dispatch(action.table().editor().toggleRow(nr, expand));
          } else if (headType === 'data-column-head') {
            dispatch.dispatch(action.table().editor().toggleCol(nr, expand));
          } else {
            dispatch.dispatch(action.table().editor().toggleTable(expand));
          }
        });
      }
    });
    this.head_selection_initialised = true;
  }

  /**
   * Init add buttons
   */
  initDropdowns() {
    const action = this.actionFactory;

    const selector = "[data-copg-ed-type='data-column-head'],[data-copg-ed-type='data-row-head']";

    // init add buttons
    document.querySelectorAll(selector).forEach((head) => {
      const headType = head.dataset.copgEdType;
      const { nr } = head.dataset;
      const { caption } = head.dataset;
      const cellPcid = head.dataset.pcid;

      const table = head.closest('table');
      const tablePcid = table.dataset.pcid;

      const { uiModel } = this;
      let li; let li_templ; let
        ul;

      head.innerHTML = uiModel.dropdown;

      const { model } = this;

      const af = action.table().editor();

      // add dropdown
      head.querySelectorAll('div.dropdown > button').forEach((b) => {
        // b.classList.add("copg-add");
        b.innerHTML = caption + b.innerHTML;
        b.addEventListener('click', (event) => {
          ul = b.parentNode.querySelector('ul');
          li_templ = ul.querySelector('li').cloneNode(true);
          ul.innerHTML = '';

          if (headType === 'data-column-head') {
            const th = b.closest('th');
            const first = !(th.previousElementSibling.previousElementSibling);
            const last = !(th.nextElementSibling);
            this.addDropdownNumberAction(
              li_templ,
              ul,
              'cont_ed_new_col_before',
              'cont_ed_nr_cols',
              nr,
              cellPcid,
              tablePcid,
              'colBefore',
            );
            this.addDropdownNumberAction(
              li_templ,
              ul,
              'cont_ed_new_col_after',
              'cont_ed_nr_cols',
              nr,
              cellPcid,
              tablePcid,
              'colAfter',
            );
            if (!first) {
              this.addDropdownAction(li_templ, ul, 'cont_ed_col_left', af.colLeft(nr, cellPcid, tablePcid));
            }
            if (!last) {
              this.addDropdownAction(li_templ, ul, 'cont_ed_col_right', af.colRight(nr, cellPcid, tablePcid));
            }
            if (!first || !last) {
              this.addDropdownAction(li_templ, ul, 'cont_ed_delete_col', af.colDelete(nr, cellPcid, tablePcid));
            }
          } else {
            const tr = b.closest('tr');
            const first = !(tr.previousElementSibling.previousElementSibling);
            const last = !(tr.nextElementSibling);
            this.addDropdownNumberAction(
              li_templ,
              ul,
              'cont_ed_new_row_before',
              'cont_ed_nr_rows',
              nr,
              cellPcid,
              tablePcid,
              'rowBefore',
            );
            this.addDropdownNumberAction(
              li_templ,
              ul,
              'cont_ed_new_row_after',
              'cont_ed_nr_rows',
              nr,
              cellPcid,
              tablePcid,
              'rowAfter',
            );
            if (!first) {
              this.addDropdownAction(li_templ, ul, 'cont_ed_row_up', af.rowUp(nr, cellPcid, tablePcid));
            }
            if (!last) {
              this.addDropdownAction(li_templ, ul, 'cont_ed_row_down', af.rowDown(nr, cellPcid, tablePcid));
            }
            if (!first || !last) {
              this.addDropdownAction(li_templ, ul, 'cont_ed_delete_row', af.rowDelete(nr, cellPcid, tablePcid));
            }
          }
        });
      });
    });
  }

  addDropdownAction(li_templ, ul, txtKey, action) {
    const dispatch = this.dispatcher;
    const li = li_templ.cloneNode(true);

    li.querySelector('a').innerHTML = il.Language.txt(txtKey);
    li.querySelector('a').addEventListener('click', (event) => {
      dispatch.dispatch(action);
    });
    ul.appendChild(li);
  }

  addDropdownNumberAction(li_templ, ul, txtKey, txtKeyProp, nr, cellPcid, tablePcid, func) {
    const dispatch = this.dispatcher;
    const li = li_templ.cloneNode(true);

    li.querySelector('a').innerHTML = il.Language.txt(txtKey);
    li.querySelector('a').addEventListener('click', (event) => {
      this.showNumberModal(txtKey, txtKeyProp, nr, cellPcid, tablePcid, func);
    });
    ul.appendChild(li);
  }

  showNumberModal(txtKey, txtKeyProp, nr, cellPcid, tablePcid, func) {
    const dispatch = this.dispatcher;
    const { uiModel } = this;
    const { signal } = uiModel.components.DataTable.number_input_modal;

    $('#il-copg-ed-table-modal').remove();
    let modal_template = uiModel.components.DataTable.number_input_modal.modal;
    modal_template = modal_template.replace('#modal-title#', il.Language.txt(txtKey));
    modal_template = modal_template.replace('#select-title#', il.Language.txt(txtKeyProp));
    modal_template = modal_template.replace('#on-form-submit-click#', '');

    $('body').append(`<div id='il-copg-ed-table-modal'>${modal_template}</div>`);
    const modalEl = document.getElementById('il-copg-ed-table-modal');
    const modalFormSubmit = modalEl.querySelector('.modal-footer button');

    // hide standard form buttons
    modalEl.querySelectorAll('form button').forEach((b) => { b.style.display = 'none'; });
    const closeEl = modalEl.querySelector('.modal-header button');
    const af = this.actionFactory.table().editor();

    // on submit click
    modalFormSubmit.addEventListener('click', (event) => {
      const selectEl = modalEl.querySelector('form select');
      const cnt = parseInt(selectEl.value);
      dispatch.dispatch(af[func](nr, cellPcid, tablePcid, cnt));
      closeEl.click();
    });

    $(document).trigger(
      signal,
      {
        id: signal,
        triggerer: $(this),
        options: JSON.parse('[]'),
      },
    );

    /*
    if (button_txt) {
      const b = document.querySelector("#il-copg-ed-modal .modal-footer button");
      b.addEventListener("click", onclick);
    } else {
      document.querySelectorAll("#il-copg-ed-modal .modal-footer").forEach((b) => {
        b.remove();
      });
    } */
  }

  initCellEditing() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    console.log('INIT CELL EDITING');
    document.querySelectorAll("[data-copg-ed-type='data-cell']").forEach((el) => {
      const { column } = el.dataset;
      const { row } = el.dataset;
      const table = el.closest('table');
      const table_pcid = table.dataset.pcid;
      const table_hierid = table.dataset.hierid;
      const { tableModel } = this;
      console.log(el.dataset);
      el.addEventListener('click', (event) => {
        if (tableModel.getState() !== tableModel.STATE_CELLS
          && tableModel.getState() !== tableModel.STATE_MERGE) {
          if (this.in_data_table) {
            dispatch.dispatch(action.table().editor().editCell(
              table_pcid,
              table_hierid,
              row,
              column,
            ));
          }
        } else {
          event.stopPropagation();
          event.preventDefault();
          document.getSelection().removeAllRanges();
          const expand = (event.shiftKey || event.ctrlKey || event.metaKey);
          dispatch.dispatch(action.table().editor().toggleCell(
            column,
            row,
            expand,
          ));
        }
      });
    });
  }

  editCell(pcid, row, col) {
    this.tinyWrapper.setDataTableMode(true);
    this.paragraphUI.setDataTableMode(true);
    const { tableModel } = this;
    const wrapper = this.tinyWrapper;
    const content_el = document.querySelector(`[data-copg-ed-type='data-cell'][data-row='${tableModel.getCurrentRow()}'][data-column='${tableModel.getCurrentColumn()}']`);

    wrapper.stopEditing();
    wrapper.initEdit(content_el, '', '');
  }

  initWrapperCallbacks() {
    const wrapper = this.tinyWrapper;
    const tableUI = this;
    const { tableModel } = this;
    const pageModel = this.page_model;
    wrapper.addCallback(TINY_CB.SWITCH_LEFT, () => {
      if (pageModel.getCurrentPCName() === 'Table') {
        tableUI.switchEditingCell(-1, 0);
      }
    });
    wrapper.addCallback(TINY_CB.SWITCH_UP, () => {
      if (pageModel.getCurrentPCName() === 'Table') {
        tableUI.switchEditingCell(0, -1);
      }
    });
    wrapper.addCallback(TINY_CB.SWITCH_RIGHT, () => {
      if (pageModel.getCurrentPCName() === 'Table') {
        tableUI.switchEditingCell(1, 0);
      }
    });
    wrapper.addCallback(TINY_CB.SWITCH_DOWN, () => {
      if (pageModel.getCurrentPCName() === 'Table') {
        tableUI.switchEditingCell(0, 1);
      }
    });
    wrapper.addCallback(TINY_CB.TAB, () => {
      if (pageModel.getCurrentPCName() === 'Table') {
        tableUI.switchEditingCell(1, 0);
      }
    });
    wrapper.addCallback(TINY_CB.SHIFT_TAB, () => {
      if (pageModel.getCurrentPCName() === 'Table') {
        tableUI.switchEditingCell(-1, 0);
      }
    });
    wrapper.addCallback(TINY_CB.KEY_UP, () => {
      if (pageModel.getCurrentPCName() === 'Table') {
        const pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
        pcModel.content[tableModel.getCurrentRow()][tableModel.getCurrentColumn()] = wrapper.getText();
        tableUI.paragraphUI.autoSave.handleAutoSaveKeyPressed();
      }
    });
    wrapper.addCallback(TINY_CB.AFTER_INIT, () => {
      if (pageModel.getCurrentPCName() === 'Table') {
        const pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
        const content = pcModel.content[tableModel.getCurrentRow()][tableModel.getCurrentColumn()];
        tableUI.paragraphUI.showToolbar(false, false);
        wrapper.initContent(content, '');
      }
    });
  }

  cellExists(col, row) {
    const pageModel = this.page_model;
    const pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
    return (row in pcModel.content && col in pcModel.content[row]);
  }

  updateModelFromCell() {
    const pageModel = this.page_model;
    const pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
    const { tableModel } = this;
    const wrapper = this.tinyWrapper;
    if (tableModel.getCurrentRow() == null) {
      return;
    }
    pcModel.content[tableModel.getCurrentRow()][tableModel.getCurrentColumn()] = wrapper.getText();
  }

  switchEditingCell(colDiff, rowDiff) {
    const pageModel = this.page_model;
    const pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    const { tableModel } = this;
    let newCol = tableModel.getCurrentColumn() + colDiff;
    let newRow = tableModel.getCurrentRow() + rowDiff;

    this.updateModelFromCell();

    // move to beginning of next row, if end of row is reached
    if (rowDiff === 0 && colDiff === 1) {
      if (!this.cellExists(newCol, newRow)) {
        newCol = 0;
        newRow = tableModel.getCurrentRow() + 1;
      }
    }

    // move to end of previous row, if beginning of row is reached
    if (rowDiff === 0 && colDiff === -1) {
      if (!this.cellExists(newCol, newRow)) {
        newCol = 0;
        newRow = tableModel.getCurrentRow() - 1;
        if (newRow >= 0) {
          newCol = pcModel.content[newRow].length - 1;
        }
      }
    }

    if (this.cellExists(newCol, newRow)) {
      dispatch.dispatch(action.table().editor().editCell(
        pageModel.getCurrentPCId(),
        pageModel.getCurrenntHierId(),
        newRow,
        newCol,
      ));
    }
  }

  refreshUIFromModelState(pageModel, table_model) {
    console.log('REFRESH');
    console.log(table_model.getState());
    switch (table_model.getState()) {
      case table_model.STATE_TABLE:
        this.showTableProperties();
        break;
      case table_model.STATE_CELLS:
        this.showCellProperties();
        break;
      case table_model.STATE_MERGE:
        this.showMergeActions();
        break;
    }
  }

  showTableProperties() {
    const { dispatcher } = this;
    const { actionFactory } = this;

    dispatcher.dispatch(actionFactory.page().editor().componentForm(
      'DataTable',
      this.uiModel.initialPCId,
      '',
    ));
  }

  initAfterFormLoaded() {
    this.initTopActions();
    this.refreshModeSelector();
  }

  showCellProperties() {
    let add = '';
    if (this.tableModel.hasSelected()) {
      add = this.uiModel.components.DataTable.cell_actions;
    } else {
      add = this.uiModel.components.DataTable.cell_info;
    }
    this.toolSlate.setContent(this.uiModel.components.DataTable.top_actions + add);
    if (this.tableModel.hasSelected()) {
      // init cancel button in cell prop form
      this.pageModifier.initFormButtonsAndSettingsLink(this.page_model);
    }
    this.initTopActions();
    this.refreshModeSelector();
    this.initCellPropertiesForm(this.page_model, this.tableModel);
  }

  showMergeActions() {
    let add = '';
    add = this.uiModel.components.DataTable.cell_info
      + this.uiModel.components.DataTable.merge_actions;
    this.toolSlate.setContent(this.uiModel.components.DataTable.top_actions + add);
    this.updateMergeButton(
      this.page_model,
      this.tableModel,
    );
    this.initTopActions();
    this.refreshModeSelector();
    document.querySelectorAll("#copg-editor-slate-content [data-copg-ed-type='button']").forEach((button) => {
      const dispatch = this.dispatcher;
      const action = this.actionFactory;
      const act = button.dataset.copgEdAction;
      const cname = button.dataset.copgEdComponent;
      const pageModel = this.page_model;
      const { tableModel } = this;
      if (cname === 'Table') {
        button.addEventListener('click', (event) => {
          event.preventDefault();
          switch (act) {
            case 'toggle.merge':
              dispatch.dispatch(action.table().editor().toggleMerge(
                pageModel.getCurrentPCId(),
                tableModel.getSelected(),
              ));
              break;
          }
        });
      }
    });
  }

  initCellPropertiesForm(pageModel, tableModel) {
    document.querySelectorAll('#copg-editor-slate-content form .dropdown-menu').forEach((dd) => {
      dd.style.right = 'auto';
    });
    document.querySelectorAll("#copg-editor-slate-content [data-copg-ed-type='form-button']").forEach((form_button) => {
      const dispatch = this.dispatcher;
      const action = this.actionFactory;
      const act = form_button.dataset.copgEdAction;
      const cname = form_button.dataset.copgEdComponent;
      if (cname === 'Table') {
        console.log('ATTACHING EVENT TO FORM BUTTON');
        console.log(form_button);
        form_button.addEventListener('click', (event) => {
          event.preventDefault();
          switch (act) {
            case 'properties.set':
              const uform = form_button.closest('form');
              const dd = uform.querySelector('.dropdown-menu');
              const uform_data = new FormData(uform);
              dispatch.dispatch(action.table().editor().propertiesSet(
                pageModel.getCurrentPCId(),
                tableModel.getSelected(),
                uform_data,
              ));
              break;

            case 'toggle.merge':
              dispatch.dispatch(action.table().editor().toggleMerge(
                pageModel.getCurrentPCId(),
                tableModel.getSelected(),
              ));
              break;
          }
        });
      }
    });
  }

  updateMergeButton(pageModel, tableModel) {
    console.log('UPDATE MERGE BUTTON');
    const b = document.querySelector("#copg-editor-slate-content [data-copg-ed-action='toggle.merge']");
    const sel = tableModel.getSelected();
    if (!b) {
      return;
    }
    if (sel.top > -1 && sel.top === sel.bottom
      && sel.left > -1 && sel.left === sel.right && this.isMerged(sel.top, sel.left)) {
      b.innerHTML = il.Language.txt('cont_split_cell');
      b.disabled = false;
      console.log('--1--');
    } else if (sel.top < sel.bottom || sel.left < sel.right) {
      console.log('--2--');
      b.innerHTML = il.Language.txt('cont_merge_cells');
      b.disabled = false;
    } else {
      console.log('--3--');
      b.disabled = true;
    }
  }

  isMerged(row, col) {
    const td = document.querySelector(`td[data-row='${row}'][data-column='${col}']`);
    console.log('ISMERGED');
    console.log(td);
    console.log(td.colSpan);
    console.log(td.rowSpan);
    if (td && (td.colSpan > 1 || td.rowSpan > 1)) {
      return true;
    }
    return false;
  }

  initTopActions() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='view-control']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case ACTIONS.SWITCH_EDIT_TABLE:
            dispatch.dispatch(action.table().editor().switchEditTable());
            break;
          case ACTIONS.SWITCH_FORMAT_CELLS:
            dispatch.dispatch(action.table().editor().switchFormatCells());
            break;
          case ACTIONS.SWITCH_MERGE_CELLS:
            dispatch.dispatch(action.table().editor().switchMergeCells());
            break;
        }
      });
    });
    document.querySelectorAll("#copg-table-top-actions [data-copg-ed-type='button']").forEach((button) => {
      const act = button.dataset.copgEdAction;
      button.addEventListener('click', (event) => {
        switch (act) {
          case PAGE_ACTIONS.COMPONENT_BACK:
            dispatch.dispatch(action.page().editor().componentBack());
            break;
        }
      });
    });
  }

  refreshModeSelector() {
    const model = this.tableModel;
    const table = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='switch.edit.table']");
    const cells = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='switch.format.cells']");
    const merge = document.querySelector("[data-copg-ed-type='view-control'][data-copg-ed-action='switch.merge.cells']");
    table.classList.remove('engaged');
    cells.classList.remove('engaged');
    merge.classList.remove('engaged');
    if (model.getState() === model.STATE_TABLE) {
      table.classList.add('engaged');
    } else if (model.getState() === model.STATE_CELLS) {
      cells.classList.add('engaged');
    } else if (model.getState() === model.STATE_MERGE) {
      merge.classList.add('engaged');
    }
  }

  markSelectedCells() {
    const selected = this.tableModel.getSelected();
    console.log('MARK SELECTED');
    console.log(selected);
    document.querySelectorAll("[data-copg-ed-type='data-cell']").forEach((el) => {
      const col = el.dataset.column;
      const { row } = el.dataset;
      el.classList.remove('il-copg-cell-selected');
      if (selected.top <= row
        && selected.bottom >= row
        && selected.left <= col
        && selected.right >= col) {
        el.classList.add('il-copg-cell-selected');
      }
    });
  }
}
