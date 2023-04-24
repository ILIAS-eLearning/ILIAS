(function (il, $$1) {
    'use strict';

    il = il && Object.prototype.hasOwnProperty.call(il, 'default') ? il['default'] : il;
    $$1 = $$1 && Object.prototype.hasOwnProperty.call($$1, 'default') ? $$1['default'] : $$1;

    class params {
        /**
         * @param string target
         * @param string parameter_name
         * @param array values
         */
        amendParameterToSignal(target, parameter_name, values) {
            target = JSON.parse(target);
            target.options[parameter_name] = values;
            return target;
        }

        /**
         * @param string target
         * @param string parameter_name
         * @param array values
         */
        amendParameterToUrl(target, parameter_name, values) {
            let base = target.split('?')[0];
            let params = this.getParametersFromUrl(decodeURI(target));
            let search = '';
            let k;

            params[parameter_name] = encodeURI(JSON.stringify(values));

            for(k in params) {
                search = search + '&' + k + '=' + params[k];
            }

            target = base + '?' + search.substr(1);
            return target;
        }

        /**
         * @param string url
         */
        getParametersFromUrl(url) {
            let params = {};
            let parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
                    params[key] = value;
                });
            return params;
        }
    }

    class data {

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
            let act_id = signal_data.options.action;
            let action = this.actions_registry[table_id][act_id];
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
            let actions = this.actions_registry[table_id];
            let modal_content = originator.parentNode.parentNode;
            let modal_close = modal_content.getElementsByClassName('close')[0];
            let selected_action = modal_content
                .getElementsByClassName('modal-body')[0]
                .getElementsByTagName('select')[0].value;

            if(selected_action in actions) {
                let signal_data = {options : {action : selected_action}};
                modal_close.click();
                doAction(table_id, signal_data, ['ALL_OBJECTS']) ;
            }
        }

        /**
         * @param string table_id
         */
        collectSelectedRowIds(table_id) {
            let table = document.getElementById(table_id);
            let cols = table.getElementsByClassName('c-table-data__row-selector');
            let ret = [];
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
            let table = document.getElementById(table_id);
            let cols = table.getElementsByClassName('c-table-data__row-selector');
            let selector_all = table.getElementsByClassName('c-table-data__selection_all')[0];
            let selector_none = table.getElementsByClassName('c-table-data__selection_none')[0];
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

    class keyboardnav {
        keys = {
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
        supported_keys = [ 
            this.keys.LEFT,
            this.keys.RIGHT, 
            this.keys.UP, 
            this.keys.DOWN
        ];
         
        /**
         * @param Event event
         * @param keyboardnav _self
         */
        onKey(event, _self) {

            if (_self.supported_keys.indexOf(event.which) === -1) {
                return;
            }

            let cell = event.target.closest('td, th');
            let row = cell.closest('tr');
            let table = row.closest('table');
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
            let next_cell = table.rows[row_index].cells[cell_index];
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

    il.UI.table.data = new data(
    	$$1,
    	new params(),
    	new keyboardnav()
    );

}(il, $));
