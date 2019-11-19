il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

//TODO: remember last tool
//TODO: close tools after removing all tools
//TODO: initially engaged

//TODO: mobile ->deal with metabar as well (one active slate only)
//TODO: mobile ->horizontal more
//TODO: mobile ->horizontal counter issues

//TODO: remove comments and log

(function($, maincontrols) {
	maincontrols.mainbar = (function($) {
		var
		mappings = {},
		addToolEntry = function (position_id, removeable=true, hidden=false) {
			this.model.actions.addTool(position_id, removeable, hidden);
		},
		addPartIdAndEntry = function (position_id, part, html_id, is_tool=false) {
			this.renderer.addEntry(position_id, part, html_id);
			if( !is_tool
				&& (position_id in this.model.getState().tools == false)
			) {
				this.model.actions.addEntry(position_id);
			}
		},
		addMapping = function(name, position_id) {
			mappings[name] = position_id;
		},
		addTriggerSignal = function(signal) {
			$(document).on(signal, function(event, signalData) {
				var id = signalData.options.entry_id,
					action = signalData.options.action,
					mb = il.UI.maincontrols.mainbar;

				switch(action) {
					case 'trigger_mapped':
						id = mappings[id]; //no break afterwards!
					case 'trigger':
						var state = mb.model.getState();
						if(id in state.tools) {
							mb.model.actions.engageTool(id)
						}
						if(id in state.entries) { //toggle
							if(state.entries[id].engaged) {
								mb.model.actions.disengageEntry(id)
							} else {
								mb.model.actions.engageEntry(id)
							}
						}
						break;
					case 'remove':
						mb.model.actions.removeTool(id);
						break;
					case 'disengage_all':
						mb.model.actions.disengageAll();
						break;
					case 'toggle_tools':
						mb.model.actions.toggleTools();
						break;

				}

				mb.renderer.render(mb.model.getState());
				mb.persistence.store(mb.model.getState());
			});
		},
		adjustToScreenSize = function() {
			var mb = il.UI.maincontrols.mainbar,
				amount = mb.renderer.calcAmountOfButtons();

			mb.model.actions.initMoreButton(amount);
			mb.renderer.render(mb.model.getState());
		},

		init = function() {
			var mb = il.UI.maincontrols.mainbar,
				cookie_state = mb.persistence.read();

			mb.model.setState(cookie_state);
			mb.model.actions.initMoreButton(mb.renderer.calcAmountOfButtons());
			mb.renderer.render(mb.model.getState());
		},

		public_interface = {
			addToolEntry: addToolEntry,
			addPartIdAndEntry: addPartIdAndEntry,
			addMapping: addMapping,
			addTriggerSignal: addTriggerSignal,
			adjustToScreenSize: adjustToScreenSize,
			init: init
		};

		return public_interface;
	})($);
})($, il.UI.maincontrols);


/**
 * The Mainbar holds a collection of entries that each consist of some triggerer
 * and an according slate; in case of Tools, these entries might be hidden at first
 * or may be removed by the users.
 * The usage of combined slates leads to nested submenus and ultimately a tree-structure.
 *
 * First of all, there is a redux-like model of the moving parts of the mainbar:
 * All entries and tools (which are enhanced entries) are stored in a state.
 * Whenever something changes, i.e. the engagement and thus visibility of elements
 * should change, these changes are applied to the model first, so that calculations
 * of dependencies can be done _before_ rendering.
  */

