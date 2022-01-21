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
                                    }
                                }
                            }
                        }
                        break;

                    case 'remove':
                        mb.model.actions.removeTool(id);
                        break;

                    case 'disengage_all':
                        mb.model.actions.disengageAll();
                        var state = mb.model.getState()
                            last_top_id = state.last_active_top;

                        after_render = function() {
                            mb.renderer.focusTopentry(last_top_id);
                        }

                        state.last_active_top = null;
                        mb.model.setState(state);
                        break;

                    case 'toggle_tools':
                        mb.model.actions.toggleTools();

                        var state = mb.model.getState()
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
                            }
                        }
                        break;
                }

                mb.renderer.render(mb.model.getState());
                if(after_render) {
                    after_render();
                }
                mb.persistence.store(mb.model.getState());
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
    adjustToScreenSize = function() {
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
                        init_state.tools[idx].engaged = true //new tool is active
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

export default mainbar;