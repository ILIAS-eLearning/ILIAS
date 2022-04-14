var mainbar = function() {
    var mappings = {},
    external_commands = {
        /**
         * Engage a certain tool or entry
         */
        engageEntry: function(mapping_id) {
            var tool_id = mappings[mapping_id];
            if(Object.keys(this.model.getState().tools).includes(tool_id)) {
                this.model.actions.engageTool(tool_id);
            }
            if(Object.keys(this.model.getState().entries).includes(tool_id)) {
                this.model.actions.engageEntry(tool_id);
            }
            this.renderer.render(this.model.getState());
            this.persistence.store(this.model.getState());
        },
        /**
         * remove a certain tool
         */
        removeTool: function(mapping_id) {
            var tool_id = mappings[mapping_id];
            this.model.actions.removeTool(tool_id);
            this.renderer.render(this.model.getState());
            this.persistence.store(this.model.getState());
        },
        /**
         * Just open the tools, activate last one
         */
        disengageAll: function() {
            this.model.actions.disengageAll();
            this.renderer.render(this.model.getState());
        },
        /*
         * clear all (active) stored states, used in e.g. logout
         */
        clearStates: function() {
            this.model.actions.disengageAll();
            this.persistence.store(this.model.getState());
        }
    },
    construction = {
        /**
         * Add an entry to the model representing a tool.
         * A tool, other than a regular entry, may be removeable by a user
         * or may be invisible at first.
         * This only adds to the model, the html-parts still need to be registered.
         */
        addToolEntry: function (position_id, removeable, hidden, gs_id) {
            this.model.actions.addTool(position_id, removeable, hidden, gs_id);
        },
        /**
         * An entry consists of several visible parts: the button, the slate and
         * the close-button (remover).
         * All these parts are summed up in the model via the position_id;
         * however, when it comes to rendering, the individual parts are needed.
         * The function also adds an entry to the model if there is none already.
         */
        addPartIdAndEntry: function (position_id, part, html_id, is_tool) {
            this.renderer.addEntry(position_id, part, html_id);
            if( !is_tool
                && (position_id in this.model.getState().tools == false)
            ) {
                this.model.actions.addEntry(position_id);
            }
        },
        /**
         * Toplevel entries and tools are being given to the mainbar with an
         * id; this maps the id to the position_id calculated during rendering of
         * the mainbar.
         */
        addMapping: function(mapping_id, position_id) {
            if(! (mapping_id in Object.keys(mappings))) {
                mappings[mapping_id] = position_id;
            }
        },
        /**
         * Register signals. Signals will have an id (=position_id) and an action
         * in their options,
         */
        addTriggerSignal: function(signal) {
            $(document).on(signal, function(event, signalData) {
                var id = signalData.options.entry_id,
                    action = signalData.options.action,
                    mb = il.UI.maincontrols.mainbar,
                    state,
                    after_render;

                switch(action) {
                    case 'trigger_mapped':
                        id = mappings[id]; //no break afterwards!

                    case 'trigger':
                        state = mb.model.getState();
                        if(id in state.tools) {
                            mb.model.actions.engageTool(id);
                            after_render = function() {
                                mb.renderer.focusSubentry(id);
                            };
                        }
                        if(id in state.entries) { //toggle
                            if(state.entries[id].engaged) {
                                mb.model.actions.disengageEntry(id);
                            } else {
                                mb.model.actions.engageEntry(id);

                                if(state.entries[id].isTopLevel()) {
                                    after_render = function() {
                                        mb.renderer.focusSubentry(id);
                                    };
                                }
                            }
                        }
                        break;

                    case 'remove':
                        mb.model.actions.removeTool(id);
                        break;

                    case 'disengage_all':
                        mb.model.actions.disengageAll();
                        var state = mb.model.getState();
                            last_top_id = state.last_active_top;

                        after_render = function() {
                            mb.renderer.focusTopentry(last_top_id);
                        };

                        state.last_active_top = null;
                        mb.model.setState(state);
                        break;

                    case 'toggle_tools':
                        mb.model.actions.toggleTools();

                        var state = mb.model.getState();
                            id = Object.keys(state.tools)[0];

                        if(state.tools_engaged) {
                            after_render = function() {
                                for(idx in state.tools) {
                                    var tool = state.tools[idx];
                                    if(tool.engaged) {
                                        id = tool.id;
                                    }
                                }
                                mb.renderer.focusTopentry(id);
                            };
                        }
                        break;
                }

                mb.renderer.render(mb.model.getState());
                if(after_render) {
                    after_render();
                }
                mb.persistence.store(mb.model.getState());
                mb.renderer.dispatchResizeNotification();
            });
        }
    },
    helper = {
        findToolByGSId: function (tools, gs_id) {
            for(var idx in tools) {
                if(tools[idx].gs_id === gs_id) {
                    return tools[idx];
                }
            }
            return null;
        },
        getFirstEngagedToolId: function(tools) {
            var keys = Object.keys(tools);
            for(var idx in keys) {
                if(tools[keys[idx]].engaged) {
                    return keys[idx];
                }
            }
            return false;
        }
    },
    adjustToScreenSize = function(event) {
         if(event.detail && event.detail.mainbar_induced) {
            return;
        }
        var mb = il.UI.maincontrols.mainbar,
            amount = mb.renderer.calcAmountOfButtons();

        if(il.UI.page.isSmallScreen()) {
            mb.model.actions.disengageAll();
        }
        mb.model.actions.initMoreButton(amount);
        mb.renderer.render(mb.model.getState());
    },
    init_desktop = function(initially_active) {
        var mb = il.UI.maincontrols.mainbar,
            cookie_state = mb.persistence.read(),
            init_state = mb.model.getState();
        /**
         * apply cookie-state;
         * tools appear and disappear by context and
         * global screen modifications - take them from there,
         * but apply engaged states
         */
        if(Object.keys(cookie_state).length > 0) {
            //re-apply engaged
            for(var idx in init_state.tools) {
                gs_id = init_state.tools[idx].gs_id;
                if(cookie_state.known_tools.indexOf(gs_id) === -1) {
                    cookie_state.known_tools.push(gs_id);
                    if(!init_state.tools[idx].hidden) {
                        init_state.tools[idx].engaged = true; //new tool is active
                    }
                } else {
                    stored = helper.findToolByGSId(cookie_state.tools, gs_id);
                    if(stored) {
                        init_state.tools[idx].engaged = stored.engaged;
                        init_state.tools[idx].hidden = stored.hidden;
                    }
                }
            }

            cookie_state.tools = init_state.tools;
            mb.model.setState(cookie_state);
        }

        init_state = mb.model.getState();
        /**
         * initially active (from mainbar-component) will override everything (but tools)
         */
        if(initially_active) {
            if(initially_active === '_none') {
                mb.model.actions.disengageAll();
            } else if(init_state.entries[mappings[initially_active]]) {
                mb.model.actions.engageEntry(mappings[initially_active]);
            } else if(init_state.tools[mappings[initially_active]]) {
                mb.model.actions.engageTool(mappings[initially_active]);
            }
        }

        /**
         * Override potentially active entry, if there are is an active tool.
         */
        first_tool_id = helper.getFirstEngagedToolId(init_state.tools);
        if(first_tool_id) {
            mb.model.actions.engageTool(first_tool_id);
        } else {
            //tools engaged, but none active: take the first one:
            var any_engaged = mb.model.getState().any_tools_engaged(),
                any_visible = mb.model.getState().any_tools_visible();

            if(any_engaged && any_visible) {
                tool_id = Object.keys(init_state.tools).shift();
                mb.model.actions.engageTool(tool_id);
            } else {
                mb.model.actions.disengageTools();
            }

            if( any_engaged === false &&
                any_visible === false &&
                mb.model.getState().any_entry_engaged === false
            ) {
                mb.model.actions.disengageAll();
            } else {
                last_top = mb.model.getState().last_active_top;
                if(last_top) {
                    mb.model.actions.engageEntry(last_top);
                }else {
                    mb.model.actions.disengageAll();
                }
            }
        }
        mb.model.actions.initMoreButton(mb.renderer.calcAmountOfButtons());
        mb.renderer.render(mb.model.getState());
    },
    init_mobile = function() {
        var mb = il.UI.maincontrols.mainbar;
        mb.model.actions.disengageAll();
        mb.model.actions.initMoreButton(mb.renderer.calcAmountOfButtons());
        mb.renderer.render(mb.model.getState());
    },
    init = function(initially_active) {
        if(il.UI.page.isSmallScreen()) {
            init_mobile();
        } else {
            init_desktop(initially_active);
        }
    },

    public_interface = {
        addToolEntry: construction.addToolEntry,
        addPartIdAndEntry: construction.addPartIdAndEntry,
        addMapping: construction.addMapping,
        addTriggerSignal: construction.addTriggerSignal,
        adjustToScreenSize: adjustToScreenSize,
        init: init,
        engageTool: external_commands.engageEntry, //for legacy reasons, please use engageEntry
        engageEntry: external_commands.engageEntry,
        removeTool: external_commands.removeTool,
        disengageAll: external_commands.disengageAll,
        clearStates: external_commands.clearStates
    };

    return public_interface;
};

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
        },
        isInView : function (entry_id) {
            if(!state.entries[entry_id]) { //tools
                return true;
            }
            var hops = entry_id.split(':'),
                entries = state.entries;
            while (hops.length > 1) {
                entry_id = hops.join(':');
                if(!state.entries[entry_id].engaged) {
                    return false;
                }
                hops.pop();
            }
            return true;
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
            state = reducers.bar.disengageTools(state);
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
            state.entries = reducers.entries.disengageTopLevel(state.entries);
            state.tools = reducers.entries.disengageTopLevel(state.tools);
            state = reducers.bar.noSlates(state);
            state = reducers.bar.disengageTools(state);

        },
        initMoreButton: function(max_buttons) {
            var entry_ids = Object.keys(state.entries),
                last_entry_id = entry_ids[entry_ids.length - 1],
                more = state.entries[last_entry_id];

            if(state.any_tools_visible()) {
                max_buttons--;
            }
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
        getTopLevelEntries: helpers.getTopLevelEntries,
        isInView: helpers.isInView
    },
    init = function() {
        state = factories.cloned(classes.bar);
    };

    init();
    return public_interface;
};

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
};

