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
 *********************************************************************/

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
    this.old_selected = false;
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

    if (!this.tableUI.in_data_table) {
      return;
    }
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
            false
          );
          this.tableUI.refreshUIFromModelState(page_model, table_model);
          this.tableUI.tinyWrapper.stopEditing();
//          this.ui.handleSaveOnEdit();
          break;

        case ACTIONS.CANCEL_CELL_EDIT:
          this.tableUI.refreshUIFromModelState(page_model, table_model);
          this.tableUI.tinyWrapper.stopEditing();
          this.sendTableModificationCommand(
            page_model.getCurrentPCId(),
            page_model.getPCModel(page_model.getCurrentPCId()),
            page_model,
            "none",
            0,
            0,
            0
          );
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
          this.sendTableModificationCommand(
            params.tablePcid,
            page_model.getPCModel(params.tablePcid),
            page_model,
            action.getType(),
            params.nr,
            params.cellPcid,
            params.cnt
          );
          break;

        case ACTIONS.AUTO_SAVE:
          this.sendUpdateDataCommand(
            page_model.getCurrentPCId(),
            page_model.getPCModel(page_model.getCurrentPCId()),
            false
          );
          break;

        case ACTIONS.SWITCH_EDIT_TABLE:
          this.tableUI.refreshUIFromModelState(page_model, table_model);
          this.tableUI.initDropdowns();
          this.tableUI.markSelectedCells();
          break;

        case ACTIONS.SWITCH_FORMAT_CELLS:
          this.tableUI.refreshUIFromModelState(page_model, table_model);
          this.tableUI.initHeadSelection();
          break;

        case ACTIONS.SWITCH_MERGE_CELLS:
          this.tableUI.refreshUIFromModelState(page_model, table_model);
          this.tableUI.initHeadSelection();
          break;

        case ACTIONS.PROPERTIES_SET:
          this.sendPropertiesSet(
            params.pcid,
            params.selected,
            params.data,
            page_model,
            table_model
          );
          break;

        case ACTIONS.TOGGLE_MERGE:
          this.sendToggleMerge(
            params.pcid,
            params.selected,
            page_model,
            table_model
          );
          break;

        case ACTIONS.TOGGLE_CELL:
        case ACTIONS.TOGGLE_ROW:
        case ACTIONS.TOGGLE_TABLE:
        case ACTIONS.TOGGLE_COL:
          this.tableUI.updateMergeButton(page_model,
            table_model);
          break;
      }

    }
    if (action.getComponent() === "DataTable") {
      switch (action.getType()) {
        case PAGE_ACTIONS.COMPONENT_FORM_LOADED:
          this.tableUI.initAfterFormLoaded();
          break;
      }
    }

    if (table_model.getState() === table_model.STATE_CELLS ||
      table_model.getState() === table_model.STATE_MERGE) {
      this.tableUI.markSelectedCells();
      // switch info text and action buttons, if selected status changes
      if (table_model.hasSelected() !== this.old_selected) {
        this.tableUI.refreshUIFromModelState(page_model, table_model);
      }
      this.old_selected = table_model.hasSelected();
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

  sendTableModificationCommand(tablePcid, pcmodel, page_model, modification, nr, cellPcid, cnt) {
    const af = this.actionFactory;
    const update_action = af.table().command().modifyTable(
      tablePcid,
      pcmodel.content,
      modification,
      nr,
      cellPcid,
      cnt
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

      for (const [key, value] of Object.entries(pl.pcModel)) {
        page_model.setPCModel(key, value);
      }

      //      il.IntLink.refresh();           // missing
      this.tableUI.head_selection_initialised = false;
      this.tableUI.reInit();
    }
  }

  sendPropertiesSet(pcid, selected, data, page_model, table_model) {
    let setPropertiesAction;
    const af = this.actionFactory;
    const dispatch = this.dispatcher;

    setPropertiesAction = af.table().command().setProperties(
      pcid,
      "Table",
      selected,
      data
    );

    this.client.sendCommand(setPropertiesAction).then(result => {
      const pl = result.getPayload();
      this.handleModificationResponse(pl, page_model);
      dispatch.dispatch(af.table().editor().switchFormatCells());
    });
  }

  sendToggleMerge(pcid, selected, page_model, table_model) {
    let toggleMergeAction;
    const af = this.actionFactory;
    const dispatch = this.dispatcher;

    toggleMergeAction = af.table().command().toggleMerge(
      pcid,
      "Table",
      selected
    );

    this.client.sendCommand(toggleMergeAction).then(result => {
      const pl = result.getPayload();
      this.handleModificationResponse(pl, page_model);
      table_model.selectNone();
      dispatch.dispatch(af.table().editor().switchMergeCells());
    });
  }

}