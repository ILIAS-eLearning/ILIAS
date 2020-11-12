import ACTIONS from "../actions/table-action-types.js";
import PAGE_ACTIONS from "../../page/actions/page-action-types.js";
import TinyWrapper from "../../paragraph/ui/tiny-wrapper.js";
import ParagraphUI from '../../paragraph/ui/paragraph-ui.js';

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

    this.uiModel = uiModel;
    let t = this;


    // init wrapper in paragraphui
    //this.paragraphUI.initTinyWrapper();

    // init menu in paragraphui
    //this.initMenu();
    this.initCellEditing();
    this.initDropdowns();
    this.autoSave.setOnAutoSave(() => {
      dispatch.dispatch(action.table().editor().autoSave());
    });
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
    this.log("table-ui.editCell" + row + " " + col);
    let content_el = document.querySelector("[data-copg-ed-type='data-cell'][data-row='" + row + "'][data-column='" + col + "']");
    console.log(content_el);
    let pc_model = this.page_model.getPCModel(pcid);
    const wrapper = this.tinyWrapper;
    const nrow = parseInt(row);
    const ncol = parseInt(col);
    const tableModel = this.tableModel;
    console.log(nrow);
    console.log(ncol);
    const content = pc_model.content[nrow][ncol];
    wrapper.stopEditing();
    wrapper.initEdit(content_el, "", "", () => {
      this.paragraphUI.showToolbar();
      //wrapper.copyInputToGhost();
      //wrapper.synchInputRegion();
      wrapper.initContent(content, "");
      //this.setParagraphClass("");
      //this.setSectionClassSelector(this.getSectionClass(pcId));
    }, () => {
      pc_model.content[tableModel.getCurrentRow()][tableModel.getCurrentColumn()] = wrapper.getText();
      console.log(pc_model);
      this.paragraphUI.autoSave.handleAutoSaveKeyPressed();
    }, () => {
      //this.switchToPrevious();
    }, () => {
      //this.switchToNext();
    });
  }


}
