/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "../actions/table-action-types.js";
import PAGE_ACTIONS from "../../page/actions/page-action-types.js";

/**
 * Table UI action handler
 */
export default class TableUIActionHandler {

  /**
   * @type {TableUI}
   */
  //tableUI;

  /**
   * @type {ActionFactory}
   */
  //actionFactory;

  /**
   * @type {Dispatcher}
   */
  //dispatcher;

  /**
   * @type {Client}
   */
  //client;

  /**
   * @param {ActionFactory} actionFactory
   * @param {Client} client
   */
  constructor(actionFactory, client) {
    this.actionFactory = actionFactory;
    this.client = client;
    this.dispatcher = null;
    this.tableUI = null;
  }

  /**
   * @param {TableUI} tableUI
   */
  setUI(tableUI) {
    this.tableUI = tableUI;
  }

  /**
   * @param {Dispatcher} dispatcher
   */
  setDispatcher(dispatcher) {
    this.dispatcher = dispatcher;
  }

  /**
   * @param {EditorAction} action
   * @param {PageModel} page_model
   * @param {TableModel} table_model
   */
  handle(action, page_model, table_model) {

    const dispatcher = this.dispatcher;
    const actionFactory = this.actionFactory;
    const client = this.client;
    let form_sent = false;

    const params = action.getParams();

    if (action.getComponent() === "Paragraph") {
      this.tableUI.updateModelFromCell();
    }

    if (action.getComponent() === "Table") {

      switch (action.getType()) {

        case ACTIONS.EDIT_CELL:
          this.tableUI.editCell(params.tablePcid, params.row, params.column);
          break;

        case ACTIONS.SAVE_RETURN:
          this.sendUpdateDataCommand(
            page_model.getCurrentPCId(),
            page_model.getPCModel(page_model.getCurrentPCId()),
            true
          );
//          this.ui.handleSaveOnEdit();
          break;

        case ACTIONS.COL_BEFORE:
        case ACTIONS.COL_AFTER:
        case ACTIONS.COL_DELETE:
        case ACTIONS.COL_LEFT:
        case ACTIONS.COL_RIGHT:
        case ACTIONS.ROW_BEFORE:
        case ACTIONS.ROW_AFTER:
        case ACTIONS.ROW_DELETE:
        case ACTIONS.ROW_UP:
        case ACTIONS.ROW_DOWN:
          console.log(params);
          this.sendTableModificationCommand(
            params.tablePcid,
            page_model.getPCModel(params.tablePcid),
            page_model,
            action.getType(),
            params.nr,
            params.cellPcid
          );
          break;

        case ACTIONS.AUTO_SAVE:
          this.sendUpdateDataCommand(
            page_model.getCurrentPCId(),
            page_model.getPCModel(page_model.getCurrentPCId()),
            false
          );
          break;
      }
    }
  }

  sendUpdateDataCommand(pcid, pcmodel, redirectToPage) {
    const af = this.actionFactory;
    const update_action = af.table().command().updateData(
      pcid,
      pcmodel.content,
      redirectToPage
    );
    this.tableUI.updateModelFromCell();
    this.tableUI.paragraphUI.autoSaveStarted();
    this.client.sendCommand(update_action).then(result => {
      const pl = result.getPayload();
      if (redirectToPage) {
        this.tableUI.pageModifier.redirectToPage(pcid);
      } else {
        this.tableUI.paragraphUI.autoSaveEnded();
        if (pl.last_update) {
          this.tableUI.paragraphUI.showLastUpdate(pl.last_update);
        }
      }
    });
  }

  sendTableModificationCommand(tablePcid, pcmodel, page_model, modification, nr, cellPcid) {
    const af = this.actionFactory;
    const update_action = af.table().command().modifyTable(
      tablePcid,
      pcmodel.content,
      modification,
      nr,
      cellPcid
    );
    console.log(this.client);
    this.client.sendCommand(update_action).then(result => {
      const pl = result.getPayload();
      this.handleModificationResponse(pl, page_model);
    });
  }

  handleModificationResponse(pl, page_model) {
    if (pl.renderedContent !== undefined) {
      const tableArea = document.getElementById("copg-ed-table-area");

      this.tableUI.tinyWrapper.stopEditing();
      tableArea.outerHTML = pl.renderedContent;

      console.log(pl.renderedContent);
      console.log("PCMODEL---");
      console.log(pl.pcModel);

      for (const [key, value] of Object.entries(pl.pcModel)) {
        page_model.setPCModel(key, value);
      }

      //      il.IntLink.refresh();           // missing
      this.tableUI.reInit();
    }
  }

}