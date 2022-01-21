var ddpersistence = function(dd_id) {
    var cs = null,
        dd_id,
        key = 'level_id',

    storage = function() {
        if (cs && dd_id !== null) { return cs; }
        return new il.Utilities.CookieStorage(dd_id);
    },

    store = function(level_id) {
        cs = storage();
        if(cs) {
            cs.add(key, level_id);
            cs.store();
        }
    },

    read = function() {
        cs = storage();
        if (!cs) {
            return null;
        }
        return cs.items[key];
    },

    public_interface = {
        read: read,
        store: store
    };
    return public_interface;
};

export default ddpersistence;