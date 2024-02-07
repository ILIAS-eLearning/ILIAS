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
                lower = elements.levels[id].children[0].children[0]
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
            let els = elements.dd.querySelectorAll(':scope > ul > li > ul > li > .' + classes.HEADER_ELEMENT_CLASS)
            els.forEach(
              (element) => {element.disabled = false;}
            );
          } else {
            actions.setEngaged(0);
            elements.dd.classList.add(classes.FILTERED);
            let els = elements.dd.querySelectorAll(':scope > ul > li > ul > li > .' + classes.HEADER_ELEMENT_CLASS)
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
            (e) => {e.style.removeProperty('top')}
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
}
export default ddmapping;