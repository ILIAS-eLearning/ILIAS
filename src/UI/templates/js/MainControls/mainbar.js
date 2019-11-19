il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};
il.UI.maincontrols.mainbar2 = il.UI.maincontrols.mainbar2 || {};

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
				tools: {},
				entries: {}
			},
			entry: {
				id: null,
				removeable: false,
				engaged: false,
				hidden: false,
				isTopLevel: () => this.id.split(':').length === 2
			}
		},

		factories = {
			entry: (id) => factories.cloned(classes.entry, {id: id}),
			cloned: (state, params) => Object.assign({}, state, params)
		},

		reducers = {
			entry: {
				engage: (entry) => {entry.engaged = true; return entry;},
				disengage: (entry) => {entry.engaged = false; return entry;},
				unhide: (entry) => {entry.hidden = false; return entry;}
			},
			bar:  {
				engageTools: (bar) => {bar.tools_engaged = true; return bar;},
				disengageTools: (bar) => {bar.tools_engaged = false; return bar;},
				anySlates: (bar) => {bar.any_entry_engaged = true; return bar;},
				noSlates: (bar) => {bar.any_entry_engaged = false; return bar;}
			},
			entries: {
				disengageTopLevel: function(entries) {
					for(id in entries) {
						entries[id] = reducers.entry.disengage(entries[id]);
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

		actions = {
			addEntry: function (entry_id) {
				state.entries[entry_id] = factories.entry(entry_id);
			},
			addTool: function (entry_id, removeable, hidden) {
				var tool = factories.entry(entry_id);
				tool.removeable = removeable ? removeable : false;
				tool.hidden = hidden ? hidden : false;
				state.tools[entry_id] = tool;
			},
			engageEntry: function (entry_id) {
				state.entries = reducers.entries.disengageTopLevel(state.entries),
				state.entries = reducers.entries.engageEntryPath(state.entries, entry_id);
				state = reducers.bar.anySlates(state);
			},
			engageTool: function (entry_id) {
				state.entries = reducers.entries.disengageTopLevel(state.entries)
				state.tools = reducers.entries.disengageTopLevel(state.tools)
				state.tools[entry_id] = reducers.entry.engage(state.tools[entry_id]);
				state = reducers.bar.anySlates(state);
				state = reducers.bar.engageTools(state);
			},
			disengageAll: function () {
				state.entries = reducers.entries.disengageTopLevel(state.entries)
				state.tools = reducers.entries.disengageTopLevel(state.tools)
				state = reducers.bar.noSlates(state);
				state = reducers.bar.disengageTools(state);
			}
		},

		public_interface = {
			entry_factory: factories.entry,
			actions: actions,
			getState: () => factories.cloned(state)
		},

		init = function() {
			state = factories.cloned(classes.bar);
			actions.addEntry('0:0')
			actions.addEntry('0:1');
			actions.addEntry('0:2');
			actions.addEntry('0:2:0');
			actions.addEntry('0:2:0:0');
			actions.addEntry('0:2:1');
			actions.addEntry('0:2:1:0');
			actions.addEntry('0:2:1:1');
			actions.addEntry('0:2:1:2');
			actions.addEntry('0:3');
			actions.addEntry('0:3:0');
			actions.addEntry('0:3:1');
			actions.addTool('0:0');
			actions.addTool('0:1');
			actions.addTool('0:2');
			actions.addTool('0:3');
		};

		init();
		return public_interface;
	})($);
})($, il.UI.maincontrols.mainbar2);



