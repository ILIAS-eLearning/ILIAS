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

import ACTIONS from "../actions/placeholder-action-types.js";
import PAGE_ACTIONS from "../../page/actions/page-action-types.js";

/**
 * PlaceHolder UI action handler
 */
export default class PlaceHolderUIActionHandler {

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
        this.ui = null;
        this.dispatcher = null;
    }

    /**
     * @param {PlaceHolderUI} ui
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
        if (action.getComponent() === "Page" && page_model.getCurrentPCName() === "PlaceHolder") {
            switch (action.getType()) {

                case PAGE_ACTIONS.COMPONENT_EDIT:
                    this.handleEditCommand(page_model, params);
                    break;
            }
        }

        // page actions
        if (action.getComponent() === "Page" && page_model.getCurrentPCName() === "Paragraph") {
            switch (action.getType()) {

                case PAGE_ACTIONS.COMPONENT_CANCEL:
                    this.handleCancelCommand(page_model);
                    break;

            }
        }

        if (action.getComponent() === "PlaceHolder") {
            switch (action.getType()) {

                //case ACTIONS.SELECT_POOL:
                    //this.ui.handlePoolSelection(params.url, params.pcid);
                //    break;

            }
        }
    }

    handleEditCommand(page_model, params) {
        const dispatcher = this.dispatcher;
        const actionFactory = this.actionFactory;
        const client = this.client;

        if (this.ui.uiModel.config.editPlaceholders) {
            client.sendForm(actionFactory.page().command().editLegacy(params.cname, params.pcid,
                params.hierid));
            //form_sent = true;
        } else {
            let pcModel = page_model.getPCModel(page_model.getCurrentPCId());
            if (pcModel.contentClass === "Text") {

                // hide the placeholde
                //console.log("HIDE " + page_model.getCurrentPCId());
                this.ui.hidePlaceholder(page_model.getCurrentPCId());

                // insert paragraph after placeholder
                dispatcher.dispatch(actionFactory.page().editor().componentInsert("Paragraph",
                    page_model.getCurrentPCId(),
                    null,
                    null,
                    true));
            } else {
                client.sendForm(actionFactory.page().command().editLegacy(params.cname, params.pcid,
                    params.hierid));
                //form_sent = true;
            }
        }
    }

    handleCancelCommand(page_model) {
        if (page_model.getComponentState() === page_model.STATE_COMPONENT_INSERT) {
            if (page_model.getCurrentPCName() === "Paragraph" &&
                page_model.getInsertFromPlaceholder()) {
                // hide the placeholde
                //console.log("SHOW " + page_model.getCurrentInsertPCId());
                this.ui.showPlaceholder(page_model.getCurrentInsertPCId());
            }
        }
    }
}