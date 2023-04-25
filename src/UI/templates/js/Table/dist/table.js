(function (il, $$1) {
    'use strict';

    il = il && Object.prototype.hasOwnProperty.call(il, 'default') ? il['default'] : il;
    $$1 = $$1 && Object.prototype.hasOwnProperty.call($$1, 'default') ? $$1['default'] : $$1;

    class Params {
        /**
         * @param string target
         * @param string parameter_name
         * @param array values
         */
        amendParameterToSignal(target, parameter_name, values) {
            let sig = JSON.parse(target);
            sig.options[parameter_name] = values;
            return sig;
        }

        /**
         * @param string target
         * @param string parameter_name
         * @param array values
         */
        amendParameterToUrl(target, parameter_name, values) {
            const base = target.split('?')[0];
            let params = this.getParametersFromUrl(decodeURI(target));
            let search = '';
            let k;

            params[parameter_name] = encodeURI(JSON.stringify(values));

            for (k in params) {
                search = `${search}&${k}=${params[k]}`;
            }

            return `${base}?${search.substr(1)}`;
        }

        /**
         * @param string url
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

        #jquery;
        #params;
        #kbnav;
        #actions_registry;
        
        constructor(jquery, params, kbnav) {
            this.jquery = jquery;
            this.params = params;
            this.kbnav = kbnav;
            this.actions_registry = {};
        }

        /**
         * @param string target_id
         */
        initKeyboardNavigation(target_id) {
            this.kbnav.init(target_id);
        }

        /**
         * @param string table_id
         * @param string action_id
         * @param string type 'SIGNAL' | 'URL'
         * @param mixed target
         * @param string parameter_name
         */
        registerAction(table_id, action_id, type, target, parameter_name) {
            let r = this.actions_registry[table_id] || {};
            r[action_id] = {
                type : type,
                target : target,
                param : parameter_name
            };
            this.actions_registry[table_id] = r;
        }

        /**
         * @param string table_id
         * @param array signal_data
         * @param array row_ids
         */
        doAction(table_id, signal_data, row_ids) {
            const act_id = signal_data.options.action;
            const action = this.actions_registry[table_id][act_id];
            let target;

            if(action.type === 'URL') {
                target = this.params.amendParameterToUrl(action.target, action.param, row_ids);
                window.location.href = target;
            }
            if(action.type === 'SIGNAL') {
                target = this.params.amendParameterToSignal(action.target, action.param, row_ids);
                $('#' + table_id).trigger(
                    target.id,
                    {
                        'id': target.id,
                        'options': target.options
                    }
                );
            }
        }

        /**
         * @param string table_id
         * @param node originator
         */
        doActionForAll(table_id, originator) {
            const actions = this.actions_registry[table_id];
            const modal_content = originator.parentNode.parentNode;
            const modal_close = modal_content.getElementsByClassName('close')[0];
            const selected_action = modal_content
                .getElementsByClassName('modal-body')[0]
                .getElementsByTagName('select')[0].value;

            if(selected_action in actions) {
                let signal_data = {options : {action : selected_action}};
                modal_close.click();
                this.doAction(table_id, signal_data, ['ALL_OBJECTS']) ;
            }
        }

        /**
         * @param string table_id
         */
        collectSelectedRowIds(table_id) {
            const table = document.getElementById(table_id);
            const cols = table.getElementsByClassName('c-table-data__row-selector');
            const ret = [];
            let col;
            let i = 0;

            for(i; i < cols.length; i = i + 1) {
                col = cols[i];
                if(col.checked) {
                    ret.push(col.value);
                }
            }
            return ret;
        }
        
        /**
         * @param string table_id
         * @param bool state
         */
        selectAll(table_id, state) {
            const table = document.getElementById(table_id);
            const cols = table.getElementsByClassName('c-table-data__row-selector');
            const selector_all = table.getElementsByClassName('c-table-data__selection_all')[0];
            const selector_none = table.getElementsByClassName('c-table-data__selection_none')[0];
            let col;
            let i = 0;
            
            for(i; i < cols.length; i = i + 1) {
                col = cols[i];
                col.checked = state;
            }
            if(state) {
                selector_all.style.display='none';
                selector_none.style.display='block';
            } else {
                selector_all.style.display='block';
                selector_none.style.display='none';
            }
        }
    }

    class Keyboardnav {
        #keys;
        #supported_keys;

         constructor() {
            this.keys = {
                ESC: 27,
                SPACE: 32,
                PAGE_UP: 33,
                PAGE_DOWN: 34,
                END: 35,
                HOME: 36,
                LEFT: 37,
                UP: 38,
                RIGHT: 39,
                DOWN: 40
            };
            this.supported_keys = [ 
                this.keys.LEFT,
                this.keys.RIGHT, 
                this.keys.UP, 
                this.keys.DOWN
            ];
        }

        /**
         * @param Event event
         * @param keyboardnav _self
         */
        onKey(event, _self) {

            if (_self.supported_keys.indexOf(event.which) === -1) {
                return;
            }

            const cell = event.target.closest('td, th');
            const row = cell.closest('tr');
            const table = row.closest('table');
            let cell_index = cell.cellIndex;
            let row_index = row.rowIndex;

            switch (event.which) {
                case _self.keys.LEFT:
                    cell_index -= 1;
                    break;
                case _self.keys.RIGHT:
                    cell_index += 1;
                    break;
                case _self.keys.UP: 
                    row_index = row_index -= 1;
                    break;
                case _self.keys.DOWN:
                    row_index = row.rowIndex + 1;
                    break;
              }
            
            if (row_index < 0 || cell_index < 0
                || row_index >= table.rows.length 
                || cell_index >= row.cells.length
            ) {
                return;
            }
            _self.focusCell(table, cell, row_index, cell_index);
        }

        focusCell(table, cell, row_index, cell_index) {
            const next_cell = table.rows[row_index].cells[cell_index];
            next_cell.focus();
            cell.setAttribute('tabindex', -1);
            next_cell.setAttribute('tabindex', 0);
        }

        /**
         * @param string target_id
         */
        init(target_id) {
            document.querySelector('#' + target_id).addEventListener('keydown', (event)=>this.onKey(event, this));
        }

    }

    il.UI = il.UI || {};
    il.UI.table = il.UI.table || {};

    il.UI.table.data = new Data(
        $$1,
        new Params(),
        new Keyboardnav()
    );

}(il, $));
