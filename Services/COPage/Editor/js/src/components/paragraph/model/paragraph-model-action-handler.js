/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "../actions/paragraph-action-types.js";
import PAGE_ACTIONS from '../../page/actions/page-action-types.js';

/**
 * Paragraph model action handler
 */
export default class ParagraphModelActionHandler {

  /**
   * {PageModel}
   */
  //pageModel;

  /**
   *
   * @param {PageModel} model
   */
  constructor(pageModel) {
    this.pageModel = pageModel;
  }


  /**
   * @param {EditorAction} action
   */
  handle(action) {

    const params = action.getParams();

    if (action.getComponent() === "Page") {

      switch (action.getType()) {

        case PAGE_ACTIONS.COMPONENT_SWITCH:
          if (this.pageModel.getComponentState() !== this.pageModel.STATE_COMPONENT_SERVER_CMD) {
            this.pageModel.setAutoSavedPCId(null);
            this.pageModel.setAddedSection(false);
            this.pageModel.setInitialSectionClass(null);
            this.pageModel.setComponentState(this.pageModel.STATE_COMPONENT_EDIT);
            this.pageModel.setPCModel(params.oldPcid, {
              text: params.oldParameters.text,
              characteristic: params.oldParameters.characteristic
            });
            this.pageModel.setCurrentPageComponent(params.cname, params.newPcid, params.newHierid);
            this.pageModel.setUndoPCModel(
              this.pageModel.getCurrentPCId(),
              this.pageModel.getPCModel(this.pageModel.getCurrentPCId())
            );
          }
          break;

        case PAGE_ACTIONS.COMPONENT_INSERT:
          this.pageModel.setAutoSavedPCId(null);
          this.pageModel.setAddedSection(false);
          this.pageModel.setInitialSectionClass(null);
          this.pageModel.setPCModel(this.pageModel.getCurrentPCId(), {
            text: '',
            characteristic: 'Standard'
          });
          break;

        case PAGE_ACTIONS.COMPONENT_EDIT:
          this.pageModel.setAutoSavedPCId(null);
          this.pageModel.setAddedSection(false);
          this.pageModel.setInitialSectionClass(null);
          break;

      }
    }

    if (action.getComponent() === "Paragraph") {

      switch (action.getType()) {

        case ACTIONS.PARAGRAPH_CLASS:
          const pcmodel = this.pageModel.getPCModel(this.pageModel.getCurrentPCId());
          if (pcmodel) {
            pcmodel.characteristic = params.characteristic;
          }
          this.pageModel.setPCModel(this.pageModel.getCurrentPCId(), pcmodel);
          break;

        case ACTIONS.SAVE_RETURN:
          this.pageModel.setState(this.pageModel.STATE_SERVER_CMD);
          this.pageModel.setPCModel(this.pageModel.getCurrentPCId(), {
            text: params.text,
            characteristic: params.characteristic
          });
          // note: we keep the component state and current component here, so that handlers
          // can use this
          break;

        case ACTIONS.AUTO_SAVE:
          this.pageModel.setPCModel(this.pageModel.getCurrentPCId(), {
            text: params.text,
            characteristic: params.characteristic
          });
          if (this.pageModel.getComponentState() === this.pageModel.STATE_COMPONENT_INSERT) {
            this.pageModel.setAutoSavedPCId(this.pageModel.getCurrentPCId());
          }
          break;

        // switch from insert to edit mode after auto insert being called
        case ACTIONS.AUTO_INSERT_POST:
          this.pageModel.setComponentState(this.pageModel.STATE_COMPONENT_EDIT);
          break;

        // switch from insert to edit mode after auto insert being called
        case ACTIONS.SPLIT_POST:
          this.pageModel.setAutoSavedPCId(null);
          this.pageModel.setComponentState(this.pageModel.STATE_COMPONENT_EDIT);
          break;

        case ACTIONS.SPLIT_PARAGRAPH:
          let splitIds = [];
          for (let k=0; k < params.contents.length; k++) {
            if (k === 0) {
              console.log("Split-1-");
              console.log(this.pageModel.getCurrentPCId());
              console.log(params.contents[k]);
              this.pageModel.setPCModel(this.pageModel.getCurrentPCId(), {
                text: params.contents[k],
                characteristic: params.characteristic
              });
            } else {
              const pcid = this.pageModel.getNewPCId();
              console.log("Split-2-");
              console.log(pcid);
              console.log(params.contents[k]);
              splitIds.push(pcid);
              this.pageModel.setPCModel(pcid, {
                text: params.contents[k],
                characteristic: "Standard"
              });
              this.pageModel.setCurrentPageComponent("Paragraph", pcid, "");
              this.pageModel.setUndoPCModel(
                this.pageModel.getCurrentPCId(),
                this.pageModel.getPCModel(this.pageModel.getCurrentPCId())
              );
            }
          }
          this.pageModel.setSplitPCIds(splitIds);
          break;

        case ACTIONS.MERGE_PREVIOUS:
          const previousModel = this.pageModel.getPCModel(params.previousPcid);
          this.pageModel.setPCModel(params.previousPcid, {
            text: params.newPreviousContent,
            characteristic: previousModel.characteristic
          });
          this.pageModel.setCurrentPageComponent("Paragraph", params.previousPcid,
            "");
          this.pageModel.setUndoPCModel(
            this.pageModel.getCurrentPCId(),
            this.pageModel.getPCModel(this.pageModel.getCurrentPCId())
          );
          break;

        case ACTIONS.SECTION_CLASS:
          if (params.oldSectionCharacteristic === "" && params.newSectionCharacteristic !== "") {
            this.pageModel.setAddedSection(true);
          }
          if (this.pageModel.getInitialSectionClass() === null) {
            this.pageModel.setInitialSectionClass(params.oldSectionCharacteristic);
          }
          this.pageModel.setPCModel(this.pageModel.getCurrentPCId(), {
            text: params.parText,
            characteristic: params.parCharacteristic
          });
          break;
      }
    }
  }
}