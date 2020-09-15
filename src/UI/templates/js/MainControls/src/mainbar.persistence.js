
var persistence = function() {
    var cs,
    storage = function() {
        if(cs) { return cs; }
        cookie_name = hash(entry_ids());
        return new il.Utilities.CookieStorage(cookie_name);
    },
    model_state = function() {
        return il.UI.maincontrols.mainbar.model.getState();
    },
    entry_ids = function() {
        var entries = model_state().entries,
            base = '';
        for(idx in entries) {
            base = base + idx;
        }
        return base;
    },
    hash = function(str) {
        var hash = 0,
            len = str.length,
            i, chr;

        for (i = 0; i < len; i = i + 1) {
            chr = str.charCodeAt(i);
            hash  = ((hash << 5) - hash) + chr;
            hash |= 0; // Convert to 32bit integer
        }
        return hash;
    },
    compressEntries = function(entries) {
        var k, v, ret = {};
        for(k in entries) {
            v = entries[k];
            ret[k] = [
                v.removeable ? 1:0,
                v.engaged ? 1:0,
                v.hidden ? 1:0
            ];
        }
        return ret;
    },
    decompressEntries = function(entries) {
        var k, v, ret = {};
        for(k in entries) {
            v = entries[k];
            ret[k] = {
                "id": k,
                "removeable": !!v[0],
                "engaged": !!v[1],
                "hidden": !!v[2]
            };
        }
        return ret;
    },
    compressTools = function(entries) {
        var k, v, ret = {};
        for(k in entries) {
            v = entries[k];
            ret[v.gs_id] = [
                v.removeable ? 1:0,
                v.engaged ? 1:0,
                v.hidden ? 1:0,
                k
            ];
        }
        return ret;
    },
    decompressTools = function(entries) {
        var k, v, ret = {}, id;
        for(k in entries) {
            v = entries[k];
            id = v[3];
            ret[id] = {
                "id": id,
                "removeable": !!v[0],
                "engaged": !!v[1],
                "hidden": !!v[2],
                "gs_id": k
            };
        }
        return ret;
    },
    storeStates = function(state) {
        state.entries = compressEntries(state.entries);
        state.tools = compressTools(state.tools);
        cs = storage();
        for(idx in state) {
            cs.add(idx, state[idx]);
        }
        cs.store();
        storePageState(state.any_entry_engaged || state.tools_engaged);
    },
    readStates = function() {
        cs = storage();
        if (("entries" in cs.items) && ("tools" in cs.items)) {
            cs.items.entries = decompressEntries(cs.items.entries);
            cs.items.tools = decompressTools(cs.items.tools);
        }
        return cs.items;
    },

    /**
     * The information wether slates are engaged or not is shared
     * with the page's renderer, so the space can be reserverd very early.
     */
    storePageState = function(engaged) {
        var shared = new il.Utilities.CookieStorage('il_mb_slates');
        shared.add('engaged', engaged);
        shared.store();
    },

    public_interface = {
        read: readStates,
        store: storeStates
    };

    return public_interface;
}

export default persistence;