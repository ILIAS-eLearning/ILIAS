var params = function() {
    var
    amendParameterToSignal = function(target, parameter_name, values) {
        target = JSON.parse(target);
        target.options[parameter_name] = values;
        return target;
    },

    amendParameterToUrl = function(target, parameter_name, values) {

        var base = target.split('?')[0],
            params = getParametersFromUrl(decodeURI(target)),
            search = '', k;

        params[parameter_name] = encodeURI(JSON.stringify(values));

        for(k in params) {
            search = search + '&' + k + '=' + params[k];
        }

        target = base + '?' + search.substr(1);
        return target;
    },

    getParametersFromUrl = function (url) {
        var params = {},
            parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
                params[key] = value;
            });
        return params;
    },

    public_interface = {
        getParametersFromUrl: getParametersFromUrl,
        amendParameterToSignal: amendParameterToSignal,
        amendParameterToUrl: amendParameterToUrl
    };

    return public_interface;
};

var data = function(params, $) {
    var
    actions_registry = {},
    /**
     * @param string table_id
     * @param string action_id
     * @param string type 'SIGNAL' | 'URL'
     * @param mixed target
     * @param string parameter_name
     */
    registerAction = function(table_id, action_id, type, target, parameter_name) {
        var r = actions_registry[table_id] || {};
        r[action_id] = {
            type : type,
            target : target,
            param : parameter_name
        };
        actions_registry[table_id] = r;
    },
    /**
     * @param string table_id
     * @param array signal_data
     * @param array row_ids
     */
    doAction = function(table_id, signal_data, row_ids) {
        var act_id = signal_data.options.action,
            action = actions_registry[table_id][act_id],
            target;

        if(action.type === 'URL') {
            target = params.amendParameterToUrl(action.target, action.param, row_ids);
            window.location.href = target;
        }
        if(action.type === 'SIGNAL') {
            target = params.amendParameterToSignal(action.target, action.param, row_ids);
            $('#' + table_id).trigger(
                target.id,
                {
                    'id': target.id,
                    'options': target.options
                }
            );
        }
    },

    doActionForAll = function(table_id, originator) {
        var actions = actions_registry[table_id],
            modal_content = originator.parentNode.parentNode,
            modal_close = modal_content.getElementsByClassName('close')[0],
            selected_action = modal_content
                .getElementsByClassName('modal-body')[0]
                .getElementsByTagName('select')[0].value,
            signal_data;

            if(selected_action in actions) {
                signal_data = {options : {action : selected_action}};
                modal_close.click();
                doAction(table_id, signal_data, ['ALL_OBJECTS']) ;
            }
    };

    collectSelectedRowIds = function(table_id) {
        var table = document.getElementById(table_id),
            cols = table.getElementsByClassName('c-table-data__row-selector'),
            i, col, ret = [];
        for(i = 0; i < cols.length; i = i + 1) {
            col = cols[i];
            if(col.checked) {
                ret.push(col.value);
            }
        }
        return ret;
    },

    selectAll = function(table_id, state) {
        var table = document.getElementById(table_id),
            cols = table.getElementsByClassName('c-table-data__row-selector'),
            selector_all = table.getElementsByClassName('c-table-data__selection_all')[0],
            selector_none = table.getElementsByClassName('c-table-data__selection_none')[0]
            ;
        for(i = 0; i < cols.length; i = i + 1) {
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
    };

    public_interface = {
        registerAction: registerAction,
        doAction: doAction,
        doActionForAll: doActionForAll,
        collectSelectedRowIds: collectSelectedRowIds,
        selectAll: selectAll
    };
    return public_interface;
};

il = il || {};
il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

il.UI.table.data = data(
	new params(),
	$
);