var renderer = function($) {
    var css = {
        engaged: 'engaged'
        ,disengaged: 'disengaged'
        ,hidden: 'hidden'
        ,page_div: 'il-layout-page'
        ,page_has_engaged_slated: 'with-mainbar-slates-engaged'
        ,tools_btn: 'il-mainbar-tools-button'
        ,toolentries_wrapper: 'il-mainbar-tools-entries'
        ,remover_class: 'il-mainbar-remove-tool'
        ,mainbar: 'il-mainbar'
        ,mainbar_buttons: '.il-mainbar .il-mainbar-entries .btn-bulky, .il-mainbar .il-mainbar-entries .link-bulky'
        ,mainbar_entries: 'il-mainbar-entries'
    },

    dom_references = {},
    dom_ref_to_element = {},
    thrown_for = {},
    dom_element = {
        withHtmlId: function (html_id) {
            return Object.assign({}, this, {html_id: html_id});
        },
        getElement: function(){
            //return document.getElementById(this.html_id);
            return $('#' + this.html_id);
        },
        engage: function() {
            var element = this.getElement();

            element.addClass(css.engaged);
            element.removeClass(css.disengaged);

            if(il.UI.page.isSmallScreen() && il.UI.maincontrols.metabar) {
                il.UI.maincontrols.metabar.disengageAll();
            }
            this.additional_engage();
        },
        disengage: function() {
            this.getElement().addClass(css.disengaged);
            this.getElement().removeClass(css.engaged);
            this.additional_disengage();
        },
        mb_hide: function(on_parent) {
            var element = this.getElement();
            if(on_parent) {
                element = element.parent();
            }
            element.addClass(css.hidden);
        },
        mb_show: function(on_parent) {
            var element = this.getElement();
            if(on_parent) {
                element = element.parent();
            }
            element.removeClass(css.hidden);
        },
        additional_engage: function(){},
        additional_disengage: function(){}
    },
    parts = {
        triggerer: Object.assign({}, dom_element, {
            remove: function() {},
            additional_engage: function(){
                this.getElement().attr('aria-expanded', true);
            },
            additional_disengage: function(){
                this.getElement().attr('aria-expanded', false);
            }
        }),
        slate: Object.assign({}, dom_element, {
            remove: null,
            mb_hide: null,
            mb_show: null,
            additional_engage: function(){
                var element = this.getElement(),
                    entry_id = dom_ref_to_element[this.html_id],
                    isInView = il.UI.maincontrols.mainbar.model.isInView(entry_id),
                    thrown = thrown_for[entry_id];

                element.attr('aria-hidden', false);
                //https://www.w3.org/TR/wai-aria-practices-1.1/examples/accordion/accordion.html
                element.attr('role', 'region');
                if(isInView && !thrown) {
                    element.trigger('in_view'); //this is most important for async loading of slates,
                                                //it triggers the GlobalScreen-Service.
                    thrown_for[entry_id] = true;
                }
                if(!isInView) {
                    thrown_for[entry_id] = false;
                }
            },
            additional_disengage: function(){
                var entry_id = dom_ref_to_element[this.html_id];
                thrown_for[entry_id] = false;
                this.getElement().attr('aria-hidden', true);
                this.getElement().removeAttr('role', 'region');
            }
        }),
        remover: Object.assign({}, dom_element, {
            engage: null,
            disengage:null,
            mb_show: function(){this.getElement().parent().show();}
        }),
        page: {
            getElement: function(){
                return $('.' + css.page_div);
            },
            slatesEngaged: function(engaged) {
                if(engaged) {
                    this.getElement().addClass(css.page_has_engaged_slated);
                } else {
                    this.getElement().removeClass(css.page_has_engaged_slated);
                }
            }
        },
        removers: {
            getElement: function(){
                return $('.' + css.remover_class);
            },
            mb_hide: function() {
                this.getElement().hide();
            }

        },
        tools_area: Object.assign({}, dom_element, {
            getElement: function(){
                return $(' .' + css.toolentries_wrapper);
            }
        }),
        tools_button: Object.assign({}, dom_element, {
            getElement: function(){
                return $('.' + css.tools_btn + ' .btn');
            },
            remove: null,
            additional_engage: function(){
                this.getElement().attr('aria-expanded', true);
            },
            additional_disengage: function(){
                this.getElement().attr('aria-expanded', false);
            }
        }),
        mainbar: {
            getElement: function(){
                return $('.' + css.mainbar);
            },
            getOffsetTop: function() {
                return this.getElement().offset().top;
            }
        }
    },

    //more-slate
    more = {
        calcAmountOfButtons: function() {

            var window_height = $(window).height(),
                window_width = $(window).width(),
                horizontal = il.UI.page.isSmallScreen(),
                btn = $(css.mainbar_buttons).first();
                btn_height = btn.height(),
                btn_width = btn.width(),
                amount_buttons = Math.floor(
                    (window_height - parts.mainbar.getOffsetTop()) / btn_height
                );

            if(horizontal) {
                amount_buttons = Math.floor(window_width / btn_width);
            }
            return amount_buttons;
        }
    },

    actions = {
        addEntry: function (entry_id, part, html_id) {
            dom_references[entry_id] = dom_references[entry_id] || {};
            dom_references[entry_id][part] = html_id;
            dom_ref_to_element[html_id] = entry_id;
            thrown_for[entry_id] = false;
        },
        renderEntry: function (entry, is_tool) {
            if(!dom_references[entry.id]){
                return;
            }

            var triggerer = parts.triggerer.withHtmlId(dom_references[entry.id].triggerer),
                slate = parts.slate.withHtmlId(dom_references[entry.id].slate);
                
                //a11y
                triggerer.getElement().attr('aria-controls', slate.html_id);
                triggerer.getElement().attr('aria-labelledby', triggerer.html_id);
                //a11y

            if(entry.hidden) {
                triggerer.mb_hide(is_tool);
            } else {
                triggerer.mb_show(is_tool);
            }

            if(entry.engaged) {
                triggerer.engage();
                slate.engage();
                if(entry.removeable) {
                    remover = parts.remover.withHtmlId(dom_references[entry.id].remover);
                    remover.mb_show(true);
                }
            } else {
                triggerer.disengage();
                slate.disengage();
            }
        },

        moveToplevelTriggerersToMore: function (model_state) {
            var entry_ids = Object.keys(model_state.entries),
                last_entry_id = entry_ids[entry_ids.length - 1],
                more_entry = model_state.entries[last_entry_id],
                more_slate = parts.slate.withHtmlId(dom_references[more_entry.id].slate),
                root_entries = il.UI.maincontrols.mainbar.model.getTopLevelEntries(),
                root_entries_length = root_entries.length - 1,
                max_buttons = more.calcAmountOfButtons() - 1; //room for the more-button

            if(model_state.any_tools_visible()) { max_buttons--;}
            for(i = max_buttons; i < root_entries_length; i++) {
                btn = parts.triggerer.withHtmlId(dom_references[root_entries[i].id].triggerer);
                list = btn.getElement().parent();
                btn.getElement().appendTo(more_slate.getElement().children('.il-maincontrols-slate-content'));
                list.remove();
            }
        },
        render: function (model_state) {
            var entry_ids = Object.keys(model_state.entries),
                last_entry_id = entry_ids[entry_ids.length - 1],
                more_entry = model_state.entries[last_entry_id],
                more_button = parts.triggerer.withHtmlId(dom_references[more_entry.id].triggerer),
                more_slate = parts.slate.withHtmlId(dom_references[more_entry.id].slate);
                //reset
                btns = more_slate.getElement().find('.btn-bulky, .link-bulky');
                for(var i = 0; i < btns.length; i = i + 1) {
                    li = document.createElement('li');
                    li.appendChild(btns[i]);
                    li.setAttribute('role', 'none');
                    $(li).insertBefore(more_button.getElement().parent());
                }

            if(model_state.more_available) {
                more_button.getElement().parent().show();
                actions.moveToplevelTriggerersToMore(model_state);
            } else {
                more_button.getElement().parent().hide();
            }

            parts.page.slatesEngaged(model_state.any_entry_engaged || model_state.tools_engaged);

            if(model_state.any_tools_visible()) {
                parts.tools_button.mb_show();
            } else {
                parts.tools_button.mb_hide();
            }

            if(model_state.tools_engaged){
                parts.tools_button.engage();
                parts.tools_area.engage();
            } else {
                parts.tools_button.disengage();
                parts.tools_area.disengage();
            }

            for(idx in model_state.entries) {
                actions.renderEntry(model_state.entries[idx], false);
            }
            for(idx in model_state.tools) {
                actions.renderEntry(model_state.tools[idx], true);
            }
            //unfortunately, this does not work properly via a class
            $('.' + css.mainbar_entries).css('visibility', 'visible');
        },
        focusSubentry: function(triggered_entry_id) {
            var dom_id = dom_references[triggered_entry_id],
                someting_to_focus_on = $('#' + dom_id.slate)
                    .children().first()
                    .children().first();
            if(someting_to_focus_on[0]){
                if(!someting_to_focus_on.attr('tabindex')) { //cannot focus w/o index
                    someting_to_focus_on.attr('tabindex', '-1');
                }
                someting_to_focus_on[0].focus();
            }
        },
        focusTopentry: function(top_entry_id) {
            var  triggerer = dom_references[top_entry_id];
            document.getElementById(triggerer.triggerer).focus();
        },

        dispatchResizeNotification: function(top_entry_id) {
            var event = new CustomEvent(
                'resize',
                {detail : {mainbar_induced : true}}
            );
            window.dispatchEvent(event);
        }
    },
    public_interface = {
        addEntry: actions.addEntry,
        calcAmountOfButtons: more.calcAmountOfButtons,
        render: actions.render,
        focusSubentry: actions.focusSubentry,
        focusTopentry: actions.focusTopentry,
        dispatchResizeNotification: actions.dispatchResizeNotification
    };

    return public_interface;
};

il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

il.UI.maincontrols.mainbar = mainbar();
il.UI.maincontrols.mainbar.model = model();
il.UI.maincontrols.mainbar.persistence = persistence();
il.UI.maincontrols.mainbar.renderer = renderer($);