(function($, mainbar) {
	mainbar.model = (function($) {
		var
		state,
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
				tools: {},
				entries: {}
			},
			entry: {
				id: null,
				removeable: false,
				engaged: false,
				hidden: false,
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
				var ret = [];
				for(id in state.entries) {
					if(state.entries[id].isTopLevel()) {
						ret.push(state.entries[id]);
					}
				}
				return ret;
			}
		},
		actions = {
			addEntry: function (entry_id) {
				state.entries[entry_id] = factories.entry(entry_id);
			},
			addTool: function (entry_id, removeable, hidden) {
				var tool = factories.entry(entry_id);
				tool.removeable = removeable ? true : false;
				tool.hidden = hidden ? true : false;
				state.tools[entry_id] = tool;
			},
			engageEntry: function (entry_id) {
				state.tools = reducers.entries.disengageTopLevel(state.tools),
				state.entries = reducers.entries.disengageTopLevel(state.entries),
				state.entries = reducers.entries.engageEntryPath(state.entries, entry_id);
				state = reducers.bar.disengageTools(state);
				state = reducers.bar.anySlates(state);

			},
			disengageEntry: function (entry_id) {
				state.entries[entry_id] = reducers.entry.disengage(state.entries[entry_id]);
				if(state.entries[entry_id].isTopLevel()) {
					state = reducers.bar.noSlates(state);
				}
			},
			hideEntry: function (entry_id) {
				state.entries[entry_id] = reducers.entry.mb_hide(state.entries[entry_id]);
			},
			showEntry: function (entry_id) {
				state.entries[entry_id] = reducers.entry.mb_show(state.entries[entry_id]);
			},
			engageTool: function (entry_id) {
				state.entries = reducers.entries.disengageTopLevel(state.entries)
				state.tools = reducers.entries.disengageTopLevel(state.tools)
				state.tools[entry_id] = reducers.entry.engage(state.tools[entry_id]);
				state = reducers.bar.engageTools(state);
				state = reducers.bar.anySlates(state);
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
			},
			toggleTools: function() {
				if(state.tools_engaged) {
					state.entries = reducers.entries.disengageTopLevel(state.entries)
					state = reducers.bar.disengageTools(state);
					state = reducers.bar.noSlates(state);
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
				//if(isNaN(max_buttons)) { return; }
				var entry_ids = Object.keys(state.entries),
					last_entry_id = entry_ids[entry_ids.length - 1],
					more = state.entries[last_entry_id];

				if(state.any_tools_visible()) { max_buttons--};

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
	})($);
})($, il.UI.maincontrols.mainbar);


(function($, mainbar) {
	mainbar.persistence = (function($) {
		var
		cs,
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
		storeStates = function(state) {
			cs = storage();
			for(idx in state) {
				cs.add(idx, state[idx]);
			}
			cs.store();
		},
		readStates = function() {
			cs = storage();
			return cs.items;
		},


		public_interface = {
			read: readStates,
			store: storeStates
		};
		return public_interface;
	})($);
})($, il.UI.maincontrols.mainbar);



(function($, mainbar) {
	mainbar.renderer = (function($) {
		var
		css = {
			engaged: 'engaged'
			,disengaged: 'disengaged'
			,hidden: 'hidden'
			,page_div: 'il-layout-page'
			,page_has_engaged_slated: 'with-mainbar-slates-engaged'
			,tools_btn: 'il-mainbar-tools-button'
			,toolentries_wrapper: 'il-mainbar-tools-entries'
			,remover_class: 'il-mainbar-remove-tool'
			,mainbar: 'il-mainbar'
		},

		dom_references = {},
		dom_element = {
			withHtmlId: function (html_id) {
				return Object.assign({}, this, {html_id: html_id}); //is there a deassign?
			},
			getElement: function(){
				return $('#' + this.html_id);
			},
			engage: function() {
				this.getElement().addClass(css.engaged);
				this.getElement().removeClass(css.disengaged);
			},
			disengage: function() {
				this.getElement().addClass(css.disengaged);
				this.getElement().removeClass(css.engaged);
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
			}
		},
		parts = {
			triggerer: Object.assign({}, dom_element, {
				remove: function() {}
			}),
			slate: Object.assign({}, dom_element, {
				remove: null,
				mb_hide: null,
				mb_show: null
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
				remove: null
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
				var
					window_height = $(window).height(),
					window_width = $(window).width(),
					horizontal = il.UI.page.isSmallS
					btn_height = parts.tools_button.getElement().height(),
					btn_width = parts.tools_button.getElement().width(),

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
			},

			renderEntry: function (entry, is_tool) {
				var	triggerer = parts.triggerer.withHtmlId(dom_references[entry.id].triggerer),
					slate = parts.slate.withHtmlId(dom_references[entry.id].slate);

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
				//TODO: this coudl be nicer...
				var entry_ids = Object.keys(model_state.entries),
					last_entry_id = entry_ids[entry_ids.length - 1],
					more_entry = model_state.entries[last_entry_id],
					more_button = parts.triggerer.withHtmlId(dom_references[more_entry.id].triggerer)
					more_slate = parts.slate.withHtmlId(dom_references[more_entry.id].slate),

					root_entries = il.UI.maincontrols.mainbar.model.getTopLevelEntries(),
					root_entries_length = root_entries.length - 1,
					max_buttons = more.calcAmountOfButtons() - 1; //room for the more-button

					if(model_state.any_tools_visible()) { max_buttons--};

					for(i = max_buttons; i < root_entries_length; i++) {
						btn = parts.triggerer.withHtmlId(dom_references[root_entries[i].id].triggerer);
						btn.getElement().appendTo(more_slate.getElement().children('.il-maincontrols-slate-content'));
					}
			},
			render: function (model_state) {

				parts.page.slatesEngaged(model_state.any_entry_engaged || model_state.tools_engaged);

				//TODO: move to parts
					//reset more:
					var entry_ids = Object.keys(model_state.entries),
					last_entry_id = entry_ids[entry_ids.length - 1],
					more_entry = model_state.entries[last_entry_id],
					more_button = parts.triggerer.withHtmlId(dom_references[more_entry.id].triggerer)
					more_slate = parts.slate.withHtmlId(dom_references[more_entry.id].slate),

					more_slate.getElement().find('.btn-bulky').insertBefore(more_button.getElement());


				if(model_state.more_available) {
					actions.moveToplevelTriggerersToMore(model_state);
				}

				if(model_state.tools_engaged){
					parts.tools_button.engage();
					parts.tools_area.engage();
					parts.removers.mb_hide();
				} else {
					if(model_state.any_tools_visible()) {
						parts.tools_button.mb_show(true); //hide on parent...
						parts.tools_button.disengage();
						parts.tools_area.disengage();
					} else {
						parts.tools_button.mb_hide();
					}
				}

				for(idx in model_state.entries) {
					actions.renderEntry(model_state.entries[idx], false);
				}
				for(idx in model_state.tools) {
					actions.renderEntry(model_state.tools[idx], true);
				}
			}
		},
		public_interface = {
			addEntry: actions.addEntry,
			calcAmountOfButtons: more.calcAmountOfButtons,
			render: actions.render
		};
		return public_interface;
	})($);
})($, il.UI.maincontrols.mainbar);
