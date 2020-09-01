
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
                target.options
            );
        }
    },

    collectSelectedRowIds = function(table_id) {
        var table = document.getElementById(table_id),
            cols = table.getElementsByClassName('row-selector'),
            i, col, ret = [];
            for(i = 0; i < cols.length; i = i + 1) {
                col = cols[i];
                if(col.checked) {
                    ret.push(col.value);
                }
            }
            return ret;
    },

    public_interface = {
        registerAction: registerAction,
        doAction: doAction,
        collectSelectedRowIds: collectSelectedRowIds
    };
    return public_interface;
}

export default data;