(function($, mainbar) {
	mainbar.renderer = (function($) {
		var
		dom_references = {},

		part_actions = {
			triggerer: {
				withId: function (html_id) {
					return Object.assign({}, this, {html_id: html_id});
				},
				engage: function() {},
				disengage: function() {},
				unhide: function() {},
				remove: function() {}
			},
			slate: {
				withId: function (html_id) {
					return Object.assign({}, this, {html_id: html_id});
				},
				engage: function() {},
				disengage: function() {}
			},
			remover: {
				withId: function (html_id) {
					return Object.assign({}, this, {html_id: html_id});
				},
				show: function() {},
				hide: function() {},
				remove: function() {}
			}
		},

/*
		var renderEntry = function(entry) {
			var triggerer = $('#' + entry.htmlids.triggerer),
				slate = $('#' + entry.htmlids.slate);

			//slate:
			applyClassToDom(slate, cls_engaged, entry.isEngaged)
			applyClassToDom(slate, cls_disengaged, !entry.isEngaged)

			//triggerer
			applyClassToDom(triggerer.parent(), cls_hidden, entry.isHidden)
			applyClassToDom(triggerer, cls_engaged, entry.isEngaged)

			if(entry.htmlids.remover) {
				var remover = $('#' + entry.htmlids.remover).parent();
				if(entry.isEngaged) {
					remover.show();
				} else {
					remover.hide();
				}
			}
		};
*/


		actions = {
			addEntry: function (entry_id, part, html_id) {
				dom_references[entry_id] = dom_references[entry_id] || {}; //{triggerer:'', slate:'', remover:''}
				dom_references[entry_id][part] = html_id;
			},
			render: function (model_state) {

			}
		}

		public_interface = {
			registerElement: actions.addEntry,
			render: actions.render,
			parts: part_actions
		},

		init = function() {};

		init();
		return public_interface;
	})($);
})($, il.UI.maincontrols.mainbar2);




