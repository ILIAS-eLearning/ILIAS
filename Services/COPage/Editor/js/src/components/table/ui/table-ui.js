import ACTIONS from "../actions/table-action-types.js";
import PAGE_ACTIONS from "../../page/actions/page-action-types.js";
import TinyWrapper from "../../paragraph/ui/tiny-wrapper.js";
import ParagraphUI from '../../paragraph/ui/paragraph-ui.js';
import TINY_CB from "../../paragraph/ui/tiny-wrapper-cb-types.js";

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * table ui
 */
export default class TableUI {


  /**
   * @type {boolean}
   */
  //debug = true;

  /**
   * Model
   * @type {Model}
   */
  //page_model = {};

  /**
   * UI model
   * @type {Object}
   */
  //uiModel = {};

  /**
   * @type {Client}
   */
  //client;

  /**
   * @type {Dispatcher}
   */
  //dispatcher;

  /**
   * @type {ActionFactory}
   */
  //actionFactory;

  /**
   * @type {ToolSlate}
   */
  //toolSlate;

  /**
   * @type {TinyWrapper}
   */
  //tinyWrapper;

  /**
   * @type {pageModifier}
   */
  //pageModifier;

  /**
   * @type {ParagraphUI}
   */
  //paragraphUI;

  /**
   * @type {TableModel}
   */
  //tableModel;


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
    this.log("table-ui.init");

    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    const pageModel = this.page_model;

    this.uiModel = uiModel;
    let t = this;


    // init wrapper in paragraphui
    //this.paragraphUI.initTinyWrapper();

    // init menu in paragraphui
    //this.initMenu();
    this.initCellEditing();
    this.initDropdowns();
    this.autoSave.addOnAutoSave(() => {
      if (pageModel.getCurrentPCName() === "Table") {
        dispatch.dispatch(action.table().editor().autoSave());
      }
    });

    this.initWrapperCallbacks();
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
  initDropdowns() {
    const action = this.actionFactory;

    const selector = "[data-copg-ed-type='data-column-head'],[data-copg-ed-type='data-row-head']"

    // init add buttons
    document.querySelectorAll(selector).forEach(head => {

      const headType = head.dataset.copgEdType;
      const nr = head.dataset.nr;
      const caption = head.dataset.caption;
      const cellPcid = head.dataset.pcid;

      const table = head.closest("table");
      const tablePcid = table.dataset.pcid;

      const uiModel = this.uiModel;
      let li, li_templ, ul;

      head.innerHTML = uiModel.dropdown;


      const model = this.model;

      const af = action.table().editor();

      // add dropdown
      head.querySelectorAll("div.dropdown > button").forEach(b => {
        //b.classList.add("copg-add");
        b.innerHTML = caption + b.innerHTML;
        b.addEventListener("click", (event) => {

          ul = b.parentNode.querySelector("ul");
          li_templ = ul.querySelector("li").cloneNode(true);
          ul.innerHTML = "";

            if (headType === "data-column-head") {
              this.addDropdownAction(li_templ, ul, "cont_ed_new_col_before", af.colBefore(nr, cellPcid, tablePcid));
              this.addDropdownAction(li_templ, ul, "cont_ed_new_col_after", af.colAfter(nr, cellPcid, tablePcid));
              this.addDropdownAction(li_templ, ul, "cont_ed_col_left", af.colLeft(nr, cellPcid, tablePcid));
              this.addDropdownAction(li_templ, ul, "cont_ed_col_right", af.colRight(nr, cellPcid, tablePcid));
              this.addDropdownAction(li_templ, ul, "cont_ed_delete_col", af.colDelete(nr, cellPcid, tablePcid));
            } else {
              this.addDropdownAction(li_templ, ul, "cont_ed_new_row_before", af.rowBefore(nr, cellPcid, tablePcid));
              this.addDropdownAction(li_templ, ul, "cont_ed_new_row_after", af.rowAfter(nr, cellPcid, tablePcid));
              this.addDropdownAction(li_templ, ul, "cont_ed_row_up", af.rowUp(nr, cellPcid, tablePcid));
              this.addDropdownAction(li_templ, ul, "cont_ed_row_down", af.rowDown(nr, cellPcid, tablePcid));
              this.addDropdownAction(li_templ, ul, "cont_ed_delete_row", af.rowDelete(nr, cellPcid, tablePcid));
            }
        });
      });
    });
  }

