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
                registerHandler(sublist, clickhandler, level.id)
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
                lower = elements.levels[id].children[0].children[0]
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
}
export default ddmapping;