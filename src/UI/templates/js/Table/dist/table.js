(function (il, $) {
    'use strict';

    il = il && Object.prototype.hasOwnProperty.call(il, 'default') ? il['default'] : il;
    $ = $ && Object.prototype.hasOwnProperty.call($, 'default') ? $['default'] : $;

    class Params {
        /**
         * @param {string} target
         * @param {string} parameterName
         * @param {string[]} values
         * @return {object}
         */
        amendParameterToSignal(target, parameterName, values) {
            const sig = JSON.parse(target);
            sig.options[parameterName] = values;
            return sig;
        }

        /**
         * @param {string} target
         * @param {string} parameterName
         * @param {string[]} values
         * @return {string}
         */
        amendParameterToUrl(target, parameterName, values) {
            const base = target.split('?')[0];
            const params = this.getParametersFromUrl(decodeURI(target));
            let search = '';

            params[parameterName] = encodeURI(JSON.stringify(values));
            Object.keys(params).forEach(
                (k) => {
                    search = `${search}&${k}=${params[k]}`;
                },
            );
            return `${base}?${search.substr(1)}`;
        }

        /**
         * @param {string} url
         * @return {array<string,string>}
         */
        getParametersFromUrl(url) {
            const params = {};
            url.replace(/[?&]+([^=&]+)=([^&]*)/gi, (m, key, value) => {
                params[key] = value;
            });
            return params;
        }
    }

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

    class Keyboardnav {
        /**
         * @type {number}
         */
        #keyLeft;

        /**
         * @type {number}
         */
        #keyUp;

        /**
         * @type {number}
         */
        #keyRight;

        /**
         * @type {number}
         */
        #keyDown;

        constructor() {
            this.#keyLeft = 37;
            this.#keyUp = 38;
            this.#keyRight = 39;
            this.#keyDown = 40;
        }

        /**
         * @param {KeyboardEvent} event
         */
        navigateCellsWithArrowKeys(event) {
            if (!(event.which === this.#keyLeft
                || event.which === this.#keyUp
                || event.which === this.#keyRight
                || event.which === this.#keyDown
            )) {
                return;
            }

            const cell = event.target.closest('td, th');
            const row = cell.closest('tr');
            const table = row.closest('table');
            let { cellIndex } = cell;
            let { rowIndex } = row;

            switch (event.which) {
            case this.#keyLeft:
                cellIndex -= 1;
                break;
            case this.#keyRight:
                cellIndex += 1;
                break;
            case this.#keyUp:
                rowIndex -= 1;
                break;
            case this.#keyDown:
                rowIndex += 1;
                break;
            }

            if (rowIndex < 0 || cellIndex < 0
                || rowIndex >= table.rows.length
                || cellIndex >= row.cells.length
            ) {
                return;
            }
            this.focusCell(table, cell, rowIndex, cellIndex);
        }

        /**
         * @param {HTMLTableElement} table
         * @param {HTMLTableCellElement} cell
         * @param {number} rowIndex
         * @param {number} cellIndex
         */
        focusCell(table, cell, rowIndex, cellIndex) {
            const nextCell = table.rows[rowIndex].cells[cellIndex];
            nextCell.focus();
            cell.setAttribute('tabindex', -1);
            nextCell.setAttribute('tabindex', 0);
        }

        /**
         * @param {string} targetId
         */
        init(targetId) {
            document.getElementById(targetId)?.addEventListener('keydown', (event) => this.navigateCellsWithArrowKeys(event, this));
        }
    }

    il.UI = il.UI || {};
    il.UI.table = il.UI.table || {};

    il.UI.table.data = new Data(
        $,
        new Params(),
        new Keyboardnav(),
    );

}(il, $));
