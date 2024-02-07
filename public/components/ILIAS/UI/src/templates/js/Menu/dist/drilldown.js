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
			mapping.parseLevel(list, model.actions.addLevel, engageLevel, filter);

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
	  filter = function(e) {
	    model.actions.filter(e);
	    mapping.setFiltered(model.actions.getFiltered());
	    e.target.focus();
	  },
		upLevel = function() {
			model.actions.upLevel();
			apply();
		},
		apply = function() {
			let
	    current = model.actions.getCurrent(),
	    parent = model.actions.getParent(),
	    level = 2;
	    if (current.parent === null) {
	      level = 0;
	    } else if (current.parent === '0') {
	      level = 1;
	    }
			mapping.setEngaged(current.id);
			persistence.store(current.id);
			mapping.setHeader(current.headerDisplayElement, parent.headerDisplayElement);
			mapping.setHeaderBacknav(level);
	    mapping.correctRightColumnPosition(current.id);
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
	            filtered : false,
	            headerDisplayElement : '',
	            leaves : []
	        }
	    },

	    factories = {
	        cloned: (obj, params) => Object.assign({}, obj, params),
	        level : (headerDisplayElement, parent, leaves) => factories.cloned(classes.level, {
	            id : data.length.toString(),
	            headerDisplayElement : headerDisplayElement,
	            parent : parent,
	            leaves : leaves
	        })
	    },

	    actions = {
	      addLevel : function(headerDisplayElement, parent, leaves) {
	          if(! parent) {
	              parent = null;
	          }
	          var level = factories.level(headerDisplayElement, parent, leaves);
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
	              }          }
	          return data[0];
	      },
	      getParent : function() {
	          let cur = actions.getCurrent();
	          if (cur.parent) {
	            return data[cur.parent];
	          }

	          return {};
	      },
	      upLevel : function() {
	          var cur = actions.getCurrent();
	          if(cur.parent) {
	              actions.engageLevel(data[cur.parent].id);
	          }
	      },
	      /**
	       * @param {Event} e
	       */
	      filter : function (e) {
	        let
	        value = e.target.value.toLowerCase(),
	        removeFilteredRecursive = (id) => {
	          if (id !== null && id !== 0) {
	            data[id].filtered = false;
	            if (data[id].parent !== null && data[id].parent !== 0) {
	              removeFilteredRecursive(data[id].parent);
	            }
	          }
	        };

	        data.forEach(
	          (level, levelId) => {
	            var hasVisibleLeaves = false;
	            level.leaves.forEach(
	              (leaf) => {
	                if (leaf.text.toLowerCase().includes(value) === false) {
	                  leaf.filtered = true;
	                  return;
	                }
	                leaf.filtered = false;
	                hasVisibleLeaves = true;
	              }
	            );
	            level.filtered = true;
	            if (hasVisibleLeaves) {
	              level.filtered = false;
	              if (level.parent !== null && level.parent !== 0) {
	                removeFilteredRecursive(levelId);
	              }
	            }
	          }
	        );
	      },
	      getFiltered : function () {
	        let filtered = [];
	        data.forEach(
	          (level) => {
	            if (level.filtered) {
	              filtered.push(level);
	              return;
	            }
	            let leaves = level.leaves.filter(
	              (leaf) => {
	                return leaf.filtered;
	              }
	            );
	            if (leaves.length > 0) {
	              let clone = factories.cloned(classes.level, {
	                id : level.id,
	                headerDisplayElement : level.headerDisplayElement,
	                parent : level.parent,
	                leaves : [...leaves]
	              });
	              filtered.push(clone);
	            }
	          }
	        );
	        return filtered;
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
	        HEADER_ELEMENT_CLASS: 'menulevel',
	        ACTIVE: 'engaged',
	        ACTIVE_PARENT: 'engaged-parent',
	        FILTERED: 'filtered',
	        WITH_BACKLINK_ONE_COL: 'show-backnav',
	        WITH_BACKLINK_TWO_COL: 'show-backnav-two-col',
	        HEADER_TAG: 'header',
	        FILTER_TAG: 'div',
	        LIST_TAG: 'ul',
	        LIST_ELEMENT_TAG: 'li',
	        INTERACTIVE_ELEMENTS_TAG: 'button, a',
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
	            var list = elements.dd.getElementsByTagName(classes.LIST_TAG)[0];
	            return list;
	        },
	        parseLevel : function(list, levelRegistry, clickHandler, filterHandler) {
	            let
	            addLevelId = function(list, id) {
	                list.setAttribute(classes.ID_ATTRIBUTE, id);
	            },
	            getLabelForList = function(list) {
	                var element = list.parentElement.querySelector('.' + classes.HEADER_ELEMENT_CLASS);
	                if (element.nodeName === classes.FILTER_TAG.toUpperCase()) {
	                  let elem = element.cloneNode(true);
	                  elem.classList.remove(classes.HEADER_ELEMENT_CLASS);
	                  elem.getElementsByTagName('input')[0].addEventListener('keyup', filterHandler);
	                  return elem;
	                }

	                let elem = document.createElement('h2');
	                elem.innerText = element.childNodes[0].nodeValue;
	                return elem;
	            },
	            getParentIdOfList = function(list) {
	                var parent = list.parentElement.parentElement;
	                return parent.getAttribute(classes.ID_ATTRIBUTE);
	            },
	            getLeavesOfList = function(list) {
	              let
	              children = list.querySelectorAll(':scope >' + classes.LIST_ELEMENT_TAG),
	              leaves = [];
	              children.forEach(
	                (child, id) => {
	                  if (child.getElementsByTagName(classes.LIST_TAG).length === 0) {
	                    let interactiveElement = child.querySelectorAll(classes.INTERACTIVE_ELEMENTS_TAG)[0];
	                    if (interactiveElement === undefined) {
	                      return;
	                    }
	                    let leaf = {
	                      id : id,
	                      text : interactiveElement.innerText,
	                      filtered : false
	                    };
	                    leaves.push(leaf);
	                  }
	                }
	              );
	              return leaves;
	            },
	            registerHandler = function(list, handler, id) {
	                let headerElement = list.parentElement.querySelector('.' + classes.HEADER_ELEMENT_CLASS);
	                headerElement.addEventListener('click', function(){handler(id);});
	            },

	            sublists = list.querySelectorAll(classes.LIST_TAG);

	            for(var idx = 0; idx < sublists.length; idx = idx + 1) {
	                var sublist = sublists[idx],
	                    level = levelRegistry( //from model
	                        getLabelForList(sublist),
	                        getParentIdOfList(sublist),
	                        getLeavesOfList(sublist)
	                    );
	                addLevelId(sublist, level.id);
	                registerHandler(sublist, clickHandler, level.id);
	                elements.levels[level.id] = sublist;
	            }
	        }
	    },

	    actions = {
	        setEngaged : function(id) {
	            var idx, lower,
	                headerElements = elements.dd.querySelectorAll('.' + classes.HEADER_ELEMENT_CLASS);

	            for(idx = 0; idx < headerElements.length; idx = idx + 1) {
	                headerElements[idx].classList.remove(classes.ACTIVE);
	                headerElements[idx].classList.remove(classes.ACTIVE_PARENT);
	            }
	            elements.levels[id].parentElement.querySelector('.' + classes.HEADER_ELEMENT_CLASS)
	                .classList.add(classes.ACTIVE);
	            elements.levels[id].parentElement.parentElement.parentElement.querySelector('.' + classes.HEADER_ELEMENT_CLASS)
	                .classList.add(classes.ACTIVE_PARENT);

	             try { //cannot access children in mocha/jsdom
	                lower = elements.levels[id].children[0].children[0];
	                lower.focus();
	            }
	            catch (e) {
	            }
	        },
	        setFiltered : function(filteredElements) {
	          let
	          headerElements = elements.dd.querySelectorAll('.' + classes.HEADER_ELEMENT_CLASS),
	          filteredElementsIds = filteredElements.map((v) => {return v.id;}),
	          allFilteredElementsIds = filteredElements.map((v) => {return v.id;}),
	          idsToFilter = filteredElements.filter((v) => {return v.filtered;}).map((v) => {return v.id;});

	          if (filteredElements.length === 0) {
	            elements.dd.classList.remove(classes.FILTERED);
	            let els = elements.dd.querySelectorAll(':scope > ul > li > ul > li > .' + classes.HEADER_ELEMENT_CLASS);
	            els.forEach(
	              (element) => {element.disabled = false;}
	            );
	          } else {
	            actions.setEngaged(0);
	            elements.dd.classList.add(classes.FILTERED);
	            let els = elements.dd.querySelectorAll(':scope > ul > li > ul > li > .' + classes.HEADER_ELEMENT_CLASS);
	            els.forEach(
	              (element) => {element.disabled = true;}
	            );
	          }

	          for (let idx = 0; idx < headerElements.length; idx = idx + 1) {
	            let children = headerElements[idx].nextElementSibling.querySelectorAll(':scope >' + classes.LIST_ELEMENT_TAG);
	            children.forEach(
	                (child) => {child.classList.remove(classes.FILTERED);}
	            );

	            if (allFilteredElementsIds.includes(idx.toString())) {
	              let index = filteredElementsIds.indexOf(idx.toString());
	              filteredElements[index].leaves.forEach(
	                (child) => {
	                  children[child.id].classList.add(classes.FILTERED);
	                }
	              );
	            }

	            if (idsToFilter.includes(idx.toString())) {
	              headerElements[idx].classList.add(classes.FILTERED);
	              continue;
	            }

	            headerElements[idx].classList.remove(classes.FILTERED);
	          }
	        },
	        setHeader : function(headerElement, headerParentElement) {
	            elements.header.children[1].replaceWith(document.createElement('div'));
	            elements.header.children[0].replaceWith(headerElement);
	            if (headerParentElement !== undefined) {
	              elements.header.children[1].replaceWith(headerParentElement);
	            }
	        },
	        setHeaderBacknav : function(level) {
	            elements.header.classList.remove(classes.WITH_BACKLINK_TWO_COL);
	            elements.header.classList.remove(classes.WITH_BACKLINK_ONE_COL);
	            if (level === 0) {
	                return;
	            }
	            if (level > 1) {
	                elements.header.classList.add(classes.WITH_BACKLINK_TWO_COL);
	            }
	            elements.header.classList.add(classes.WITH_BACKLINK_ONE_COL);
	        },
	        correctRightColumnPosition : function(id) {
	          let elem = elements.levels[id];
	          elements.levels.forEach(
	            (e) => {e.style.removeProperty('top');}
	          );
	          elem.style.top = '-' + elem.offsetTop + 'px';
	        }
	    },

	    public_interface = {
	        parse : parser.parse,
	        parseLevel : parser.parseLevel,
	        setEngaged : actions.setEngaged,
	        setFiltered : actions.setFiltered,
	        setHeader : actions.setHeader,
	        setHeaderBacknav : actions.setHeaderBacknav,
	        correctRightColumnPosition : actions.correctRightColumnPosition
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

})();
