var Y = YAHOO,
    Y_DOM = YAHOO.util.Dom, 
    EMPTY_ARRAY = [],
    Y_UA = Y.env.ua,
    Y_Lang = Y.lang,
    Y_DOC = document,
    Y_DOCUMENT_ELEMENT = Y_DOC.documentElement,

    Y_DOM_inDoc = Y_DOM.inDocument, 
    Y_mix = Y_Lang.augmentObject,
    Y_guid = Y_DOM.generateId,

    Y_getDoc = function(element) {
        var doc = Y_DOC;
        if (element) {
            doc = (element.nodeType === 9) ? element : // element === document
                element.ownerDocument || // element === DOM node
                element.document || // element === window
                Y_DOC; // default
        }

        return doc;
    },
    
    Y_Array = function(o, startIdx) {
        var l, a, start = startIdx || 0;

        // IE errors when trying to slice HTMLElement collections
        try {
            return Array.prototype.slice.call(o, start);
        } catch (e) {
            a = [];
            l = o.length;
            for (; start < l; start++) {
                a.push(o[start]);
            }
            return a;
        }
    },

    Y_DOM_allById = function(id, root) {
        root = root || Y_DOC;
        var nodes = [],
            ret = [],
            i,
            node;

        if (root.querySelectorAll) {
            ret = root.querySelectorAll('[id="' + id + '"]');
        } else if (root.all) {
            nodes = root.all(id);

            if (nodes) {
                // root.all may return HTMLElement or HTMLCollection.
                // some elements are also HTMLCollection (FORM, SELECT).
                if (nodes.nodeName) {
                    if (nodes.id === id) { // avoid false positive on name
                        ret.push(nodes);
                        nodes = EMPTY_ARRAY; // done, no need to filter
                    } else { //  prep for filtering
                        nodes = [nodes];
                    }
                }

                if (nodes.length) {
                    // filter out matches on node.name
                    // and element.id as reference to element with id === 'id'
                    for (i = 0; node = nodes[i++];) {
                        if (node.id === id  || 
                                (node.attributes && node.attributes.id &&
                                node.attributes.id.value === id)) { 
                            ret.push(node);
                        }
                    }
                }
            }
        } else {
            ret = [Y_getDoc(root).getElementById(id)];
        }

        return ret;
    };