  addDropdownAction(li_templ, ul, txtKey, action) {
    const dispatch = this.dispatcher;
    const li = li_templ.cloneNode(true);

    li.querySelector("a").innerHTML = il.Language.txt(txtKey);
    li.querySelector("a").addEventListener("click", (event) => {
      dispatch.dispatch(action);
    });
    ul.appendChild(li);
  }

  initCellEditing() {
    const dispatch = this.dispatcher;
    const action = this.actionFactory;

    document.querySelectorAll("[data-copg-ed-type='data-cell']").forEach((el) => {
      const column = el.dataset.column;
      const row = el.dataset.row;
      const table = el.closest("table");
      const table_pcid = table.dataset.pcid;
      const table_hierid = table.dataset.hierid;
      console.log(el.dataset);
      el.addEventListener("click", (event) => {
        dispatch.dispatch(action.table().editor().editCell(
          table_pcid,
          table_hierid,
          row,
          column
        ));
      });
    });
  }

  editCell(pcid, row, col) {
    this.tinyWrapper.setDataTableMode(true);
    this.paragraphUI.setDataTableMode(true);
    const tableModel = this.tableModel;
    const wrapper = this.tinyWrapper;
    let content_el = document.querySelector("[data-copg-ed-type='data-cell'][data-row='" + tableModel.getCurrentRow() + "'][data-column='" + tableModel.getCurrentColumn() + "']");

    wrapper.stopEditing();
    wrapper.initEdit(content_el, "", "");
  }

  initWrapperCallbacks() {
    const wrapper = this.tinyWrapper;
    const tableUI = this;
    const tableModel = this.tableModel;
    const pageModel = this.page_model;
    wrapper.addCallback(TINY_CB.SWITCH_LEFT, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        tableUI.switchEditingCell(-1,0);
      }
    });
    wrapper.addCallback(TINY_CB.SWITCH_UP, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        tableUI.switchEditingCell(0,-1);
      }
    });
    wrapper.addCallback(TINY_CB.SWITCH_RIGHT, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        tableUI.switchEditingCell(1,0);
      }
    });
    wrapper.addCallback(TINY_CB.SWITCH_DOWN, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        tableUI.switchEditingCell(0,1);
      }
    });
    wrapper.addCallback(TINY_CB.TAB, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        tableUI.switchEditingCell(1,0);
      }
    });
    wrapper.addCallback(TINY_CB.SHIFT_TAB, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        tableUI.switchEditingCell(-1,0);
      }
    });
    wrapper.addCallback(TINY_CB.KEY_UP, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        let pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
        pcModel.content[tableModel.getCurrentRow()][tableModel.getCurrentColumn()] = wrapper.getText();
        tableUI.paragraphUI.autoSave.handleAutoSaveKeyPressed();
      }
    });
    wrapper.addCallback(TINY_CB.AFTER_INIT, () => {
      if (pageModel.getCurrentPCName() === "Table") {
        let pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
        let content = pcModel.content[tableModel.getCurrentRow()][tableModel.getCurrentColumn()];
        tableUI.paragraphUI.showToolbar(false, false);
        wrapper.initContent(content, "");
      }
    });
  }

  cellExists (col, row) {
    const pageModel = this.page_model;
    const pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
    return (row in pcModel.content && col in pcModel.content[row]);
  }

  updateModelFromCell() {
    const pageModel = this.page_model;
    const pcModel = pageModel.getPCModel(pageModel.getCurrentPCId());
    const tableModel = this.tableModel;
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
    const tableModel = this.tableModel;
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
          newCol
      ));
    }
  }

}
