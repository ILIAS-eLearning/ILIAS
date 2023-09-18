var ddmodel = function() {
    var
    data = [],

    classes = {
        level : {
            id: null,
            parent: null,
            engaged : false,
            label : '',
        }
    },
    
    factories = {
        cloned: (obj, params) => Object.assign({}, obj, params),
        level : (label, parent) => factories.cloned(classes.level, {
            id : data.length.toString(),
            label : label,
            parent : parent
        }),
    },

    actions = {
        addLevel : function(label, parent) {
            if(! parent) {
                parent = null;
            }
            var level = factories.level(label, parent);
            data[level.id] = level;
            return level;
        },
        /**
         * @param  {String} id
         */
        engageLevel : function(id) {
            for(var idx in data) {
                data[idx].engaged = false;
                if(data[idx].id === id) {
                    data[idx].engaged = true;
                }
            }
        },
        getCurrent : function() {
            for(var idx in data) {
                if(data[idx].engaged) {
                    return data[idx];
                };
            }
            return data[0];
        },
        upLevel : function() {
            var cur = actions.getCurrent();
            if(cur.parent) {
                actions.engageLevel(data[cur.parent].id);
            }
        }
    },

    public_interface = {
        actions : actions
    };
    return public_interface;
}
export default ddmodel;