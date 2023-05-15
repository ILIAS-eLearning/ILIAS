class Data {
    /**
     * @type {jQuery}
     */
    #jquery;

    /**
     * @type {Params}
     */
    #params;

    /**
     * @type {Keyboardnavigation}
     */
    #kbnav;

    /**
     * @type {{type: {url: string, signal: string}, opt: {mainkey: string, id: string}}
     */
    #actionsConstants;

    /**
     * @type {array<string, array>}
     */
    #actionsRegistry;

    constructor(jquery, params, kbnav) {
        this.#jquery = jquery;
        this.#params = params;
        this.#kbnav = kbnav;
        this.#actionsConstants = {};
        this.#actionsRegistry = {};
    }

    /**
     * @param {string} typeURL
     * @param {string} typeSignal
     * @param {string} optOptions
     * @param {string} optId
     */
    initActionConstants(typeURL, typeSignal, optOptions, optId) {
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
    }

    /**
     * @param {string} targetId
     */
    initKeyboardNavigation(targetId) {
        this.#kbnav.init(targetId);
    }

    /**
     * @param {string} tableId
     * @param {string} actionId
     * @param {string} type 'SIGNAL' | 'URL'
     * @param {mixed} target
     * @param {string} parameterName
     */
    registerAction(tableId, actionId, type, target, parameterName) {
        const r = this.#actionsRegistry[tableId] || {};
        r[actionId] = {
            type,
            target,
            param: parameterName,
        };
        this.#actionsRegistry[tableId] = r;
    }

    /**
     * @param {string} tableId
     * @param {array} signalData
     * @param {string[]} rowIds
     */
    doAction(tableId, signalData, rowIds) {
        const actId = signalData.options.action;
        const action = this.#actionsRegistry[tableId][actId];
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
            this.#jquery(`#${tableId}`).trigger(target.id, opts);
        }
    }

    /**
     * @param {string} tableId
     * @param {HTMLElement} originator
     */
    doActionForAll(tableId, originator) {
        const actions = this.#actionsRegistry[tableId];
        const modalContent = originator.parentNode.parentNode;
        const modalClose = modalContent.getElementsByClassName('close')[0];
        const selectedAction = modalContent
            .getElementsByClassName('modal-body')[0]
            .getElementsByTagName('select')[0].value;

        if (selectedAction in actions) {
            const signalData = { options: { action: selectedAction } };
            modalClose.click();
            this.doAction(tableId, signalData, ['ALL_OBJECTS']);
        }
    }

    /**
     * @param {string} tableId
     * @return {string[]}
     */
    collectSelectedRowIds(tableId) {
        const table = document.getElementById(tableId);
        const cols = table.getElementsByClassName('c-table-data__row-selector');
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
     * @param {string} tableId
     * @param {bool} state
     */
    selectAll(tableId, state) {
        const table = document.getElementById(tableId);
        const cols = table.getElementsByClassName('c-table-data__row-selector');
        const selectorAll = table.getElementsByClassName('c-table-data__selection_all')[0];
        const selectorNone = table.getElementsByClassName('c-table-data__selection_none')[0];

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
}
export default Data;