(function($, maincontrols) {
	maincontrols.mainbar = (function($) {

		/**
		 * Mainbar  provides the ->bar as collection of ->entries
		 * and a ->renderer to project the datamodel onto the DOM.
		 * Last, there is >mapping to adress entries from the outside.
		 */

		 var entry = function() {

		 }



		var ROOT_ID = "0",
			TOOL_ROOT_ID = "1"
			,cls_engaged = 'engaged' //used for buttons and slates
			,cls_disengaged = 'disengaged' //used for buttons and slates
			,cls_hidden = 'hidden' //used for buttons and slates
			,cls_page_div = 'il-layout-page' //encapsulating div of the page
			,cls_page_has_engaged_slated = 'with-mainbar-slates-engaged' //set on page_div
			,cls_tools_btn = 'il-mainbar-tools-button' //encapsulating div of the tools-button
			,cls_toolentries_wrapper = 'il-mainbar-tools-entries' //tools (within cls_entries_wrapper)
			,cls_mainbar = 'il-mainbar'
			,id
		;

		var registerSignals = function (
			component_id,
			disengage_all_signal,
			tools_toggle_signal
		) {
			id = component_id;
			_cls_single_slate = il.UI.maincontrols.slate._cls_single_slate;
			_cls_slate_engaged = il.UI.maincontrols.slate._cls_engaged;

			$(document).on(disengage_all_signal, function(event, signalData) {
				disengageTopLevel(ROOT_ID);
				disengageTopLevel(TOOL_ROOT_ID);
				render();
			});

			$(document).on(tools_toggle_signal, function(event, signalData) {

				if(entries.tools_active) {
					disengageTopLevel(TOOL_ROOT_ID);
					entries.tools_active = false;
				} else {

					var tools = entries.getChildrenOf(TOOL_ROOT_ID),
						active = 0;

					disengageTopLevel(ROOT_ID);
					for(idx in tools) {
						entry = tools[idx];
						if(entry.isEngaged) {
							active = idx;
						}
					}
					tools[active].isEngaged = true;
				}
				render();

			});
		};

		var readAndRender = function() {
			readStates();
			render();
		}

		var addTriggerSignal = function(signal) {
			$(document).on(signal, function(event, signalData) {
				var id = signalData.options.entry_id,
					action = signalData.options.action;

				switch(action) {
					case 'trigger_mapped':
						id = entries.mappings[id];
					case 'trigger':
						triggerEntry(id);
						break;
					case 'remove':
						removeEntry(id);
						break;
				}
			});
		}

		/**
		 * calculate available space and show the "more"-button if needed.
		 */
		var initMore = function() {
			var window_height = $(window).height(),
				window_width = $(window).width(),
				entries = il.UI.maincontrols.mainbar.entries,
				horizontal = il.UI.page.isSmallScreen(),
				mainbar = $('.' + cls_mainbar),
				btn = $('#' + entries.entries["0:0"].htmlids.triggerer);
				tools_available = entries.getChildrenOf(TOOL_ROOT_ID).length > 0;
				root_entries = entries.getChildrenOf(ROOT_ID);
			;

			amount_buttons = Math.floor(
				(window_height - mainbar.offset().top)	/ btn.height()
			);
			if(horizontal) {
				amount_buttons = Math.floor(window_width / btn.width());
			}

			if(tools_available) {
				amount_buttons = amount_buttons - 1;
			}

			more_entry = root_entries[root_entries.length-1];
			more_button = $('#' + more_entry.htmlids.triggerer);
			more_slate = $('#' + more_entry.htmlids.slate);

			//reset:
			more_slate.find('.btn-bulky').insertBefore(more_button);

			if(root_entries.length - 2 < amount_buttons) {
				more_button.hide();
				return;
			}

			more_button.show();
			amount_buttons = amount_buttons -1;
			for(i = amount_buttons; i < root_entries.length - 1; i++) {
				//root_entries[i].parent_id = more_entry.id;
				btn = $('#' + root_entries[i].htmlids.triggerer);
				btn.appendTo(more_slate.children('.il-maincontrols-slate-content'));
			}
		};


		var triggerEntry = function(entry_id) {
			var clicked_entry = entries.entries[entry_id],
				entry = clicked_entry,
				triggers = [entry];

			while(entry.parent_id != ROOT_ID && entry.parent_id != TOOL_ROOT_ID) {
				entry = entries.entries[entry.parent_id];
				triggers.push(entry);
			}

			var isTool = (entry.parent_id == TOOL_ROOT_ID);

			if(isTool) {
				clicked_entry.isEngaged = true;
			} else {
				//toggle
				clicked_entry.isEngaged = !clicked_entry.isEngaged;
			}

			if(clicked_entry.isEngaged) {
				clicked_entry.isHidden = false;

				if(	clicked_entry.parent_id == ROOT_ID ||
					clicked_entry.parent_id == TOOL_ROOT_ID
				){
					//close all other toplevels
					disengageTopLevel(ROOT_ID);
					disengageTopLevel(TOOL_ROOT_ID);
					clicked_entry.isEngaged = true;
				}
			}
			render();
		};

		var removeEntry = function(entry_id) {
			var clicked_entry = entries.entries[entry_id];

			clicked_entry.isEngaged = false;
			clicked_entry.isHidden = true;

			//calculate remaining Tools
			var tools = entries.getChildrenOf(TOOL_ROOT_ID);
			for(idx in tools) {
				entry = tools[idx];
				if(!entry.isHidden) {
					entry.isEngaged = true;
					break;
				}
			}
			render();
		};

		var disengageTopLevel = function(root_id) {
			toplevel = entries.getChildrenOf(root_id);
			for(idx in toplevel) {
				entry = toplevel[idx];
				entry.isEngaged = false;
			}
		}

		var render = function() {
			var idx,
				slates_generally_engaged = false,
				tools_engaged = false,
				any_visible_tools = false;

			//do the entries
			for (idx in entries.entries) {
				entry = entries.entries[idx];
				renderEntry(entry);
			}

			//is a top-level entry or tool engaged?
			toplevel = entries.getChildrenOf(ROOT_ID);
			tools = entries.getChildrenOf(TOOL_ROOT_ID);

			for(idx in tools) {
				entry = tools[idx];
				if(!entry.isHidden) {
					any_visible_tools = true;
				}
				if(entry.isEngaged) {
					tools_engaged = true;
					slates_generally_engaged = true;
				}
			}
			if(!slates_generally_engaged) {
				for(idx in toplevel) {
					entry = toplevel[idx];
					if(entry.isEngaged) {
						slates_generally_engaged = true;
					}
				}
			}

			entries.tools_active = tools_engaged;

			var tools_btn = $('#' + id +' .' + cls_tools_btn + ' .btn');
			var tools_area = $('#' + id +' .' + cls_toolentries_wrapper);
			applyClassToDom(tools_btn, cls_engaged, tools_engaged);
			applyClassToDom(tools_area, cls_engaged, tools_engaged);

			if(!any_visible_tools) {
				tools_btn.hide();
			}

			var page_div = $('.' + cls_page_div);
			applyClassToDom(page_div, cls_page_has_engaged_slated,  slates_generally_engaged);

			storeStates();
		};

		var renderEntry = function(entry) {
			var triggerer = $('#' + entry.htmlids.triggerer),
				slate = $('#' + entry.htmlids.slate);

			//slate:
			applyClassToDom(slate, cls_engaged, entry.isEngaged)
			applyClassToDom(slate, cls_disengaged, !entry.isEngaged)

			//triggerer
			applyClassToDom(triggerer.parent(), cls_hidden, entry.isHidden)
			applyClassToDom(triggerer, cls_engaged, entry.isEngaged)

			if(entry.htmlids.remover) {
				var remover = $('#' + entry.htmlids.remover).parent();
				if(entry.isEngaged) {
					remover.show();
				} else {
					remover.hide();
				}
			}
		};

		var applyClassToDom = function(node, classname, active) {
			if(active) {
				node.addClass(classname);
			} else {
				node.removeClass(classname);
			}
		};


		var EntryCollection = function() {
			this.entries = [];
			this.children = [];
			this.mappings = [];

			this.tools_active = false;

			this.addEntry = function(entry) {
				if(!this.entries.hasOwnProperty(entry.id)) {
					this.entries[entry.id] = entry;
				}
				for (etype in entry.htmlids) {
					if( entry.htmlids[etype] !== false) {
						this.entries[entry.id].htmlids[etype] = entry.htmlids[etype];
					}
				}
			};

			this.addChild = function(parent_id, entry_id){
				if(!this.children.hasOwnProperty(parent_id)) {
					this.children[parent_id] = [];
				}
				if(this.children[parent_id].indexOf(entry_id) < 0) {
					this.children[parent_id].push(entry_id);
				}
			};

			this.getChildrenOf = function(parent_id) {
				var idx, ret = [];
				for(idx in this.children[parent_id]) {
					entry_id = this.children[parent_id][idx];
					ret.push(this.entries[entry_id]);
				}
				return ret;
			}
		};

		var Entry = function(id, parent_id) {
			this.id = id;
			this.parent_id = parent_id;
			this.htmlids = {
				'triggerer': false,
				'slate': false,
				'remover': false
			};

			this.isRemoveable = false;
			this.isHidden = false;
			this.isEngaged = false;
		};

		var entries = new EntryCollection();

		var registerEntry = function (id, type, parent_id, html_id) {
			var entry = new Entry(id, parent_id);
			entry.htmlids[type] = html_id;
			entries.addEntry(entry);
			entries.addChild(parent_id, entry.id);
		};

		var addMapping = function(name, id) {
			entries.mappings[name] = id;
		};

		var hashCode = function(str) {
			var hash = 0,
				len = str.length,
				i, chr;

			for (i = 0; i < len; i = i + 1) {
				chr = str.charCodeAt(i);
				hash  = ((hash << 5) - hash) + chr;
				hash |= 0; // Convert to 32bit integer
			}
			return hash;
		};

		var cs;
		var cookieStorage = function() {
			if(!cs) {
				var base = '',
					cookie_name;
				for(idx in entries.entries) {
					base = base + idx;
				}
				cookie_name = hashCode(base);
				cs = new il.Utilities.CookieStorage(cookie_name);
			}
			return cs;
		}
		var storeStates = function() {
			cs  = cookieStorage();
			for(idx in entries.entries) {
				cs.add(idx, entries.entries[idx].isEngaged);
			}
			cs.store();
		};
		var readStates = function() {
			cs  = cookieStorage();
			for(idx in cs.items) {
				entries.entries[idx].isEngaged = cs.items[idx];
			}
			render();
		};

		return {
			entries: entries
			,registerEntry: registerEntry
			,render: render
			,addTriggerSignal: addTriggerSignal
			,addMapping: addMapping
			,registerSignals: registerSignals
			//,initActive: initActive
			,initMore: initMore
			//,storeStates: storeStates
			//,readStates: readStates
			//,cookieStorage: cookieStorage
			//,hashCode: hashCode
			,readAndRender: readAndRender
		}

	})($);
})($, il.UI.maincontrols);

