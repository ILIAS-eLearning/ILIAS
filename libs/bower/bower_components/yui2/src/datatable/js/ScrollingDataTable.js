(function () {

var lang   = YAHOO.lang,
    util   = YAHOO.util,
    widget = YAHOO.widget,
    ua     = YAHOO.env.ua,
    
    Dom    = util.Dom,
    Ev     = util.Event,
    DS     = util.DataSourceBase,
    DT     = widget.DataTable,
    Pag    = widget.Paginator;
    
/**
 * The ScrollingDataTable class extends the DataTable class to provide
 * functionality for x-scrolling, y-scrolling, and xy-scrolling.
 *
 * @namespace YAHOO.widget
 * @class ScrollingDataTable
 * @extends YAHOO.widget.DataTable
 * @constructor
 * @param elContainer {HTMLElement} Container element for the TABLE.
 * @param aColumnDefs {Object[]} Array of object literal Column definitions.
 * @param oDataSource {YAHOO.util.DataSource} DataSource instance.
 * @param oConfigs {object} (optional) Object literal of configuration values.
 */
widget.ScrollingDataTable = function(elContainer,aColumnDefs,oDataSource,oConfigs) {
    oConfigs = oConfigs || {};
    
    // Prevent infinite loop
    if(oConfigs.scrollable) {
        oConfigs.scrollable = false;
    }

    this._init();

    widget.ScrollingDataTable.superclass.constructor.call(this, elContainer,aColumnDefs,oDataSource,oConfigs); 

    // Once per instance
    this.subscribe("columnShowEvent", this._onColumnChange);
};

var SDT = widget.ScrollingDataTable;

/////////////////////////////////////////////////////////////////////////////
//
// Public constants
//
/////////////////////////////////////////////////////////////////////////////
lang.augmentObject(SDT, {

    /**
     * Class name assigned to inner DataTable header container.
     *
     * @property DataTable.CLASS_HEADER
     * @type String
     * @static
     * @final
     * @default "yui-dt-hd"
     */
    CLASS_HEADER : "yui-dt-hd",
    
    /**
     * Class name assigned to inner DataTable body container.
     *
     * @property DataTable.CLASS_BODY
     * @type String
     * @static
     * @final
     * @default "yui-dt-bd"
     */
    CLASS_BODY : "yui-dt-bd"
});

lang.extend(SDT, DT, {

/**
 * Container for fixed header TABLE element.
 *
 * @property _elHdContainer
 * @type HTMLElement
 * @private
 */
_elHdContainer : null,

/**
 * Fixed header TABLE element.
 *
 * @property _elHdTable
 * @type HTMLElement
 * @private
 */
_elHdTable : null,

/**
 * Container for scrolling body TABLE element.
 *
 * @property _elBdContainer
 * @type HTMLElement
 * @private
 */
_elBdContainer : null,

/**
 * Body THEAD element.
 *
 * @property _elBdThead
 * @type HTMLElement
 * @private
 */
_elBdThead : null,

/**
 * Offscreen container to temporarily clone SDT for auto-width calculation.
 *
 * @property _elTmpContainer
 * @type HTMLElement
 * @private
 */
_elTmpContainer : null,

/**
 * Offscreen TABLE element for auto-width calculation.
 *
 * @property _elTmpTable
 * @type HTMLElement
 * @private
 */
_elTmpTable : null,

/**
 * True if x-scrollbar is currently visible.
 * @property _bScrollbarX
 * @type Boolean
 * @private 
 */
_bScrollbarX : null,















/////////////////////////////////////////////////////////////////////////////
//
// Superclass methods
//
/////////////////////////////////////////////////////////////////////////////

/**
 * Implementation of Element's abstract method. Sets up config values.
 *
 * @method initAttributes
 * @param oConfigs {Object} (Optional) Object literal definition of configuration values.
 * @private
 */

initAttributes : function(oConfigs) {
    oConfigs = oConfigs || {};
    SDT.superclass.initAttributes.call(this, oConfigs);

    /**
    * @attribute width
    * @description Table width for scrollable tables (e.g., "40em").
    * @type String
    */
    this.setAttributeConfig("width", {
        value: null,
        validator: lang.isString,
        method: function(oParam) {
            if(this._elHdContainer && this._elBdContainer) {
                this._elHdContainer.style.width = oParam;
                this._elBdContainer.style.width = oParam;            
                this._syncScrollX();      
                this._syncScrollOverhang();
            }
        }
    });

    /**
    * @attribute height
    * @description Table body height for scrollable tables, not including headers (e.g., "40em").
    * @type String
    */
    this.setAttributeConfig("height", {
        value: null,
        validator: lang.isString,
        method: function(oParam) {
            if(this._elHdContainer && this._elBdContainer) {
                this._elBdContainer.style.height = oParam;    
                this._syncScrollX();   
                this._syncScrollY();
                this._syncScrollOverhang();
            }
        }
    });

    /**
    * @attribute COLOR_COLUMNFILLER
    * @description CSS color value assigned to header filler on scrollable tables.  
    * @type String
    * @default "#F2F2F2"
    */
    this.setAttributeConfig("COLOR_COLUMNFILLER", {
        value: "#F2F2F2",
        validator: lang.isString,
        method: function(oParam) {
            if(this._elHdContainer) {
                this._elHdContainer.style.backgroundColor = oParam;
            }
        }
    });
},

/**
 * Initializes internal variables.
 *
 * @method _init
 * @private
 */
_init : function() {
    this._elHdContainer = null;
    this._elHdTable = null;
    this._elBdContainer = null;
    this._elBdThead = null;
    this._elTmpContainer = null;
    this._elTmpTable = null;
},

/**
 * Initializes DOM elements for a ScrollingDataTable, including creation of
 * two separate TABLE elements.
 *
 * @method _initDomElements
 * @param elContainer {HTMLElement | String} HTML DIV element by reference or ID. 
 * return {Boolean} False in case of error, otherwise true 
 * @private
 */
_initDomElements : function(elContainer) {
    // Outer and inner containers
    this._initContainerEl(elContainer);
    if(this._elContainer && this._elHdContainer && this._elBdContainer) {
        // TABLEs
        this._initTableEl();
        
        if(this._elHdTable && this._elTable) {
            // COLGROUPs
            ///this._initColgroupEl(this._elHdTable, this._elTable);  
            this._initColgroupEl(this._elHdTable);        
            
            // THEADs
            this._initTheadEl(this._elHdTable, this._elTable);
            
            // Primary TBODY
            this._initTbodyEl(this._elTable);
            // Message TBODY
            this._initMsgTbodyEl(this._elTable);            
        }
    }
    if(!this._elContainer || !this._elTable || !this._elColgroup ||  !this._elThead || !this._elTbody || !this._elMsgTbody ||
            !this._elHdTable || !this._elBdThead) {
        YAHOO.log("Could not instantiate DataTable due to an invalid DOM elements", "error", this.toString());
        return false;
    }
    else {
        return true;
    }
},

/**
 * Destroy's the DataTable outer and inner container elements, if available.
 *
 * @method _destroyContainerEl
 * @param elContainer {HTMLElement} Reference to the container element. 
 * @private
 */
_destroyContainerEl : function(elContainer) {
    Dom.removeClass(elContainer, DT.CLASS_SCROLLABLE);
    SDT.superclass._destroyContainerEl.call(this, elContainer);
    this._elHdContainer = null;
    this._elBdContainer = null;
},

/**
 * Initializes the DataTable outer container element and creates inner header
 * and body container elements.
 *
 * @method _initContainerEl
 * @param elContainer {HTMLElement | String} HTML DIV element by reference or ID.
 * @private
 */
_initContainerEl : function(elContainer) {
    SDT.superclass._initContainerEl.call(this, elContainer);
    
    if(this._elContainer) {
        elContainer = this._elContainer; // was constructor input, now is DOM ref
        Dom.addClass(elContainer, DT.CLASS_SCROLLABLE);
        
        // Container for header TABLE
        var elHdContainer = document.createElement("div");
        elHdContainer.style.width = this.get("width") || "";
        elHdContainer.style.backgroundColor = this.get("COLOR_COLUMNFILLER");
        Dom.addClass(elHdContainer, SDT.CLASS_HEADER);
        this._elHdContainer = elHdContainer;
        elContainer.appendChild(elHdContainer);
    
        // Container for body TABLE
        var elBdContainer = document.createElement("div");
        elBdContainer.style.width = this.get("width") || "";
        elBdContainer.style.height = this.get("height") || "";
        Dom.addClass(elBdContainer, SDT.CLASS_BODY);
        Ev.addListener(elBdContainer, "scroll", this._onScroll, this); // to sync horiz scroll headers
        this._elBdContainer = elBdContainer;
        elContainer.appendChild(elBdContainer);
    }
},

/**
 * Creates HTML markup CAPTION element.
 *
 * @method _initCaptionEl
 * @param sCaption {String} Text for caption.
 * @private
 */
_initCaptionEl : function(sCaption) {
    // Not yet supported
    /*if(this._elHdTable && sCaption) {
        // Create CAPTION element
        if(!this._elCaption) { 
            this._elCaption = this._elHdTable.createCaption();
        }
        // Set CAPTION value
        this._elCaption.innerHTML = sCaption;
    }
    else if(this._elCaption) {
        this._elCaption.parentNode.removeChild(this._elCaption);
    }*/
},

/**
 * Destroy's the DataTable head TABLE element, if available.
 *
 * @method _destroyHdTableEl
 * @private
 */
_destroyHdTableEl : function() {
    var elTable = this._elHdTable;
    if(elTable) {
        Ev.purgeElement(elTable, true);
        elTable.parentNode.removeChild(elTable);
        
        // A little out of place, but where else can we null out these extra elements?
        ///this._elBdColgroup = null;
        this._elBdThead = null;
    }
},

/**
 * Initializes ScrollingDataTable TABLE elements into the two inner containers.
 *
 * @method _initTableEl
 * @private
 */
_initTableEl : function() {
    // Head TABLE
    if(this._elHdContainer) {
        this._destroyHdTableEl();
    
        // Create TABLE
        this._elHdTable = this._elHdContainer.appendChild(document.createElement("table"));   

        // Set up mouseover/mouseout events via mouseenter/mouseleave delegation
        Ev.delegate(this._elHdTable, "mouseenter", this._onTableMouseover, "thead ."+DT.CLASS_LABEL, this);
        Ev.delegate(this._elHdTable, "mouseleave", this._onTableMouseout, "thead ."+DT.CLASS_LABEL, this);
    }
    // Body TABLE
    SDT.superclass._initTableEl.call(this, this._elBdContainer);
},

/**
 * Initializes ScrollingDataTable THEAD elements into the two inner containers.
 *
 * @method _initTheadEl
 * @param elHdTable {HTMLElement} (optional) Fixed header TABLE element reference.
 * @param elTable {HTMLElement} (optional) TABLE element reference.
 * @private
 */
_initTheadEl : function(elHdTable, elTable) {
    elHdTable = elHdTable || this._elHdTable;
    elTable = elTable || this._elTable;
    
    // Scrolling body's THEAD
    this._initBdTheadEl(elTable);
    // Standard fixed head THEAD
    SDT.superclass._initTheadEl.call(this, elHdTable);
},

/**
 * SDT changes ID so as not to duplicate the accessibility TH IDs.
 *
 * @method _initThEl
 * @param elTh {HTMLElement} TH element reference.
 * @param oColumn {YAHOO.widget.Column} Column object.
 * @private
 */
_initThEl : function(elTh, oColumn) {
    SDT.superclass._initThEl.call(this, elTh, oColumn);
    elTh.id = this.getId() +"-fixedth-" + oColumn.getSanitizedKey(); // Needed for getColumn by TH and ColumnDD
},

/**
 * Destroy's the DataTable body THEAD element, if available.
 *
 * @method _destroyBdTheadEl
 * @private
 */
_destroyBdTheadEl : function() {
    var elBdThead = this._elBdThead;
    if(elBdThead) {
        var elTable = elBdThead.parentNode;
        Ev.purgeElement(elBdThead, true);
        elTable.removeChild(elBdThead);
        this._elBdThead = null;

        this._destroyColumnHelpers();
    }
},

/**
 * Initializes body THEAD element.
 *
 * @method _initBdTheadEl
 * @param elTable {HTMLElement} TABLE element into which to create THEAD.
 * @return {HTMLElement} Initialized THEAD element. 
 * @private
 */
_initBdTheadEl : function(elTable) {
    if(elTable) {
        // Destroy previous
        this._destroyBdTheadEl();

        var elThead = elTable.insertBefore(document.createElement("thead"), elTable.firstChild);
        
        // Add TRs to the THEAD;
        var oColumnSet = this._oColumnSet,
            colTree = oColumnSet.tree,
            elTh, elTheadTr, oColumn, i, j, k, len;

        for(i=0, k=colTree.length; i<k; i++) {
            elTheadTr = elThead.appendChild(document.createElement("tr"));
    
            // ...and create TH cells
            for(j=0, len=colTree[i].length; j<len; j++) {
                oColumn = colTree[i][j];
                elTh = elTheadTr.appendChild(document.createElement("th"));
                this._initBdThEl(elTh,oColumn,i,j);
            }
        }
        this._elBdThead = elThead;
        YAHOO.log("Accessibility TH cells for " + this._oColumnSet.keys.length + " keys created","info",this.toString());
    }
},

/**
 * Populates TH element for the body THEAD element.
 *
 * @method _initBdThEl
 * @param elTh {HTMLElement} TH element reference.
 * @param oColumn {YAHOO.widget.Column} Column object.
 * @private
 */
_initBdThEl : function(elTh, oColumn) {
    elTh.id = this.getId()+"-th-" + oColumn.getSanitizedKey(); // Needed for accessibility
    elTh.rowSpan = oColumn.getRowspan();
    elTh.colSpan = oColumn.getColspan();
    // Assign abbr attribute
    if(oColumn.abbr) {
        elTh.abbr = oColumn.abbr;
    }

    // TODO: strip links and form elements
    var sKey = oColumn.getKey();
    var sLabel = lang.isValue(oColumn.label) ? oColumn.label : sKey;
    elTh.innerHTML = sLabel;
},

/**
 * Initializes ScrollingDataTable TBODY element for data
 *
 * @method _initTbodyEl
 * @param elTable {HTMLElement} TABLE element into which to create TBODY .
 * @private
 */
_initTbodyEl : function(elTable) {
    SDT.superclass._initTbodyEl.call(this, elTable);
    
    // Bug 2105534 - Safari 3 gap
    // Bug 2492591 - IE8 offsetTop
    elTable.style.marginTop = (this._elTbody.offsetTop > 0) ?
            "-"+this._elTbody.offsetTop+"px" : 0;
},





























/**
 * Sets focus on the given element.
 *
 * @method _focusEl
 * @param el {HTMLElement} Element.
 * @private
 */
_focusEl : function(el) {
    el = el || this._elTbody;
    var oSelf = this;
    this._storeScrollPositions();
    // http://developer.mozilla.org/en/docs/index.php?title=Key-navigable_custom_DHTML_widgets
    // The timeout is necessary in both IE and Firefox 1.5, to prevent scripts from doing
    // strange unexpected things as the user clicks on buttons and other controls.
    
    // Bug 1921135: Wrap the whole thing in a setTimeout
    setTimeout(function() {
        setTimeout(function() {
            try {
                el.focus();
                oSelf._restoreScrollPositions();
            }
            catch(e) {
            }
        },0);
    }, 0);
},



















/**
 * Internal wrapper calls run() on render Chain instance.
 *
 * @method _runRenderChain
 * @private 
 */
_runRenderChain : function() {
    this._storeScrollPositions();
    this._oChainRender.run();
},

/**
 * Stores scroll positions so they can be restored after a render.
 *
 * @method _storeScrollPositions
 * @private
 */
 _storeScrollPositions : function() {
    this._nScrollTop = this._elBdContainer.scrollTop;
    this._nScrollLeft = this._elBdContainer.scrollLeft;
},

/**
 * Clears stored scroll positions to interrupt the automatic restore mechanism.
 * Useful for setting scroll positions programmatically rather than as part of
 * the post-render cleanup process.
 *
 * @method clearScrollPositions
 * @private
 */
 clearScrollPositions : function() {
    this._nScrollTop = 0;
    this._nScrollLeft = 0;
},

/**
 * Restores scroll positions to stored value. 
 *
 * @method _retoreScrollPositions
 * @private 
 */
 _restoreScrollPositions : function() {
    // Reset scroll positions
    if(this._nScrollTop) {
        this._elBdContainer.scrollTop = this._nScrollTop;
        this._nScrollTop = null;
    } 
    if(this._nScrollLeft) {
        this._elBdContainer.scrollLeft = this._nScrollLeft;
        // Bug 2529024
        this._elHdContainer.scrollLeft = this._nScrollLeft; 
        this._nScrollLeft = null;
    } 
},

/**
 * Helper function calculates and sets a validated width for a Column in a ScrollingDataTable.
 *
 * @method _validateColumnWidth
 * @param oColumn {YAHOO.widget.Column} Column instance.
 * @param elTd {HTMLElement} TD element to validate against.
 * @private
 */
_validateColumnWidth : function(oColumn, elTd) {
    // Only Columns without widths that are not hidden
    if(!oColumn.width && !oColumn.hidden) {
        var elTh = oColumn.getThEl();
        // Unset a calculated auto-width
        if(oColumn._calculatedWidth) {
            this._setColumnWidth(oColumn, "auto", "visible");
        }
        // Compare auto-widths
        if(elTh.offsetWidth !== elTd.offsetWidth) {
            var elWider = (elTh.offsetWidth > elTd.offsetWidth) ?
                    oColumn.getThLinerEl() : elTd.firstChild;               

            // Grab the wider liner width, unless the minWidth is wider
            var newWidth = Math.max(0,
                (elWider.offsetWidth -(parseInt(Dom.getStyle(elWider,"paddingLeft"),10)|0) - (parseInt(Dom.getStyle(elWider,"paddingRight"),10)|0)),
                oColumn.minWidth);
                
            var sOverflow = 'visible';
            
            // Now validate against maxAutoWidth
            if((oColumn.maxAutoWidth > 0) && (newWidth > oColumn.maxAutoWidth)) {
                newWidth = oColumn.maxAutoWidth;
                sOverflow = "hidden";
            }

            // Set to the wider auto-width
            this._elTbody.style.display = "none";
            this._setColumnWidth(oColumn, newWidth+'px', sOverflow);
            oColumn._calculatedWidth = newWidth;
            this._elTbody.style.display = "";
        }
    }
},

/**
 * For one or all Columns of a ScrollingDataTable, when Column is not hidden,
 * and width is not set, syncs widths of header and body cells and 
 * validates that width against minWidth and/or maxAutoWidth as necessary.
 *
 * @method validateColumnWidths
 * @param oArg.column {YAHOO.widget.Column} (optional) One Column to validate. If null, all Columns' widths are validated.
 */
validateColumnWidths : function(oColumn) {
    // Validate there is at least one TR with proper TDs
    var allKeys   = this._oColumnSet.keys,
        allKeysLength = allKeys.length,
        elRow     = this.getFirstTrEl();

    // Reset overhang for IE
    if(ua.ie) {
        this._setOverhangValue(1);
    }

    if(allKeys && elRow && (elRow.childNodes.length === allKeysLength)) {
        // Temporarily unsnap container since it causes inaccurate calculations
        var sWidth = this.get("width");
        if(sWidth) {
            this._elHdContainer.style.width = "";
            this._elBdContainer.style.width = "";
        }
        this._elContainer.style.width = "";
        
        //Validate just one Column
        if(oColumn && lang.isNumber(oColumn.getKeyIndex())) {
            this._validateColumnWidth(oColumn, elRow.childNodes[oColumn.getKeyIndex()]);
        }
        // Iterate through all Columns to unset calculated widths in one pass
        else {
            var elTd, todos = [], thisTodo, i, len;
            for(i=0; i<allKeysLength; i++) {
                oColumn = allKeys[i];
                // Only Columns without widths that are not hidden, unset a calculated auto-width
                if(!oColumn.width && !oColumn.hidden && oColumn._calculatedWidth) {
                    todos[todos.length] = oColumn;      
                }
            }
            
            this._elTbody.style.display = "none";
            for(i=0, len=todos.length; i<len; i++) {
                this._setColumnWidth(todos[i], "auto", "visible");
            }
            this._elTbody.style.display = "";
            
            todos = [];

            // Iterate through all Columns and make the store the adjustments to make in one pass
            for(i=0; i<allKeysLength; i++) {
                oColumn = allKeys[i];
                elTd = elRow.childNodes[i];
                // Only Columns without widths that are not hidden
                if(!oColumn.width && !oColumn.hidden) {
                    var elTh = oColumn.getThEl();

                    // Compare auto-widths
                    if(elTh.offsetWidth !== elTd.offsetWidth) {
                        var elWider = (elTh.offsetWidth > elTd.offsetWidth) ?
                                oColumn.getThLinerEl() : elTd.firstChild;               
                
                        // Grab the wider liner width, unless the minWidth is wider
                        var newWidth = Math.max(0,
                            (elWider.offsetWidth -(parseInt(Dom.getStyle(elWider,"paddingLeft"),10)|0) - (parseInt(Dom.getStyle(elWider,"paddingRight"),10)|0)),
                            oColumn.minWidth);
                            
                        var sOverflow = 'visible';
                        
                        // Now validate against maxAutoWidth
                        if((oColumn.maxAutoWidth > 0) && (newWidth > oColumn.maxAutoWidth)) {
                            newWidth = oColumn.maxAutoWidth;
                            sOverflow = "hidden";
                        }
                
                        todos[todos.length] = [oColumn, newWidth, sOverflow];
                    }
                }
            }
            
            this._elTbody.style.display = "none";
            for(i=0, len=todos.length; i<len; i++) {
                thisTodo = todos[i];
                // Set to the wider auto-width
                this._setColumnWidth(thisTodo[0], thisTodo[1]+"px", thisTodo[2]);
                thisTodo[0]._calculatedWidth = thisTodo[1];
            }
            this._elTbody.style.display = "";
        }
    
        // Resnap unsnapped containers
        if(sWidth) {
            this._elHdContainer.style.width = sWidth;
            this._elBdContainer.style.width = sWidth;
        } 
    }
    
    this._syncScroll();
    this._restoreScrollPositions();
},

/**
 * Syncs padding around scrollable tables, including Column header right-padding
 * and container width and height.
 *
 * @method _syncScroll
 * @private 
 */
_syncScroll : function() {
    this._syncScrollX();
    this._syncScrollY();
    this._syncScrollOverhang();
    if(ua.opera) {
        // Bug 1925874
        this._elHdContainer.scrollLeft = this._elBdContainer.scrollLeft;
        if(!this.get("width")) {
            // Bug 1926125
            document.body.style += '';
        }
    }
 },

/**
 * Snaps container width for y-scrolling tables.
 *
 * @method _syncScrollY
 * @private
 */
_syncScrollY : function() {
    var elTbody = this._elTbody,
        elBdContainer = this._elBdContainer;
    
    // X-scrolling not enabled
    if(!this.get("width")) {
        // Snap outer container width to content
        this._elContainer.style.width = 
                (elBdContainer.scrollHeight > elBdContainer.clientHeight) ?
                // but account for y-scrollbar since it is visible
                (elTbody.parentNode.clientWidth + 19) + "px" :
                // no y-scrollbar, just borders
                (elTbody.parentNode.clientWidth + 2) + "px";
    }
},

/**
 * Snaps container height for x-scrolling tables in IE. Syncs message TBODY width.
 *
 * @method _syncScrollX
 * @private
 */
_syncScrollX : function() {
    var elTbody = this._elTbody,
        elBdContainer = this._elBdContainer;
    
    // IE 6 and 7 only when y-scrolling not enabled
    if(!this.get("height") && (ua.ie)) {
        // Snap outer container height to content
        elBdContainer.style.height = 
                // but account for x-scrollbar if it is visible
                (elBdContainer.scrollWidth > elBdContainer.offsetWidth ) ?
                (elTbody.parentNode.offsetHeight + 18) + "px" : 
                elTbody.parentNode.offsetHeight + "px";
    }

    // Sync message tbody
    if(this._elTbody.rows.length === 0) {
        this._elMsgTbody.parentNode.style.width = this.getTheadEl().parentNode.offsetWidth + "px";
    }
    else {
        this._elMsgTbody.parentNode.style.width = "";
    }
},

/**
 * Adds/removes Column header overhang as necesary.
 *
 * @method _syncScrollOverhang
 * @private
 */
_syncScrollOverhang : function() {
    var elBdContainer = this._elBdContainer,
        // Overhang should be either 1 (default) or 18px, depending on the location of the right edge of the table
        nPadding = 1;
    
    // Y-scrollbar is visible, which is when the overhang needs to jut out
    if((elBdContainer.scrollHeight > elBdContainer.clientHeight) &&
        // X-scrollbar is also visible, which means the right is jagged, not flush with the Column
        (elBdContainer.scrollWidth > elBdContainer.clientWidth)) {
        nPadding = 18;
    }
    
    this._setOverhangValue(nPadding);
    
},

/**
 * Sets Column header overhang to given width.
 *
 * @method _setOverhangValue
 * @param nBorderWidth {Number} Value of new border for overhang. 
 * @private
 */
_setOverhangValue : function(nBorderWidth) {
    var aLastHeaders = this._oColumnSet.headers[this._oColumnSet.headers.length-1] || [],
        len = aLastHeaders.length,
        sPrefix = this._sId+"-fixedth-",
        sValue = nBorderWidth + "px solid " + this.get("COLOR_COLUMNFILLER");

    this._elThead.style.display = "none";
    for(var i=0; i<len; i++) {
        Dom.get(sPrefix+aLastHeaders[i]).style.borderRight = sValue;
    }
    this._elThead.style.display = "";
},






































/**
 * Returns DOM reference to the DataTable's fixed header container element.
 *
 * @method getHdContainerEl
 * @return {HTMLElement} Reference to DIV element.
 */
getHdContainerEl : function() {
    return this._elHdContainer;
},

/**
 * Returns DOM reference to the DataTable's scrolling body container element.
 *
 * @method getBdContainerEl
 * @return {HTMLElement} Reference to DIV element.
 */
getBdContainerEl : function() {
    return this._elBdContainer;
},

/**
 * Returns DOM reference to the DataTable's fixed header TABLE element.
 *
 * @method getHdTableEl
 * @return {HTMLElement} Reference to TABLE element.
 */
getHdTableEl : function() {
    return this._elHdTable;
},

/**
 * Returns DOM reference to the DataTable's scrolling body TABLE element.
 *
 * @method getBdTableEl
 * @return {HTMLElement} Reference to TABLE element.
 */
getBdTableEl : function() {
    return this._elTable;
},

/**
 * Disables ScrollingDataTable UI.
 *
 * @method disable
 */
disable : function() {
    var elMask = this._elMask;
    elMask.style.width = this._elBdContainer.offsetWidth + "px";
    elMask.style.height = this._elHdContainer.offsetHeight + this._elBdContainer.offsetHeight + "px";
    elMask.style.display = "";
    this.fireEvent("disableEvent");
},

/**
 * Removes given Column. NOTE: You cannot remove nested Columns. You can only remove
 * non-nested Columns, and top-level parent Columns (which will remove all
 * children Columns).
 *
 * @method removeColumn
 * @param oColumn {YAHOO.widget.Column} Column instance.
 * @return oColumn {YAHOO.widget.Column} Removed Column instance.
 */
removeColumn : function(oColumn) {
    // Store scroll pos
    var hdPos = this._elHdContainer.scrollLeft;
    var bdPos = this._elBdContainer.scrollLeft;
    
    // Call superclass method
    oColumn = SDT.superclass.removeColumn.call(this, oColumn);
    
    // Restore scroll pos
    this._elHdContainer.scrollLeft = hdPos;
    this._elBdContainer.scrollLeft = bdPos;
    
    return oColumn;
},

/**
 * Inserts given Column at the index if given, otherwise at the end. NOTE: You
 * can only add non-nested Columns and top-level parent Columns. You cannot add
 * a nested Column to an existing parent.
 *
 * @method insertColumn
 * @param oColumn {Object | YAHOO.widget.Column} Object literal Column
 * definition or a Column instance.
 * @param index {Number} (optional) New tree index.
 * @return oColumn {YAHOO.widget.Column} Inserted Column instance. 
 */
insertColumn : function(oColumn, index) {
    // Store scroll pos
    var hdPos = this._elHdContainer.scrollLeft;
    var bdPos = this._elBdContainer.scrollLeft;
    
    // Call superclass method
    var oNewColumn = SDT.superclass.insertColumn.call(this, oColumn, index);
    
    // Restore scroll pos
    this._elHdContainer.scrollLeft = hdPos;
    this._elBdContainer.scrollLeft = bdPos;
    
    return oNewColumn;
},

/**
 * Removes given Column and inserts into given tree index. NOTE: You
 * can only reorder non-nested Columns and top-level parent Columns. You cannot
 * reorder a nested Column to an existing parent.
 *
 * @method reorderColumn
 * @param oColumn {YAHOO.widget.Column} Column instance.
 * @param index {Number} New tree index.
 */
reorderColumn : function(oColumn, index) {
    // Store scroll pos
    var hdPos = this._elHdContainer.scrollLeft;
    var bdPos = this._elBdContainer.scrollLeft;
    
    // Call superclass method
    var oNewColumn = SDT.superclass.reorderColumn.call(this, oColumn, index);
    
    // Restore scroll pos
    this._elHdContainer.scrollLeft = hdPos;
    this._elBdContainer.scrollLeft = bdPos;

    return oNewColumn;
},

/**
 * Sets given Column to given pixel width. If new width is less than minWidth
 * width, sets to minWidth. Updates oColumn.width value.
 *
 * @method setColumnWidth
 * @param oColumn {YAHOO.widget.Column} Column instance.
 * @param nWidth {Number} New width in pixels.
 */
setColumnWidth : function(oColumn, nWidth) {
    oColumn = this.getColumn(oColumn);
    if(oColumn) {
        this._storeScrollPositions();

        // Validate new width against minWidth
        if(lang.isNumber(nWidth)) {
            nWidth = (nWidth > oColumn.minWidth) ? nWidth : oColumn.minWidth;

            // Save state
            oColumn.width = nWidth;
            
            // Resize the DOM elements
            this._setColumnWidth(oColumn, nWidth+"px");
            this._syncScroll();
            
            this.fireEvent("columnSetWidthEvent",{column:oColumn,width:nWidth});
            YAHOO.log("Set width of Column " + oColumn + " to " + nWidth + "px", "info", this.toString());
        }
        // Unsets a width to auto-size
        else if(nWidth === null) {
            // Save state
            oColumn.width = nWidth;
            
            // Resize the DOM elements
            this._setColumnWidth(oColumn, "auto");
            this.validateColumnWidths(oColumn);
            this.fireEvent("columnUnsetWidthEvent",{column:oColumn});
            YAHOO.log("Column " + oColumn + " width unset", "info", this.toString());
        }
        
        // Bug 2339454: resize then sort misaligment
        this._clearTrTemplateEl();
    }
    else {
        YAHOO.log("Could not set width of Column " + oColumn + " to " + nWidth + "px", "warn", this.toString());
    }
},

/**
 * Scrolls to given row or cell
 *
 * @method scrollTo
 * @param to {YAHOO.widget.Record | HTMLElement } Itme to scroll to.
 */
scrollTo : function(to) {
        var td = this.getTdEl(to);
        if(td) {
            this.clearScrollPositions();
            this.getBdContainerEl().scrollLeft = td.offsetLeft;
            this.getBdContainerEl().scrollTop = td.parentNode.offsetTop;
        }
        else {
            var tr = this.getTrEl(to);
            if(tr) {
                this.clearScrollPositions();
                this.getBdContainerEl().scrollTop = tr.offsetTop;
            }
        }
},

/**
 * Displays message within secondary TBODY.
 *
 * @method showTableMessage
 * @param sHTML {String} (optional) Value for innerHTMlang.
 * @param sClassName {String} (optional) Classname.
 */
showTableMessage : function(sHTML, sClassName) {
    var elCell = this._elMsgTd;
    if(lang.isString(sHTML)) {
        elCell.firstChild.innerHTML = sHTML;
    }
    if(lang.isString(sClassName)) {
        Dom.addClass(elCell.firstChild, sClassName);
    }

    // Needed for SDT only
    var elThead = this.getTheadEl();
    var elTable = elThead.parentNode;
    var newWidth = elTable.offsetWidth;
    this._elMsgTbody.parentNode.style.width = this.getTheadEl().parentNode.offsetWidth + "px";

    this._elMsgTbody.style.display = "";

    this.fireEvent("tableMsgShowEvent", {html:sHTML, className:sClassName});
    YAHOO.log("DataTable showing message: " + sHTML, "info", this.toString());
},













/////////////////////////////////////////////////////////////////////////////
//
// Private Custom Event Handlers
//
/////////////////////////////////////////////////////////////////////////////

/**
 * Handles Column mutations
 *
 * @method onColumnChange
 * @param oArgs {Object} Custom Event data.
 */
_onColumnChange : function(oArg) {
    // Figure out which Column changed
    var oColumn = (oArg.column) ? oArg.column :
            (oArg.editor) ? oArg.editor.column : null;
    this._storeScrollPositions();
    this.validateColumnWidths(oColumn);
},















/////////////////////////////////////////////////////////////////////////////
//
// Private DOM Event Handlers
//
/////////////////////////////////////////////////////////////////////////////

/**
 * Syncs scrolltop and scrollleft of all TABLEs.
 *
 * @method _onScroll
 * @param e {HTMLEvent} The scroll event.
 * @param oSelf {YAHOO.widget.ScrollingDataTable} ScrollingDataTable instance.
 * @private
 */
_onScroll : function(e, oSelf) {
    oSelf._elHdContainer.scrollLeft = oSelf._elBdContainer.scrollLeft;

    if(oSelf._oCellEditor && oSelf._oCellEditor.isActive) {
        oSelf.fireEvent("editorBlurEvent", {editor:oSelf._oCellEditor});
        oSelf.cancelCellEditor();
    }

    var elTarget = Ev.getTarget(e);
    var elTag = elTarget.nodeName.toLowerCase();
    oSelf.fireEvent("tableScrollEvent", {event:e, target:elTarget});
},

/**
 * Handles keydown events on the THEAD element.
 *
 * @method _onTheadKeydown
 * @param e {HTMLEvent} The key event.
 * @param oSelf {YAHOO.widget.ScrollingDataTable} ScrollingDataTable instance.
 * @private
 */
_onTheadKeydown : function(e, oSelf) {
    // If tabbing to next TH label link causes THEAD to scroll,
    // need to sync scrollLeft with TBODY
    if(Ev.getCharCode(e) === 9) {
        setTimeout(function() {
            if((oSelf instanceof SDT) && oSelf._sId) {
                oSelf._elBdContainer.scrollLeft = oSelf._elHdContainer.scrollLeft;
            }
        },0);
    }
    
    var elTarget = Ev.getTarget(e);
    var elTag = elTarget.nodeName.toLowerCase();
    var bKeepBubbling = true;
    while(elTarget && (elTag != "table")) {
        switch(elTag) {
            case "body":
                return;
            case "input":
            case "textarea":
                // TODO: implement textareaKeyEvent
                break;
            case "thead":
                bKeepBubbling = oSelf.fireEvent("theadKeyEvent",{target:elTarget,event:e});
                break;
            default:
                break;
        }
        if(bKeepBubbling === false) {
            return;
        }
        else {
            elTarget = elTarget.parentNode;
            if(elTarget) {
                elTag = elTarget.nodeName.toLowerCase();
            }
        }
    }
    oSelf.fireEvent("tableKeyEvent",{target:(elTarget || oSelf._elContainer),event:e});
}




/**
 * Fired when a fixed scrolling DataTable has a scroll.
 *
 * @event tableScrollEvent
 * @param oArgs.event {HTMLEvent} The event object.
 * @param oArgs.target {HTMLElement} The DataTable's CONTAINER element (in IE)
 * or the DataTable's TBODY element (everyone else).
 *
 */




});

})();
