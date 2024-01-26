/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "../actions/paragraph-action-types.js";
import PAGE_ACTIONS from "../../page/actions/page-action-types.js";

/**
 * Paragraph UI action handler
 */
export default class ParagraphUIActionHandler {

  /**
   * @type {ParagraphUI}
   */
  //ui;

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
    this.ui = null;
  }

  /**
   * @param {ParagraphUI} ui
   */
  setUI(ui) {
    this.ui = ui;
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
   */
  handle(action, page_model) {

    const dispatcher = this.dispatcher;
    const actionFactory = this.actionFactory;
    const client = this.client;
    let form_sent = false;

    const params = action.getParams();

    // page actions
    if (action.getComponent() === "Page" && page_model.getCurrentPCName() === "Paragraph") {
      switch (action.getType()) {

        case PAGE_ACTIONS.COMPONENT_INSERT:
          this.ui.insertParagraph(page_model.getCurrentPCId(), page_model.getCurrentInsertPCId());
          break;

        case PAGE_ACTIONS.COMPONENT_EDIT:
          const pcModel = page_model.getPCModel(page_model.getCurrentPCId());
          if (pcModel.characteristic !== "Code") {
            this.ui.editParagraph(page_model.getCurrentPCId());
          } else {
            client.sendForm(actionFactory.page().command().editLegacy("SourceCode", params.pcid,
                params.hierid));
            form_sent = true;
          }
          break;

        case PAGE_ACTIONS.COMPONENT_CANCEL:
          this.ui.cmdCancel();
          this.sendCancelCommand(page_model);
          break;

        case PAGE_ACTIONS.COMPONENT_SWITCH:
          if (page_model.getComponentState() !== page_model.STATE_COMPONENT_SERVER_CMD) {
            if (params.oldComponentState === page_model.STATE_COMPONENT_INSERT) {
              this.sendInsertCommand(
                params.oldPcid,
                page_model.getCurrentInsertPCId(),
                page_model.getPCModel(params.oldPcid),
                page_model
              );
              this.ui.handleSaveOnInsert();
            } else {
              this.sendUpdateCommand(
                params.oldPcid,
                page_model.getPCModel(params.oldPcid),
                page_model
              );
              this.ui.handleSaveOnEdit();
            }
            this.ui.editParagraph(page_model.getCurrentPCId(), params.switchToEnd);
          }
          break;
      }
    }

    if (action.getComponent() === "Paragraph") {
      switch (action.getType()) {


        case ACTIONS.PARAGRAPH_CLASS:
          this.ui.setParagraphClass(params.characteristic);
          break;

        case ACTIONS.SELECTION_FORMAT:
          this.ui.cmdSpan(params.format);
          break;

        case ACTIONS.SELECTION_REMOVE_FORMAT:
          this.ui.cmdRemoveFormat();
          break;

        case ACTIONS.SELECTION_KEYWORD:
          this.ui.cmdKeyword();
          break;

        case ACTIONS.SELECTION_TEX:
          this.ui.cmdTex();
          break;

        case ACTIONS.SELECTION_FN:
          this.ui.cmdFn();
          break;

        case ACTIONS.SELECTION_ANCHOR:
          this.ui.cmdAnc();
          break;

        case ACTIONS.LIST_BULLET:
          this.ui.cmdBList();
          break;

        case ACTIONS.LIST_NUMBER:
          this.ui.cmdNList();
          break;

        case ACTIONS.LIST_OUTDENT:
          this.ui.cmdListOutdent();
          break;

        case ACTIONS.LIST_INDENT:
          this.ui.cmdListIndent();
          break;

        case ACTIONS.LINK_WIKI_SELECTION:
          this.ui.cmdWikiLinkSelection(params.url);
          break;

        case ACTIONS.LINK_WIKI:
          this.ui.cmdWikiLink();
          break;

        case ACTIONS.LINK_INTERNAL:
          this.ui.cmdIntLink();
          break;

        case ACTIONS.SECTION_CLASS:
          this.handleSectionClass(params.oldSectionCharacteristic, params.newSectionCharacteristic, page_model);
          break;

        case ACTIONS.LINK_EXTERNAL:
          this.ui.cmdExtLink();
          break;

        case ACTIONS.LINK_USER:
          this.ui.cmdUserLink();
          break;

        case ACTIONS.SAVE_RETURN:
          if (page_model.getComponentState() === page_model.STATE_COMPONENT_INSERT) {
            this.sendInsertCommand(
              page_model.getCurrentPCId(),
              page_model.getCurrentInsertPCId(),
              page_model.getPCModel(page_model.getCurrentPCId()),
              page_model
            );
            this.ui.handleSaveOnInsert();
          } else {
            this.sendUpdateCommand(
              page_model.getCurrentPCId(),
              page_model.getPCModel(page_model.getCurrentPCId()),
              page_model
            );
            this.ui.handleSaveOnEdit();
          }
          break;

        case ACTIONS.AUTO_SAVE:
          if (page_model.getState() === page_model.STATE_COMPONENT) {
            if (page_model.getComponentState() === page_model.STATE_COMPONENT_INSERT) {
              this.sendAutoInsertCommand(
                page_model.getCurrentPCId(),
                page_model.getCurrentInsertPCId(),
                page_model.getPCModel(page_model.getCurrentPCId()),
                page_model
              );
            } else {
              this.sendAutoSaveCommand(
                page_model.getCurrentPCId(),
                page_model.getPCModel(page_model.getCurrentPCId()),
                page_model
              );
            }
          }
          break;

        case ACTIONS.SPLIT_PARAGRAPH:
          let newParagraphs = [];
          let after_pcid = "";
          const splitIds = page_model.getSplitPCIds();
          let insertMode = false;
          if (page_model.getComponentState() === page_model.STATE_COMPONENT_INSERT) {
            after_pcid = page_model.getCurrentInsertPCId()
            insertMode = true;
          }
          let pcmodel = page_model.getPCModel(params.pcid);
          for (let k = 0; k < splitIds.length; k++) {
            newParagraphs.push({
                pcid: splitIds[k],
                model: page_model.getPCModel(splitIds[k])
              });
          }
          this.ui.performAutoSplit(
            params.pcid,
            pcmodel.text,
            pcmodel.characteristic,
            newParagraphs
          );
          this.sendSplitCommand(insertMode, after_pcid, params.pcid,
            pcmodel.text,
            pcmodel.characteristic,
            newParagraphs,
            page_model);
          break;

        case ACTIONS.MERGE_PREVIOUS:
          this.ui.performMergePrevious(
            params.pcid,
            params.previousPcid,
            params.newPreviousContent
          );
          this.sendMergePreviousCommand(params.pcid,
            params.previousPcid,
            params.newPreviousContent,
            page_model);
          break;

      }
    }

    switch (page_model.getComponentState()) {
      case page_model.STATE_COMPONENT_SERVER_CMD:
        this.ui.disableEditing();
        break;
      default:
        //this.ui.enableButtons();
        break;
    }
  }

  sendInsertCommand(pcid, target_pcid, pcmodel, page_model) {
    const af = this.actionFactory;
    const insert_action = af.paragraph().command().insert(
        target_pcid,
        pcid,
        pcmodel.text,
        pcmodel.characteristic,
        page_model.getInsertFromPlaceholder()
    );
    this.client.sendCommand(insert_action).then(result => {
      const pl = result.getPayload();
      this.handleSaveResponse(pcid, pl, page_model);
    });
  }

  sendAutoInsertCommand(pcid, target_pcid, pcmodel, page_model) {
    const af = this.actionFactory;
    const dispatch = this.dispatcher;
    const insert_action = af.paragraph().command().autoInsert(
        target_pcid,
        pcid,
        pcmodel.text,
        pcmodel.characteristic,
        page_model.getInsertFromPlaceholder()
    );
    this.ui.autoSaveStarted();
    this.client.sendCommand(insert_action).then(result => {
      this.ui.autoSaveEnded();
      const pl = result.getPayload();

      dispatch.dispatch(af.paragraph().editor().autoInsertPostProcessing());

      this.handleSaveResponse(pcid, pl, page_model);
    });
  }

  /**
   *
   * @param pcid
   * @param pcmodel
   * @param page_model
   * @param initialSectionClass only set from cancel command, if reset is necessary
   */
  sendUpdateCommand(pcid, pcmodel, page_model, initialSectionClass = null) {
    const af = this.actionFactory;
    const update_action = af.paragraph().command().update(
      pcid,
      pcmodel.text,
      pcmodel.characteristic,
      initialSectionClass
    );
    this.client.sendCommand(update_action).then(result => {
      const pl = result.getPayload();
      this.handleSaveResponse(pcid, pl, page_model);
    });
  }

  sendAutoSaveCommand(pcid, pcmodel, page_model) {
    const af = this.actionFactory;
    const auto_save_action = af.paragraph().command().autoSave(
      pcid,
      pcmodel.text,
      pcmodel.characteristic
    );
    this.ui.autoSaveStarted();
    this.client.sendCommand(auto_save_action).then(result => {
      this.ui.autoSaveEnded();
      const pl = result.getPayload();
      this.handleSaveResponse(pcid, pl, page_model);
    });
  }

  handleSaveResponse(pcid, pl, page_model) {
    const dispatch = this.dispatcher;
    const af = this.actionFactory;
    const still_editing = (pcid === page_model.getCurrentPCId() && page_model.getState() === page_model.STATE_COMPONENT);
    if (pl.error) {
      this.ui.showError(pl.error);
    } else {
      if (pl.renderedContent && !still_editing) {
        this.ui.replaceRenderedParagraph(pcid, pl.renderedContent);
      }
      if (pl.last_update && still_editing) {
        this.ui.showLastUpdate(pl.last_update);
      }
    }

    // this is the case, if we return to the page (the page is put in STATE_SERVER_CMD state)
    if (page_model.getState() === page_model.STATE_SERVER_CMD) {
      dispatch.dispatch(af.page().editor().enablePageEditing());
    }
  }

  sendSplitCommand(insertMode, after_pcid, pcid, text, characteristic, newParagraphs, page_model) {
    const af = this.actionFactory;
    const dispatch = this.dispatcher;
    const insert_action = af.paragraph().command().split(
        insertMode,
        after_pcid,
        pcid,
        text,
        characteristic,
        newParagraphs,
        page_model.getInsertFromPlaceholder()
    );
    this.ui.autoSaveStarted();
    // directly go to "EDIT" mode, since server "knows" all elements now
    dispatch.dispatch(af.paragraph().editor().splitPostProcessing());
    this.client.sendCommand(insert_action).then(result => {
      this.ui.autoSaveEnded();
      const pl = result.getPayload();

      this.handleSaveResponseSplit(pl, page_model);
    });
  }

  handleSaveResponseSplit(pl, page_model) {
    let still_editing;

    if (pl.error) {
      this.ui.showError(pl.error);
    } else {
      for (const [pcid, renderedContent] of Object.entries(pl.renderedContent)) {
        still_editing = (pcid === page_model.getCurrentPCId() && page_model.getState() === page_model.STATE_COMPONENT);
        if (renderedContent && !still_editing) {
          this.ui.replaceRenderedParagraph(pcid, renderedContent);
        }
      }
      if (pl.last_update) {
        this.ui.showLastUpdate(pl.last_update);
      }
    }
  }

  handleSectionClass(oldCharacteristic, newCharacteristic, page_model) {
    const af = this.actionFactory;
    const dispatch = this.dispatcher;

    this.ui.setSectionClass(page_model.getCurrentPCId(), newCharacteristic);

    const is_insert = (page_model.getComponentState() === page_model.STATE_COMPONENT_INSERT);
    page_model.setComponentState(page_model.STATE_COMPONENT_SERVER_CMD);

    const secClassAction = af.paragraph().command().sectionClass(
      page_model.getCurrentPCId(),
      page_model.getCurrentInsertPCId(),
      page_model.getPCModel(page_model.getCurrentPCId()),
      is_insert,
      oldCharacteristic,
      newCharacteristic
    );

    //this.ui.autoSaveStarted();
    this.client.sendCommand(secClassAction).then(result => {
      //this.ui.autoSaveEnded();
      const pl = result.getPayload();

      if (is_insert) {
        dispatch.dispatch(af.paragraph().editor().autoInsertPostProcessing());
      }
      this.ui.enableEditing();
      page_model.setComponentState(page_model.STATE_COMPONENT_EDIT);

      if ((oldCharacteristic === "" && newCharacteristic !== "") ||
        (oldCharacteristic !== "" && newCharacteristic === "")) {
        this.ui.pageModifier.handlePageReloadResponse(result);
        let content_el = document.querySelector("[data-copg-ed-type='pc-area'][data-pcid='" + page_model.getCurrentPCId() + "']");
        //this.ui.tinyWrapper.setGhostAt(content_el);
        //this.ui.tinyWrapper.synchInputRegion();
        //this.ui.tinyWrapper.copyInputToGhost();
        this.ui.tinyWrapper.stopEditing();
        this.ui.editParagraph(page_model.getCurrentPCId());
        this.ui.syncTiny();
      }
    });

  }

  sendMergePreviousCommand(pcid, previousPcid, newPreviousContent, page_model) {
    const af = this.actionFactory;
    const dispatch = this.dispatcher;

    const previousModel = page_model.getPCModel(previousPcid);

    const merge_action = af.paragraph().command().mergePrevious(
      pcid,
      previousPcid,
      newPreviousContent,
      previousModel.characteristic
    );
    this.ui.autoSaveStarted();
    this.client.sendCommand(merge_action).then(result => {
      this.ui.autoSaveEnded();
      const pl = result.getPayload();

      //dispatch.dispatch(af.paragraph().editor().splitPostProcessing());

      //this.handleSaveResponseSplit(pl, page_model);
    });
  }

  sendCancelCommand(page_model) {
    const af = this.actionFactory;
    const pcModel = page_model.getPCModel(page_model.getCurrentPCId());
    //console.log("send Cancel " + page_model.getCurrentPCId());


    if (page_model.getAddedSection()) {

      const cancel_action = af.paragraph().command().cancel(
        page_model.getCurrentPCId(),
        pcModel.text,
        pcModel.characteristic
      );

      //this.ui.autoSaveStarted();
      this.client.sendCommand(cancel_action).then(result => {
        const pl = result.getPayload();
        this.ui.pageModifier.handlePageReloadResponse(result);
      });
    } else if (page_model.getComponentState() === page_model.STATE_COMPONENT_INSERT) {

      this.ui.pageModifier.removeInsertedComponent(page_model.getCurrentPCId());

    } else {

      if (page_model.getAutoSavedPCId() === page_model.getCurrentPCId()) {
        // the element has been inserted, autosaved but now canceled
        // we need to save the "undo" state back, if autosave made changes
        this.sendDeleteCommand(page_model.getCurrentPCId());

      } else {
        if (page_model.getInitialSectionClass()) {
          this.ui.setSectionClass(page_model.getCurrentPCId(), page_model.getInitialSectionClass());
        }
        // we need to save the "undo" state back, if autosave made changes
        this.sendUpdateCommand(page_model.getCurrentPCId(),
          page_model.getPCModel(page_model.getCurrentPCId()),
          page_model,
          page_model.getInitialSectionClass()
        );
      }
    }
  }

  sendDeleteCommand(pcid) {
    const af = this.actionFactory;
    const delete_action = af.paragraph().command().delete(
      pcid
    );
    this.client.sendCommand(delete_action).then(result => {
      const pl = result.getPayload();
      this.ui.pageModifier.handlePageReloadResponse(result);
    });
  }


}