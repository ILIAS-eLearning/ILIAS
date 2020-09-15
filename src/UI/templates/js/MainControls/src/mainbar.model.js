/**
 * The Mainbar holds a collection of entries that each consist of some triggerer
 * and an according slate; in case of Tools, these entries might be hidden at first
 * or may be removed by the users.
 * There is a redux-like model of the moving parts of the mainbar: All entries and tools
 * are stored in a state.
 * Whenever something changes, i.e. the engagement and thus visibility of elements
 * should change, these changes are applied to the model first, so that calculations
 * of dependencies can be done _before_ rendering.
  */

var model = function() {
    var state,
    classes = {
        bar: {
            any_entry_engaged : false,
            tools_engaged: false,
            more_available: false,
            any_tools_visible: function() {
                for(idx in this.tools) {
                    if(!this.tools[idx].hidden) {
                        return true;
                    }
                }
                return false;
            },
            any_tools_engaged: function() {
                for(idx in this.tools) {
                    if(this.tools[idx].engaged) {
                        return true;
                    }
                }
                return false;
            },

            entries: {},
            tools: {}, //"moving" parts, current tools
            known_tools: [], //gs-ids; a tool is "new", if not listed here
            last_active_top: null
        },
        entry: {
            id: null,
            removeable: false,
            engaged: false,
            hidden: false,
            gs_id: null,
            isTopLevel: function() {return this.id.split(':').length === 2;}
        }
    },
    factories = {
        entry: (id) => factories.cloned(classes.entry, {id: id}),
        cloned: (state, params) => Object.assign({}, state, params),
        state: function(nu_state) {
            var tmp_state = factories.cloned(state, nu_state);
            for(idx in tmp_state.entries) {
                tmp_state.entries[idx].isTopLevel = classes.entry.isTopLevel;
            }
            for(idx in tmp_state.tools) {
                tmp_state.tools[idx].isTopLevel = classes.entry.isTopLevel;
            }
            state = tmp_state;
        }
    },
    reducers = {
        entry: {
            engage: (entry) => {
                entry.engaged = true;
                entry.hidden = false;
                return entry;
            },
            disengage: (entry) => {entry.engaged = false; return entry;},
            mb_show: (entry) => {entry.hidden = false; return entry;},
            mb_hide: (entry) => {
                entry.hidden = true;
                entry.engaged = false;
                return entry;
            }
        },
        bar:  {
            engageTools: (bar) => {bar.tools_engaged = true; return bar;},
            disengageTools: (bar) => {bar.tools_engaged = false; return bar;},
            anySlates: (bar) => {bar.any_entry_engaged = true; return bar;},
            noSlates: (bar) => {bar.any_entry_engaged = false; return bar;},
            withMoreButton: (bar) => {bar.more_available = true; return bar;},
            withoutMoreButton: (bar) => {bar.more_available = false; return bar;}
        },
        entries: {
            disengageTopLevel: function(entries) {
                var id;
                for(id in entries) {
                    if(entries[id].isTopLevel()) {
                        entries[id] = reducers.entry.disengage(entries[id]);
                    }
                }
                return entries;
            },
            engageEntryPath: function(entries, entry_id) {
                var hops = entry_id.split(':');
                hops.map(function(v, idx, hops) {
                    var id = hops.slice(0, idx+1).join(':');
                    if(id && id != '0') {
                        entries[id] = reducers.entry.engage(entries[id]);
                    }
                });
                return entries;
            }
        }
    },
    helpers = {
        getTopLevelEntries: function() {
            var id,
                ret = [];
            for(id in state.entries) {
                if(state.entries[id].isTopLevel()) {
                    ret.push(state.entries[id]);
                }
            }
            return ret;
        },
        getEngagedTopLevelEntryId: function() {
            var id,
                entries = helpers.getTopLevelEntries();
            for(id in entries) {
                if(entries[id].engaged) {
                    return entries[id].id;
                }
            }
            return null;
        }
    },
    actions = {
        addEntry: function (entry_id) {
            state.entries[entry_id] = factories.entry(entry_id);
        },
        addTool: function (entry_id, removeable, hidden, gs_id) {
            var tool = factories.entry(entry_id);
            tool.removeable = removeable ? true : false;
            tool.hidden = hidden ? true : false;
            tool.gs_id = gs_id;
            state.tools[entry_id] = tool;
        },
        engageEntry: function (entry_id) {
            state.tools = reducers.entries.disengageTopLevel(state.tools);
            state.entries = reducers.entries.disengageTopLevel(state.entries);
            state.entries = reducers.entries.engageEntryPath(state.entries, entry_id);
            state = reducers.bar.disengageTools(state);
            state = reducers.bar.anySlates(state);
            state.last_active_top = helpers.getEngagedTopLevelEntryId();
        },
        disengageEntry: function (entry_id) {
            state.entries[entry_id] = reducers.entry.disengage(state.entries[entry_id]);
            if(state.entries[entry_id].isTopLevel()) {
                state = reducers.bar.noSlates(state);
            }
            state.last_active_top = helpers.getEngagedTopLevelEntryId();
        },
        hideEntry: function (entry_id) {
            state.entries[entry_id] = reducers.entry.mb_hide(state.entries[entry_id]);
        },
        showEntry: function (entry_id) {
            state.entries[entry_id] = reducers.entry.mb_show(state.entries[entry_id]);
        },
        engageTool: function (entry_id) {
            state.entries = reducers.entries.disengageTopLevel(state.entries);
            state.tools = reducers.entries.disengageTopLevel(state.tools);
            state.tools[entry_id] = reducers.entry.engage(state.tools[entry_id]);
            state = reducers.bar.engageTools(state);
            state = reducers.bar.anySlates(state);
        },
        engageTools: function() {
            state = reducers.bar.engageTools(state);
        },
        disengageTools: function() {
            state = reducers.bar.disengageTools(state)
        },
        removeTool: function (entry_id) {
            state.tools[entry_id] = reducers.entry.mb_hide(state.tools[entry_id]);
            for(idx in state.tools) {
                tool = state.tools[idx];
                if(!tool.hidden) {
                    state.tools[tool.id] = reducers.entry.engage(tool);
                    break;
                }
            }
            if(!state.any_tools_visible()) {
                actions.disengageTools();
                last_top = state.last_active_top;
                if(last_top) {
                    actions.engageEntry(last_top);
                }else {
                    actions.disengageAll();
                }
            }
        },
        toggleTools: function() {
            if(state.tools_engaged) {
                actions.disengageAll();
            } else {
                for(idx in state.tools) {
                    var tool = state.tools[idx];
                    if(tool.engaged) {
                        actions.engageTool(tool.id);
                        return;
                    }
                }
                var tool_id = Object.keys(state.tools)[0];
                actions.engageTool(tool_id);
            }
        },
        disengageAll: function () {
            state.entries = reducers.entries.disengageTopLevel(state.entries)
            state.tools = reducers.entries.disengageTopLevel(state.tools)
            state = reducers.bar.noSlates(state);
            state = reducers.bar.disengageTools(state);

        },
        initMoreButton: function(max_buttons) {
            var entry_ids = Object.keys(state.entries),
                last_entry_id = entry_ids[entry_ids.length - 1],
                more = state.entries[last_entry_id];

            if(state.any_tools_visible()) {
                max_buttons--
            };

            //get length of top-level entries (w/o) more-button
            amount_toplevel = helpers.getTopLevelEntries().length - 1;

            if(amount_toplevel > max_buttons) {
                state.entries[more.id] = reducers.entry.mb_show(more);
                state = reducers.bar.withMoreButton(state);
            } else {
                state.entries[more.id] = reducers.entry.mb_hide(more);
                state = reducers.bar.withoutMoreButton(state);
            }
        }
    },
    public_interface = {
        actions: actions,
        getState: () => factories.cloned(state),
        setState: factories.state,
        getTopLevelEntries: helpers.getTopLevelEntries
    },
    init = function() {
        state = factories.cloned(classes.bar);
    };

    init();
    return public_interface;
}

export default model;