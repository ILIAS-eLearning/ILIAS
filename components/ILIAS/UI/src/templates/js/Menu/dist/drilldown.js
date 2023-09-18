(function () {
	'use strict';

	var drilldown = function(model, mapping, persistence, dd) {
		var 
		model = model,
		mapping = mapping,
		persistence = persistence,
		dd = dd,
		instances = {},

		init = function(id, back_signal, persistence_id) {
			instances[id] = new dd(model(), mapping(), persistence(persistence_id));
			instances[id].init(id, back_signal);
		},
		
		public_interface = {
			init: init,
			instances: instances
	    };
	    return public_interface;
	};

	var dd = function(model, mapping, persistence) {

		var 
		model = model,
		mapping = mapping,
		persistence = persistence,

		init = function(id, back_signal) {
			$(document).on(back_signal, upLevel);
			var list = mapping.parse(id);
			mapping.parseLevel(list, model.actions.addLevel, engageLevel);

			var level = persistence.read();
			if(!level) {
				level = 0;
			}

			engageLevel(level);
		},
		engageLevel = function(id) {
			model.actions.engageLevel(id);
			apply();
		},
		upLevel = function() {
			model.actions.upLevel();
			apply();
		},
		apply = function() {
			var current = model.actions.getCurrent();
			mapping.setEngaged(current.id);
			persistence.store(current.id);
			mapping.setHeaderTitle(current.label);
			mapping.setHeaderBacknav(current.parent != null);
		},

		public_interface = {
			init: init,
			engage: engageLevel
	    };
	    return public_interface;
	};

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
	                }            }
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
	};

	var ddmapping = function() {
	    var 
	    classes = {
	        MENU: 'il-drilldown',
	        BUTTON: 'button.menulevel',
	        ACTIVE: 'engaged',
	        WITH_BACKLINK: 'show-backnav',
	        HEADER_TAG: 'header',
	        HEADER_TITLE_TAG: 'h2',
	        LIST_TAG: 'ul',
	        ID_ATTRIBUTE: 'data-ddindex'
	    },
	    
	    elements = {
	        dd : null,
	        header : null,
	        header_title : null,
	        levels : []
	    },
	    
	    parser = {
	        parse : function(component_id) {
	            elements.dd = document.getElementById(component_id);
	            elements.header = elements.dd.getElementsByTagName(classes.HEADER_TAG)[0];
	            elements.header_title = elements.header.getElementsByTagName(classes.HEADER_TITLE_TAG)[0];
	            var list = elements.dd.getElementsByTagName(classes.LIST_TAG)[0];
	            return list;
	        },
	        parseLevel : function(list, level_registry, clickhandler) {
	            var
	            addLevelId = function(list, id) {
	                list.setAttribute(classes.ID_ATTRIBUTE, id);
	            },
	            getLabelForList = function(list) {
	                var btn = list.parentElement.querySelector(classes.BUTTON); 
	                return btn.childNodes[0].nodeValue;     
	            },
	            getParentIdOfList = function(list) {
	                var parent = list.parentElement.parentElement;
	                return parent.getAttribute(classes.ID_ATTRIBUTE);
	            },
	            registerHandler = function(list, handler, id) {
	                var btn = list.parentElement.querySelector(classes.BUTTON); 
	                btn.addEventListener('click', function(){handler(id);});
	            },
	            
	            sublists = list.querySelectorAll(classes.LIST_TAG);

	            for(var idx = 0; idx < sublists.length; idx = idx + 1) {
	                var sublist = sublists[idx],
	                    level = level_registry( //from model
	                        getLabelForList(sublist),
	                        getParentIdOfList(sublist)
	                    );
	                addLevelId(sublist, level.id);
	                registerHandler(sublist, clickhandler, level.id);
	                elements.levels[level.id] = sublist;
	            }
	        }
	    },

	    actions = {
	        setEngaged : function(id) {
	            var idx, lower,
	                btns = elements.dd.querySelectorAll(classes.BUTTON);
	            
	            for(idx = 0; idx < btns.length; idx = idx + 1) {
	                btns[idx].classList.remove(classes.ACTIVE);
	            }
	            elements.levels[id].parentElement.querySelector(classes.BUTTON)
	                .classList.add(classes.ACTIVE);
	            
	             try { //cannot access children in mocha/jsdom
	                lower = elements.levels[id].children[0].children[0];
	                lower.focus();
	            }
	            catch (e) {
	            }
	        },
	        setHeaderTitle : function(title) {
	            elements.header_title.innerHTML = title;
	        },
	        setHeaderBacknav : function(status) {
	            if(status) {
	                elements.header.classList.add(classes.WITH_BACKLINK);
	            } else {
	                elements.header.classList.remove(classes.WITH_BACKLINK);
	            }
	        }
	    },

	    public_interface = {
	        parse : parser.parse,
	        parseLevel : parser.parseLevel,
	        setEngaged : actions.setEngaged,
	        setHeaderTitle : actions.setHeaderTitle,
	        setHeaderBacknav : actions.setHeaderBacknav
	    };
	    return public_interface;
	};

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

	il = il || {};
	il.UI = il.UI || {};
	il.UI.menu = il.UI.menu || {};

	il.UI.menu.drilldown = drilldown(
	 	ddmodel,
		ddmapping,
		ddpersistence,
		dd
	);

}());
