var Dom = YAHOO.util.Dom;
var Event = YAHOO.util.Event;
var DDM = YAHOO.util.DragDropMgr;

YAHOO.example.DDApp = {
	init: function() 
	{
		ddelements = Dom.getElementsBy(function (el) { return (el.className == 'ddelement') ? true : false; }, 'li', Dom.get('horizontal'));
		for (i = 0; i < ddelements.length; i++)
		{
			new YAHOO.example.DDList(ddelements[i].id);
			new YAHOO.util.DDTarget(ddelements[i].id);
		}
		this.saveOrder();
	},

	saveOrder: function(e) {
		items = Dom.getElementsBy(function (el) { return (el.className == 'ddelement') ? true : false; }, 'li', Dom.get('horizontal'));
		resultStr = '';
		for (i = 0; i < items.length; i++)
		{
			textStr = "";
			for (j = 0; j < items[i].childNodes.length; j++) textStr += items[i].childNodes[j].nodeValue;
			resultStr += textStr;
			if (i < items.length-1) resultStr += "{::}";
		}
		hidden = Dom.get('orderresult');
		hidden.value = resultStr;
	}
};

YAHOO.example.DDList = function(id, sGroup, config) {

    YAHOO.example.DDList.superclass.constructor.call(this, id, sGroup, config);

    this.logger = this.logger || YAHOO;
    var el = this.getDragEl();
    Dom.setStyle(el, "opacity", 0.67); // The proxy is slightly transparent

    this.goingUp = false;
    this.lastY = 0;
};

YAHOO.extend(YAHOO.example.DDList, YAHOO.util.DDProxy, {

    startDrag: function(x, y) {
        this.logger.log(this.id + " startDrag");

        // make the proxy look like the source element
        var dragEl = this.getDragEl();
        var clickEl = this.getEl();
        Dom.setStyle(clickEl, "visibility", "hidden");

        dragEl.innerHTML = clickEl.innerHTML;
//				Dom.addClass(dragEl, 'proxy');
Dom.setStyle(dragEl, "color", Dom.getStyle(clickEl, "color"));
Dom.setStyle(dragEl, "font-size", Dom.getStyle(clickEl, "font-size"));
Dom.setStyle(dragEl, "font-weight", Dom.getStyle(clickEl, "font-weight"));
//        Dom.setStyle(dragEl, "backgroundColor", "#ffffc0");
    },

    endDrag: function(e) {
        var srcEl = this.getEl();
        var proxy = this.getDragEl();

        // Show the proxy element and animate it to the src element's location
        Dom.setStyle(proxy, "visibility", "");
        var a = new YAHOO.util.Motion( 
            proxy, { 
                points: { 
                    to: Dom.getXY(srcEl)
                }
            }, 
            0.2, 
            YAHOO.util.Easing.easeOut 
        )
        var proxyid = proxy.id;
        var thisid = this.id;
        // Hide the proxy and show the source element when finished with the animation
        a.onComplete.subscribe(function() {
                Dom.setStyle(proxyid, "visibility", "hidden");
                Dom.setStyle(thisid, "visibility", "");
            });
        a.animate();
				YAHOO.example.DDApp.saveOrder();
    },

    onDragDrop: function(e, id) {
        // If there is one drop interaction, the li was dropped either on the list,
        // or it was dropped on the current location of the source element.
        if (DDM.interactionInfo.drop.length === 1) {

            // The position of the cursor at the time of the drop (YAHOO.util.Point)
            var pt = DDM.interactionInfo.point; 

            // The region occupied by the source element at the time of the drop
            var region = DDM.interactionInfo.sourceRegion; 

            // Check to see if we are over the source element's location.  We will
            // append to the bottom of the list once we are sure it was a drop in
            // the negative space (the area of the list without any list items)
//            if (!region.intersect(pt)) {
//              var destEl = Dom.get(id);
//              var destDD = DDM.getDDById(id);
//              //destEl.appendChild(this.getEl());
//              destDD.isEmpty = false;
//              DDM.refreshCache();
//            }

        }
    },

    onDrag: function(e) {

        // Keep track of the direction of the drag for use during onDragOver
        var y = Event.getPageY(e);

        if (y < this.lastY) {
            this.goingUp = true;
        } else if (y > this.lastY) {
            this.goingUp = false;
        }

        this.lastY = y;
    },

    onDragOver: function(e, id) {
    
        var srcEl = this.getEl();
        var destEl = Dom.get(id);

        // We are only concerned with list items, we ignore the dragover
        // notifications for the list.
        if (destEl.nodeName.toLowerCase() == "li") {
            var orig_p = srcEl.parentNode;
            var p = destEl.parentNode;

            if (this.goingUp) {
                p.insertBefore(srcEl, destEl); // insert above
            } else {
                p.insertBefore(srcEl, destEl.nextSibling); // insert below
            }

            DDM.refreshCache();
        }
    }
});

Event.onDOMReady(YAHOO.example.DDApp.init, YAHOO.example.DDApp, true);