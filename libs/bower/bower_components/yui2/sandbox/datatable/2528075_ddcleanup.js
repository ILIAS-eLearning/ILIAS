var util = YAHOO.util,
    Dom = util.Dom,
    Ev = util.Event,
    DD = util.DD,
    lang = YAHOO.lang,
    DT = YAHOO.widget.DataTable,
    proto = DT.prototype;
 
proto._elColumnDragTarget = null;

proto._elColumnResizerProxy = null;

proto._destroyDraggableColumns = function() {
    var oColumn, elTh;
    for(var i=0, len=this._oColumnSet.tree[0].length; i<len; i++) {
        oColumn = this._oColumnSet.tree[0][i];
        if(oColumn._dd) {
            oColumn._dd = oColumn._dd.unreg();
            Dom.removeClass(oColumn.getThEl(), DT.CLASS_DRAGGABLE);
        }
    }

    // Destroy column drag proxy
    this._destroyColumnDragTargetEl();
};

proto._initDraggableColumns = function() {
    this._destroyDraggableColumns();
    if(DD) {
        var oColumn, elTh, elDragTarget;
        for(var i=0, len=this._oColumnSet.tree[0].length; i<len; i++) {
            oColumn = this._oColumnSet.tree[0][i];
            elTh = oColumn.getThEl();
            Dom.addClass(elTh, DT.CLASS_DRAGGABLE);
            elDragTarget = this._initColumnDragTargetEl();
            oColumn._dd = new YAHOO.widget.ColumnDD(this, oColumn, elTh, elDragTarget);
        }
    }
    else {
    }
};

proto._destroyColumnDragTargetEl = function() {
    if(this._elColumnDragTarget) {
        var el = this._elColumnDragTarget;
        Ev.purgeElement(el);
        el.parentNode.removeChild(el);
        this._elColumnDragTarget = null;
    }
};

proto._initColumnDragTargetEl = function() {
    if(!this._elColumnDragTarget) {
        // Attach Column drag target element as first child of body
        var elColumnDragTarget = document.createElement('div');
        elColumnDragTarget.id = this.getId() + "-coltarget";
        elColumnDragTarget.className = DT.CLASS_COLTARGET;
        elColumnDragTarget.style.display = "none";
        document.body.insertBefore(elColumnDragTarget, document.body.firstChild);

        // Internal tracker of Column drag target
        this._elColumnDragTarget = elColumnDragTarget;

    }
    return this._elColumnDragTarget;
};

proto._destroyResizeableColumns = function() {
    var aKeys = this._oColumnSet.keys;
    for(var i=0, len=aKeys.length; i<len; i++) {
        if(aKeys[i]._ddResizer) {
            aKeys[i]._ddResizer = aKeys[i]._ddResizer.unreg();
            Dom.removeClass(aKeys[i].getThEl(), DT.CLASS_RESIZEABLE);
        }
    }

    // Destroy resizer proxy
    this._destroyColumnResizerProxyEl();
};

proto._initResizeableColumns = function() {
    this._destroyResizeableColumns();
    if(DD) {
        var oColumn, elTh, elThLiner, elThResizerLiner, elThResizer, elResizerProxy, cancelClick;
        for(var i=0, len=this._oColumnSet.keys.length; i<len; i++) {
            oColumn = this._oColumnSet.keys[i];
            if(oColumn.resizeable) {
                elTh = oColumn.getThEl();
                Dom.addClass(elTh, DT.CLASS_RESIZEABLE);
                elThLiner = oColumn.getThLinerEl();

                // Bug 1915349: So resizer is as tall as TH when rowspan > 1
                // Create a separate resizer liner with position:relative
                elThResizerLiner = elTh.appendChild(document.createElement("div"));
                elThResizerLiner.className = DT.CLASS_RESIZERLINER;

                // Move TH contents into the new resizer liner
                elThResizerLiner.appendChild(elThLiner);

                // Create the resizer
                elThResizer = elThResizerLiner.appendChild(document.createElement("div"));
                elThResizer.id = elTh.id + "-resizer"; // Needed for ColumnResizer
                elThResizer.className = DT.CLASS_RESIZER;
                oColumn._elResizer = elThResizer;

                // Create the resizer proxy, once per instance
                elResizerProxy = this._initColumnResizerProxyEl();
                oColumn._ddResizer = new YAHOO.util.ColumnResizer(
                        this, oColumn, elTh, elThResizer, elResizerProxy);
                cancelClick = function(e) {
                    Ev.stopPropagation(e);
                };
                Ev.addListener(elThResizer,"click",cancelClick);
            }
        }
    }
    else {
    }
};

proto._destroyColumnResizerProxyEl = function() {
    if(this._elColumnResizerProxy) {
        var el = this._elColumnResizerProxy;
        Ev.purgeElement(el);
        el.parentNode.removeChild(el);
        this._elColumnResizerProxy = null;
    }
};

proto._initColumnResizerProxyEl = function() {
    if(!this._elColumnResizerProxy) {
        // Attach Column resizer element as first child of body
        var elColumnResizerProxy = document.createElement("div");
        elColumnResizerProxy.id = this.getId() + "-colresizerproxy"; // Needed for ColumnResizer
        elColumnResizerProxy.className = DT.CLASS_RESIZERPROXY;
        document.body.insertBefore(elColumnResizerProxy, document.body.firstChild);

        // Internal tracker of Column resizer proxy
        this._elColumnResizerProxy = elColumnResizerProxy;
    }
    return this._elColumnResizerProxy;
};

proto.destroy = function() {
    // Store for later
    var instanceName = this.toString();

    this._oChainRender.stop();

    // Destroy ColumnDD and ColumnResizers
    this._destroyColumnHelpers();

    // Destroy all CellEditors
    var oCellEditor;
    for(var i=0, len=this._oColumnSet.flat.length; i<len; i++) {
        oCellEditor = this._oColumnSet.flat[i].editor;
        if(oCellEditor && oCellEditor.destroy) {
            oCellEditor.destroy();
            this._oColumnSet.flat[i].editor = null;
        }
    }

    // Destroy Paginator
    this._destroyPaginator();

    // Unhook custom events
    this._oRecordSet.unsubscribeAll();
    this.unsubscribeAll();

    // Unhook DOM events
    Ev.removeListener(document, "click", this._onDocumentClick);

    // Clear out the container
    this._destroyContainerEl(this._elContainer);

    // Null out objects
    for(var param in this) {
        if(lang.hasOwnProperty(this, param)) {
            this[param] = null;
        }
    }

    // Clean up static values
    DT._nCurrentCount--;

    if(DT._nCurrentCount < 1) {
        if(DT._elDynStyleNode) {
            document.getElementsByTagName('head')[0].removeChild(DT._elDynStyleNode);
            DT._elDynStyleNode = null;
        }
    }
};

