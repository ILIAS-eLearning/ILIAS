/****************************************************************************/
/****************************************************************************/
/****************************************************************************/

var Dom = YAHOO.util.Dom;

/**
 * The ColumnSet class defines and manages a DataTable's Columns,
 * including nested hierarchies and access to individual Column instances.
 *
 * @namespace YAHOO.widget
 * @class ColumnSet
 * @uses YAHOO.util.EventProvider
 * @constructor
 * @param aDefinitions {Object[]} Array of object literals that define cells in
 * the THEAD.
 */
YAHOO.widget.ColumnSet = function(aDefinitions) {
    this._sId = Dom.generateId(null, "yui-cs"); // "yui-cs" + YAHOO.widget.ColumnSet._nCount;

    // First clone the defs
    aDefinitions = YAHOO.widget.DataTable._cloneObject(aDefinitions);
    this._init(aDefinitions);

    YAHOO.widget.ColumnSet._nCount++;
    YAHOO.log("ColumnSet initialized", "info", this.toString());
};

/////////////////////////////////////////////////////////////////////////////
//
// Private member variables
//
/////////////////////////////////////////////////////////////////////////////

/**
 * Internal class variable to index multiple ColumnSet instances.
 *
 * @property ColumnSet._nCount
 * @type Number
 * @private
 * @static
 */
YAHOO.widget.ColumnSet._nCount = 0;

YAHOO.widget.ColumnSet.prototype = {
    /**
     * Unique instance name.
     *
     * @property _sId
     * @type String
     * @private
     */
    _sId : null,

    /**
     * Array of object literal Column definitions passed to the constructor.
     *
     * @property _aDefinitions
     * @type Object[]
     * @private
     */
    _aDefinitions : null,

    /////////////////////////////////////////////////////////////////////////////
    //
    // Public member variables
    //
    /////////////////////////////////////////////////////////////////////////////

    /**
     * Top-down tree representation of Column hierarchy.
     *
     * @property tree
     * @type YAHOO.widget.Column[]
     */
    tree : null,

    /**
     * Flattened representation of all Columns.
     *
     * @property flat
     * @type YAHOO.widget.Column[]
     * @default []
     */
    flat : null,

    /**
     * Array of Columns that map one-to-one to a table column.
     *
     * @property keys
     * @type YAHOO.widget.Column[]
     * @default []
     */
    keys : null,

    /**
     * ID index of nested parent hierarchies for HEADERS accessibility attribute.
     *
     * @property headers
     * @type String[]
     * @default []
     */
    headers : null,

    /////////////////////////////////////////////////////////////////////////////
    //
    // Private methods
    //
    /////////////////////////////////////////////////////////////////////////////

    /**
     * Initializes ColumnSet instance with data from Column definitions.
     *
     * @method _init
     * @param aDefinitions {Object[]} Array of object literals that define cells in
     * the THEAD .
     * @private
     */

    _init : function(aDefinitions) {        
        // DOM tree representation of all Columns
        var tree = [];
        // Flat representation of all Columns
        var flat = [];
        // Flat representation of only Columns that are meant to display data
        var keys = [];
        // Array of HEADERS attribute values for all keys in the "keys" array
        var headers = [];

        // Tracks current node list depth being tracked
        var nodeDepth = -1;

        // Internal recursive function to define Column instances
        var parseColumns = function(nodeList, parent) {
            // One level down
            nodeDepth++;

            // Create corresponding tree node if not already there for this depth
            if(!tree[nodeDepth]) {
                tree[nodeDepth] = [];
            }


            // Parse each node at this depth for attributes and any children
            for(var j=0; j<nodeList.length; j++) {
                var currentNode = nodeList[j];

                // Instantiate a new Column for each node
                var oColumn = new YAHOO.widget.Column(currentNode);
                
                // Cross-reference Column ID back to the original object literal definition
                currentNode.yuiColumnId = oColumn._sId;
                
                // Add the new Column to the flat list
                flat.push(oColumn);

                // Assign its parent as an attribute, if applicable
                if(parent) {
                    oColumn._oParent = parent;
                }

                // The Column has descendants
                if(YAHOO.lang.isArray(currentNode.children)) {
                    oColumn.children = currentNode.children;

                    // Determine COLSPAN value for this Column
                    var terminalChildNodes = 0;
                    var countTerminalChildNodes = function(ancestor) {
                        var descendants = ancestor.children;
                        // Drill down each branch and count terminal nodes
                        for(var k=0; k<descendants.length; k++) {
                            // Keep drilling down
                            if(YAHOO.lang.isArray(descendants[k].children)) {
                                countTerminalChildNodes(descendants[k]);
                            }
                            // Reached branch terminus
                            else {
                                terminalChildNodes++;
                            }
                        }
                    };
                    countTerminalChildNodes(currentNode);
                    oColumn._nColspan = terminalChildNodes;

                    // Cascade certain properties to children if not defined on their own
                    var currentChildren = currentNode.children;
                    for(var k=0; k<currentChildren.length; k++) {
                        var child = currentChildren[k];
                        if(oColumn.className && (child.className === undefined)) {
                            child.className = oColumn.className;
                        }
                        if(oColumn.editor && (child.editor === undefined)) {
                            child.editor = oColumn.editor;
                        }
                        //TODO: Deprecated
                        if(oColumn.editorOptions && (child.editorOptions === undefined)) {
                            child.editorOptions = oColumn.editorOptions;
                        }
                        if(oColumn.formatter && (child.formatter === undefined)) {
                            child.formatter = oColumn.formatter;
                        }
                        if(oColumn.resizeable && (child.resizeable === undefined)) {
                            child.resizeable = oColumn.resizeable;
                        }
                        if(oColumn.sortable && (child.sortable === undefined)) {
                            child.sortable = oColumn.sortable;
                        }
                        if(oColumn.hidden) {
                            child.hidden = true;
                        }
                        if(oColumn.width && (child.width === undefined)) {
                            child.width = oColumn.width;
                        }
                        if(oColumn.minWidth && (child.minWidth === undefined)) {
                            child.minWidth = oColumn.minWidth;
                        }
                        if(oColumn.maxAutoWidth && (child.maxAutoWidth === undefined)) {
                            child.maxAutoWidth = oColumn.maxAutoWidth;
                        }
                        // Backward compatibility
                        if(oColumn.type && (child.type === undefined)) {
                            child.type = oColumn.type;
                        }
                        if(oColumn.type && !oColumn.formatter) {
                            YAHOO.log("The property type has been" +
                            " deprecated in favor of formatter", "warn", oColumn.toString());
                            oColumn.formatter = oColumn.type;
                        }
                        if(oColumn.text && !YAHOO.lang.isValue(oColumn.label)) {
                            YAHOO.log("The property text has been" +
                            " deprecated in favor of label", "warn", oColumn.toString());
                            oColumn.label = oColumn.text;
                        }
                        if(oColumn.parser) {
                            YAHOO.log("The property parser is no longer supported",
                            "warn", this.toString());
                        }
                        if(oColumn.sortOptions && ((oColumn.sortOptions.ascFunction) ||
                                (oColumn.sortOptions.descFunction))) {
                            YAHOO.log("The properties sortOptions.ascFunction and " +
                            " sortOptions.descFunction have been deprecated in favor " +
                            " of sortOptions.sortFunction", "warn", oColumn.toString());
                        }
                    }

                    // The children themselves must also be parsed for Column instances
                    if(!tree[nodeDepth+1]) {
                        tree[nodeDepth+1] = [];
                    }
                    parseColumns(currentChildren, oColumn);
                }
                // This Column does not have any children
                else {
                    oColumn._nKeyIndex = keys.length;
                    oColumn._nColspan = 1;
                    keys.push(oColumn);
                }

                // Add the Column to the top-down tree
                tree[nodeDepth].push(oColumn);
            }
            nodeDepth--;
        };

        // Parse out Column instances from the array of object literals
        if(YAHOO.lang.isArray(aDefinitions)) {
            parseColumns(aDefinitions);

            // Store the array
            this._aDefinitions = aDefinitions;
        }
        else {
            YAHOO.log("Could not initialize ColumnSet due to invalid definitions","error");
            return null;
        }

        var i;

        // Determine ROWSPAN value for each Column in the tree
        var parseTreeForRowspan = function(tree) {
            var maxRowDepth = 1;
            var currentRow;
            var currentColumn;

            // Calculate the max depth of descendants for this row
            var countMaxRowDepth = function(row, tmpRowDepth) {
                tmpRowDepth = tmpRowDepth || 1;

                for(var n=0; n<row.length; n++) {
                    var col = row[n];
                    // Column has children, so keep counting
                    if(YAHOO.lang.isArray(col.children)) {
                        tmpRowDepth++;
                        countMaxRowDepth(col.children, tmpRowDepth);
                        tmpRowDepth--;
                    }
                    // No children, is it the max depth?
                    else {
                        if(tmpRowDepth > maxRowDepth) {
                            maxRowDepth = tmpRowDepth;
                        }
                    }

                }
            };

            // Count max row depth for each row
            for(var m=0; m<tree.length; m++) {
                currentRow = tree[m];
                countMaxRowDepth(currentRow);

                // Assign the right ROWSPAN values to each Column in the row
                for(var p=0; p<currentRow.length; p++) {
                    currentColumn = currentRow[p];
                    if(!YAHOO.lang.isArray(currentColumn.children)) {
                        currentColumn._nRowspan = maxRowDepth;
                    }
                    else {
                        currentColumn._nRowspan = 1;
                    }
                }

                // Reset counter for next row
                maxRowDepth = 1;
            }
        };
        parseTreeForRowspan(tree);

        // Store tree index values
        for(i=0; i<tree[0].length; i++) {
            tree[0][i]._nTreeIndex = i;
        }

        // Store header relationships in an array for HEADERS attribute
        var recurseAncestorsForHeaders = function(i, oColumn) {
            headers[i].push(oColumn.getSanitizedKey());
            if(oColumn._oParent) {
                recurseAncestorsForHeaders(i, oColumn._oParent);
            }
        };
        for(i=0; i<keys.length; i++) {
            headers[i] = [];
            recurseAncestorsForHeaders(i, keys[i]);
            headers[i] = headers[i].reverse();
        }

        // Save to the ColumnSet instance
        this.tree = tree;
        this.flat = flat;
        this.keys = keys;
        this.headers = headers;
    },

    /////////////////////////////////////////////////////////////////////////////
    //
    // Public methods
    //
    /////////////////////////////////////////////////////////////////////////////

    /**
     * Returns unique name of the ColumnSet instance.
     *
     * @method getId
     * @return {String} Unique name of the ColumnSet instance.
     */

    getId : function() {
        return this._sId;
    },

    /**
     * ColumnSet instance name, for logging.
     *
     * @method toString
     * @return {String} Unique name of the ColumnSet instance.
     */

    toString : function() {
        return "ColumnSet instance " + this._sId;
    },

    /**
     * Public accessor to the definitions array.
     *
     * @method getDefinitions
     * @return {Object[]} Array of object literal Column definitions.
     */

    getDefinitions : function() {
        var aDefinitions = this._aDefinitions;
        
        // Internal recursive function to define Column instances
        var parseColumns = function(nodeList, oSelf) {
            // Parse each node at this depth for attributes and any children
            for(var j=0; j<nodeList.length; j++) {
                var currentNode = nodeList[j];
                
                // Get the Column for each node
                var oColumn = oSelf.getColumnById(currentNode.yuiColumnId);
                
                if(oColumn) {    
                    // Update the current values
                    var oDefinition = oColumn.getDefinition();
                    for(var name in oDefinition) {
                        if(YAHOO.lang.hasOwnProperty(oDefinition, name)) {
                            currentNode[name] = oDefinition[name];
                        }
                    }
                }
                            
                // The Column has descendants
                if(YAHOO.lang.isArray(currentNode.children)) {
                    // The children themselves must also be parsed for Column instances
                    parseColumns(currentNode.children, oSelf);
                }
            }
        };

        parseColumns(aDefinitions, this);
        this._aDefinitions = aDefinitions;
        return aDefinitions;
    },

    /**
     * Returns Column instance with given ID.
     *
     * @method getColumnById
     * @param column {String} Column ID.
     * @return {YAHOO.widget.Column} Column instance.
     */

    getColumnById : function(column) {
        if(YAHOO.lang.isString(column)) {
            var allColumns = this.flat;
            for(var i=allColumns.length-1; i>-1; i--) {
                if(allColumns[i]._sId === column) {
                    return allColumns[i];
                }
            }
        }
        return null;
    },

    /**
     * Returns Column instance with given key or ColumnSet key index.
     *
     * @method getColumn
     * @param column {String | Number} Column key or ColumnSet key index.
     * @return {YAHOO.widget.Column} Column instance.
     */

    getColumn : function(column) {
        if(YAHOO.lang.isNumber(column) && this.keys[column]) {
            return this.keys[column];
        }
        else if(YAHOO.lang.isString(column)) {
            var allColumns = this.flat;
            var aColumns = [];
            for(var i=0; i<allColumns.length; i++) {
                if(allColumns[i].key === column) {
                    aColumns.push(allColumns[i]);
                }
            }
            if(aColumns.length === 1) {
                return aColumns[0];
            }
            else if(aColumns.length > 1) {
                return aColumns;
            }
        }
        return null;
    },

    /**
     * Public accessor returns array of given Column's desendants (if any), including itself.
     *
     * @method getDescendants
     * @parem {YAHOO.widget.Column} Column instance.
     * @return {Array} Array including the Column itself and all descendants (if any).
     */
    getDescendants : function(oColumn) {
        var oSelf = this;
        var allDescendants = [];
        var i;

        // Recursive function to loop thru all children
        var parse = function(oParent) {
            allDescendants.push(oParent);
            // This Column has children
            if(oParent.children) {
                for(i=0; i<oParent.children.length; i++) {
                    parse(oSelf.getColumn(oParent.children[i].key));
                }
            }
        };
        parse(oColumn);

        return allDescendants;
    }
};

/****************************************************************************/
/****************************************************************************/
/****************************************************************************/

/**
 * The Column class defines and manages attributes of DataTable Columns
 *
 * @namespace YAHOO.widget
 * @class Column
 * @constructor
 * @param oConfigs {Object} Object literal of definitions.
 */
YAHOO.widget.Column = function(oConfigs) {
    this._sId = Dom.generateId(null, "yui-col"); // "yui-col" + YAHOO.widget.Column._nCount;
    
    // Object literal defines Column attributes
    if(oConfigs && YAHOO.lang.isObject(oConfigs)) {
        for(var sConfig in oConfigs) {
            if(sConfig) {
                this[sConfig] = oConfigs[sConfig];
            }
        }
    }

    // Assign a key if not found
    if(!YAHOO.lang.isValue(this.key)) {
        this.key = Dom.generateId(null, "yui-dt-col"); //"yui-dt-col" + YAHOO.widget.Column._nCount;
    }
    
    // Assign a field if not found, defaults to key
    if(!YAHOO.lang.isValue(this.field)) {
        this.field = this.key;
    }

    // Increment counter
    YAHOO.widget.Column._nCount++;

    // Backward compatibility
    if(this.width && !YAHOO.lang.isNumber(this.width)) {
        this.width = null;
        YAHOO.log("The Column property width must be a number", "warn", this.toString());
    }
    if(this.editor && YAHOO.lang.isString(this.editor)) {
        this.editor = new YAHOO.widget.CellEditor(this.editor, this.editorOptions);
        YAHOO.log("The Column property editor must be an instance of YAHOO.widget.CellEditor", "warn", this.toString());
    }
};

/////////////////////////////////////////////////////////////////////////////
//
// Private member variables
//
/////////////////////////////////////////////////////////////////////////////

YAHOO.lang.augmentObject(YAHOO.widget.Column, {
    /**
     * Internal class variable to index multiple Column instances.
     *
     * @property Column._nCount
     * @type Number
     * @private
     * @static
     */
    _nCount : 0,

    formatCheckbox : function(elCell, oRecord, oColumn, oData) {
        YAHOO.log("The method YAHOO.widget.Column.formatCheckbox() has been" +
        " deprecated in favor of YAHOO.widget.DataTable.formatCheckbox()", "warn",
        "YAHOO.widget.Column.formatCheckbox");
        YAHOO.widget.DataTable.formatCheckbox(elCell, oRecord, oColumn, oData);
    },

    formatCurrency : function(elCell, oRecord, oColumn, oData) {
        YAHOO.log("The method YAHOO.widget.Column.formatCurrency() has been" +
        " deprecated in favor of YAHOO.widget.DataTable.formatCurrency()", "warn",
        "YAHOO.widget.Column.formatCurrency");
        YAHOO.widget.DataTable.formatCurrency(elCell, oRecord, oColumn, oData);
    },

    formatDate : function(elCell, oRecord, oColumn, oData) {
        YAHOO.log("The method YAHOO.widget.Column.formatDate() has been" +
        " deprecated in favor of YAHOO.widget.DataTable.formatDate()", "warn",
        "YAHOO.widget.Column.formatDate");
        YAHOO.widget.DataTable.formatDate(elCell, oRecord, oColumn, oData);
    },

    formatEmail : function(elCell, oRecord, oColumn, oData) {
        YAHOO.log("The method YAHOO.widget.Column.formatEmail() has been" +
        " deprecated in favor of YAHOO.widget.DataTable.formatEmail()", "warn",
        "YAHOO.widget.Column.formatEmail");
        YAHOO.widget.DataTable.formatEmail(elCell, oRecord, oColumn, oData);
    },

    formatLink : function(elCell, oRecord, oColumn, oData) {
        YAHOO.log("The method YAHOO.widget.Column.formatLink() has been" +
        " deprecated in favor of YAHOO.widget.DataTable.formatLink()", "warn",
        "YAHOO.widget.Column.formatLink");
        YAHOO.widget.DataTable.formatLink(elCell, oRecord, oColumn, oData);
    },

    formatNumber : function(elCell, oRecord, oColumn, oData) {
        YAHOO.log("The method YAHOO.widget.Column.formatNumber() has been" +
        " deprecated in favor of YAHOO.widget.DataTable.formatNumber()", "warn",
        "YAHOO.widget.Column.formatNumber");
        YAHOO.widget.DataTable.formatNumber(elCell, oRecord, oColumn, oData);
    },

    formatSelect : function(elCell, oRecord, oColumn, oData) {
        YAHOO.log("The method YAHOO.widget.Column.formatSelect() has been" +
        " deprecated in favor of YAHOO.widget.DataTable.formatDropdown()", "warn",
        "YAHOO.widget.Column.formatSelect");
        YAHOO.widget.DataTable.formatDropdown(elCell, oRecord, oColumn, oData);
    }
});

YAHOO.widget.Column.prototype = {
    /**
     * Unique String identifier assigned at instantiation.
     *
     * @property _sId
     * @type String
     * @private
     */
    _sId : null,

    /**
     * Reference to Column's current position index within its ColumnSet's keys
     * array, if applicable. This property only applies to non-nested and bottom-
     * level child Columns.
     *
     * @property _nKeyIndex
     * @type Number
     * @private
     */
    _nKeyIndex : null,

    /**
     * Reference to Column's current position index within its ColumnSet's tree
     * array, if applicable. This property only applies to non-nested and top-
     * level parent Columns.
     *
     * @property _nTreeIndex
     * @type Number
     * @private
     */
    _nTreeIndex : null,

    /**
     * Number of table cells the Column spans.
     *
     * @property _nColspan
     * @type Number
     * @private
     */
    _nColspan : 1,

    /**
     * Number of table rows the Column spans.
     *
     * @property _nRowspan
     * @type Number
     * @private
     */
    _nRowspan : 1,

    /**
     * Column's parent Column instance, or null.
     *
     * @property _oParent
     * @type YAHOO.widget.Column
     * @private
     */
    _oParent : null,

    /**
     * The DOM reference to the associated TH element.
     *
     * @property _elTh
     * @type HTMLElement
     * @private
     */
    _elTh : null,

    /**
     * The DOM reference to the associated TH element's liner DIV element.
     *
     * @property _elThLiner
     * @type HTMLElement
     * @private
     */
    _elThLiner : null,

    /**
     * The DOM reference to the associated TH element's label SPAN element.
     *
     * @property _elThLabel
     * @type HTMLElement
     * @private
     */
    _elThLabel : null,

    /**
     * The DOM reference to the associated resizerelement (if any).
     *
     * @property _elResizer
     * @type HTMLElement
     * @private
     */
    _elResizer : null,

    /**
     * Internal width tracker.
     *
     * @property _nWidth
     * @type Number
     * @private
     */
    _nWidth : null,

    /**
     * For unreg() purposes, a reference to the Column's DragDrop instance.
     *
     * @property _dd
     * @type YAHOO.util.DragDrop
     * @private
     */
    _dd : null,

    /**
     * For unreg() purposes, a reference to the Column resizer's DragDrop instance.
     *
     * @property _ddResizer
     * @type YAHOO.util.DragDrop
     * @private
     */
    _ddResizer : null,

    /////////////////////////////////////////////////////////////////////////////
    //
    // Public member variables
    //
    /////////////////////////////////////////////////////////////////////////////

    /**
     * Unique name, required. If "label" property is not provided, the "key"
     * value will be treated as markup and inserted into the DOM as innerHTML.
     *
     * @property key
     * @type String|HTML
     */
    key : null,

    /**
     * Associated database field, or null.
     *
     * @property field
     * @type String
     */
    field : null,

    /**
     * Value displayed as Column header in the TH element. String value is
     * treated as markup and inserted into the DOM as innerHTML.
     *
     * @property label
     * @type HTML
     */
    label : null,

    /**
     * Column head cell ABBR for accessibility.
     *
     * @property abbr
     * @type String
     */
    abbr : null,

    /**
     * Array of object literals that define children (nested headers) of a Column.
     *
     * @property children
     * @type Object[]
     */
    children : null,

    /**
     * Column width (in pixels).
     *
     * @property width
     * @type Number
     */
    width : null,

    /**
     * Minimum Column width (in pixels).
     *
     * @property minWidth
     * @type Number
     * @default null
     */
    minWidth : null,

    /**
     * When a width is not defined for a Column, maxAutoWidth defines an upper
     * limit that the Column should be auto-sized to. If resizeable is enabled, 
     * users may still resize to a greater width. Most useful for Columns intended
     * to hold long unbroken, unwrapped Strings, such as URLs, to prevent very
     * wide Columns from disrupting visual readability by inducing truncation.
     *
     * @property maxAutoWidth
     * @type Number
     * @default null
     */
    maxAutoWidth : null,

    /**
     * True if Column is in hidden state.
     *
     * @property hidden
     * @type Boolean
     * @default false     
     */
    hidden : false,

    /**
     * True if Column is in selected state.
     *
     * @property selected
     * @type Boolean
     * @default false     
     */
    selected : false,

    /**
     * Custom CSS class or array of classes to be applied to every cell in the Column.
     *
     * @property className
     * @type String || String[]
     */
    className : null,

    /**
     * Cell formatter function, or a shortcut pointer to a function in the
     * DataTable.Formatter object. The function, called from the DataTable's
     * formatCell method, renders markup into the cell liner
     * element and accepts the following arguments:
     * <dl>
     *    <dt>elLiner</dt>
     *    <dd>The element to write innerHTML to.</dd>
     *    <dt>oRecord</dt>
     *    <dd>The associated Record for the row.</dd>
     *    <dt>oColumn</dt>
     *    <dd>The Column instance for the cell.</dd>
     *    <dt>oData</dt>
     *    <dd>The data value for the cell.</dd>
     * </dl>
     *
     * @property formatter
     * @type String || HTMLFunction
     */
    formatter : null,
    
    /**
     * Config passed to YAHOO.util.Number.format() by the 'currency' Column formatter.
     *
     * @property currencyOptions
     * @type Object
     * @default null
     */
    currencyOptions : null,

    /**
     * Config passed to YAHOO.util.Date.format() by the 'date' Column formatter.
     *
     * @property dateOptions
     * @type Object
     * @default null
     */
    dateOptions : null,

    /**
     * Array of dropdown values for formatter:"dropdown" cases. Can either be a
     * simple array (e.g., ["Alabama","Alaska","Arizona","Arkansas"]) or a an
     * array of objects (e.g., [{label:"Alabama", value:"AL"},
     * {label:"Alaska", value:"AK"}, {label:"Arizona", value:"AZ"},
     * {label:"Arkansas", value:"AR"}]). String values are treated as markup and
     * inserted into the DOM as innerHTML.
     *
     * @property dropdownOptions
     * @type HTML[] | Object[]
     */
    dropdownOptions : null,
     
    /**
     * A CellEditor instance, otherwise Column is not editable.     
     *
     * @property editor
     * @type YAHOO.widget.CellEditor
     */
    editor : null,

    /**
     * True if Column is resizeable, false otherwise. The Drag & Drop Utility is
     * required to enable this feature. Only bottom-level and non-nested Columns are
     * resizeble. 
     *
     * @property resizeable
     * @type Boolean
     * @default false
     */
    resizeable : false,

    /**
     * True if Column is sortable, false otherwise.
     *
     * @property sortable
     * @type Boolean
     * @default false
     */
    sortable : false,

    /**
     * @property sortOptions.defaultOrder
     * @deprecated Use sortOptions.defaultDir.
     */
    /**
     * Default sort direction for Column: YAHOO.widget.DataTable.CLASS_ASC or YAHOO.widget.DataTable.CLASS_DESC.
     *
     * @property sortOptions.defaultDir
     * @type String
     * @default null
     */
    /**
     * Custom field to sort on.
     *
     * @property sortOptions.field
     * @type String
     * @default null
     */
    /**
     * Custom sort handler. Signature: sortFunction(a, b, desc, field) where field is the sortOptions.field value
     *
     * @property sortOptions.sortFunction
     * @type Function
     * @default null
     */
    sortOptions : null,















    /////////////////////////////////////////////////////////////////////////////
    //
    // Public methods
    //
    /////////////////////////////////////////////////////////////////////////////

    /**
     * Returns unique ID string.
     *
     * @method getId
     * @return {String} Unique ID string.
     */
    getId : function() {
        return this._sId;
    },

    /**
     * Column instance name, for logging.
     *
     * @method toString
     * @return {String} Column's unique name.
     */
    toString : function() {
        return "Column instance " + this._sId;
    },

    /**
     * Returns object literal definition.
     *
     * @method getDefinition
     * @return {Object} Object literal definition.
     */
    getDefinition : function() {
        var oDefinition = {};
        
        // Update the definition
        oDefinition.abbr = this.abbr;
        oDefinition.className = this.className;
        oDefinition.editor = this.editor;
        oDefinition.editorOptions = this.editorOptions; //TODO: deprecated
        oDefinition.field = this.field;
        oDefinition.formatter = this.formatter;
        oDefinition.hidden = this.hidden;
        oDefinition.key = this.key;
        oDefinition.label = this.label;
        oDefinition.minWidth = this.minWidth;
        oDefinition.maxAutoWidth = this.maxAutoWidth;
        oDefinition.resizeable = this.resizeable;
        oDefinition.selected = this.selected;
        oDefinition.sortable = this.sortable;
        oDefinition.sortOptions = this.sortOptions;
        oDefinition.width = this.width;
        
        // Bug 2529147
        oDefinition._calculatedWidth = this._calculatedWidth;

        return oDefinition;
    },

    /**
     * Returns unique Column key.
     *
     * @method getKey
     * @return {String} Column key.
     */
    getKey : function() {
        return this.key;
    },
    
    /**
     * Returns field.
     *
     * @method getField
     * @return {String} Column field.
     */
    getField : function() {
        return this.field;
    },
    
    /**
     * Returns Column key which has been sanitized for DOM (class and ID) usage
     * starts with letter, contains only letters, numbers, hyphen, or period.
     *
     * @method getSanitizedKey
     * @return {String} Sanitized Column key.
     */
    getSanitizedKey : function() {
        return this.getKey().replace(/[^\w\-]/g,"");
    },

    /**
     * Public accessor returns Column's current position index within its
     * ColumnSet's keys array, if applicable. Only non-nested and bottom-level
     * child Columns will return a value.
     *
     * @method getKeyIndex
     * @return {Number} Position index, or null.
     */
    getKeyIndex : function() {
        return this._nKeyIndex;
    },

    /**
     * Public accessor returns Column's current position index within its
     * ColumnSet's tree array, if applicable. Only non-nested and top-level parent
     * Columns will return a value;
     *
     * @method getTreeIndex
     * @return {Number} Position index, or null.
     */
    getTreeIndex : function() {
        return this._nTreeIndex;
    },

    /**
     * Public accessor returns Column's parent instance if any, or null otherwise.
     *
     * @method getParent
     * @return {YAHOO.widget.Column} Column's parent instance.
     */
    getParent : function() {
        return this._oParent;
    },

    /**
     * Public accessor returns Column's calculated COLSPAN value.
     *
     * @method getColspan
     * @return {Number} Column's COLSPAN value.
     */
    getColspan : function() {
        return this._nColspan;
    },
    // Backward compatibility
    getColSpan : function() {
        YAHOO.log("The method getColSpan() has been" +
        " deprecated in favor of getColspan()", "warn", this.toString());
        return this.getColspan();
    },

    /**
     * Public accessor returns Column's calculated ROWSPAN value.
     *
     * @method getRowspan
     * @return {Number} Column's ROWSPAN value.
     */
    getRowspan : function() {
        return this._nRowspan;
    },

    /**
     * Returns DOM reference to the key TH element.
     *
     * @method getThEl
     * @return {HTMLElement} TH element.
     */
    getThEl : function() {
        return this._elTh;
    },

    /**
     * Returns DOM reference to the TH's liner DIV element. Introduced since
     * resizeable Columns may have an extra resizer liner, making the DIV liner
     * not reliably the TH element's first child.               
     *
     * @method getThLInerEl
     * @return {HTMLElement} TH element.
     */
    getThLinerEl : function() {
        return this._elThLiner;
    },
    
    /**
     * Returns DOM reference to the resizer element, or null.
     *
     * @method getResizerEl
     * @return {HTMLElement} DIV element.
     */
    getResizerEl : function() {
        return this._elResizer;
    },

    // Backward compatibility
    /**
     * @method getColEl
     * @deprecated Use getThEl
     */
    getColEl : function() {
        YAHOO.log("The method getColEl() has been" +
        " deprecated in favor of getThEl()", "warn",
        this.toString());
        return this.getThEl();
    },
    getIndex : function() {
        YAHOO.log("The method getIndex() has been" +
        " deprecated in favor of getKeyIndex()", "warn",
        this.toString());
        return this.getKeyIndex();
    },
    format : function() {
        YAHOO.log("The method format() has been deprecated in favor of the " +
        "DataTable method formatCell()", "error", this.toString());
    }
};

/****************************************************************************/
/****************************************************************************/
/****************************************************************************/

/**
 * Sort static utility to support Column sorting.
 *
 * @namespace YAHOO.util
 * @class Sort
 * @static
 */
YAHOO.util.Sort = {
    /////////////////////////////////////////////////////////////////////////////
    //
    // Public methods
    //
    /////////////////////////////////////////////////////////////////////////////

    /**
     * Comparator function for simple case-insensitive string sorting.
     *
     * @method compare
     * @param a {Object} First sort argument.
     * @param b {Object} Second sort argument.
     * @param desc {Boolean} True if sort direction is descending, false if
     * sort direction is ascending.
     * @return {Boolean} Return -1 when a < b. Return 0 when a = b.
     * Return 1 when a > b.
     */
    compare: function(a, b, desc) {
        if((a === null) || (typeof a == "undefined")) {
            if((b === null) || (typeof b == "undefined")) {
                return 0;
            }
            else {
                return 1;
            }
        }
        else if((b === null) || (typeof b == "undefined")) {
            return -1;
        }

        if(a.constructor == String) {
            a = a.toLowerCase();
        }
        if(b.constructor == String) {
            b = b.toLowerCase();
        }
        if(a < b) {
            return (desc) ? 1 : -1;
        }
        else if (a > b) {
            return (desc) ? -1 : 1;
        }
        else {
            return 0;
        }
    }
};

/****************************************************************************/
/****************************************************************************/
/****************************************************************************/

/**
 * ColumnDD subclasses DragDrop to support rearrangeable Columns.
 *
 * @namespace YAHOO.util
 * @class ColumnDD
 * @extends YAHOO.util.DDProxy
 * @constructor
 * @param oDataTable {YAHOO.widget.DataTable} DataTable instance.
 * @param oColumn {YAHOO.widget.Column} Column instance.
 * @param elTh {HTMLElement} TH element reference.
 * @param elTarget {HTMLElement} Drag target element.
 */
YAHOO.widget.ColumnDD = function(oDataTable, oColumn, elTh, elTarget) {
    if(oDataTable && oColumn && elTh && elTarget) {
        this.datatable = oDataTable;
        this.table = oDataTable.getTableEl();
        this.column = oColumn;
        this.headCell = elTh;
        this.pointer = elTarget;
        this.newIndex = null;
        this.init(elTh);
        this.initFrame(); // Needed for DDProxy
        this.invalidHandleTypes = {};

        // Set top/bottom padding to account for children of nested columns
        this.setPadding(10, 0, (this.datatable.getTheadEl().offsetHeight + 10) , 0);

        YAHOO.util.Event.on(window, 'resize', function() {
            this.initConstraints();
        }, this, true);
    }
    else {
        YAHOO.log("Column dragdrop could not be created","warn",oDataTable.toString());
    }
};

if(YAHOO.util.DDProxy) {
    YAHOO.extend(YAHOO.widget.ColumnDD, YAHOO.util.DDProxy, {
        initConstraints: function() {
            //Get the top, right, bottom and left positions
            var region = YAHOO.util.Dom.getRegion(this.table),
                //Get the element we are working on
                el = this.getEl(),
                //Get the xy position of it
                xy = YAHOO.util.Dom.getXY(el),
                //Get the width and height
                width = parseInt(YAHOO.util.Dom.getStyle(el, 'width'), 10),
                height = parseInt(YAHOO.util.Dom.getStyle(el, 'height'), 10),
                //Set left to x minus left
                left = ((xy[0] - region.left) + 15), //Buffer of 15px
                //Set right to right minus x minus width
                right = ((region.right - xy[0] - width) + 15);
    
            //Set the constraints based on the above calculations
            this.setXConstraint(left, right);
            this.setYConstraint(10, 10);            
        },
        _resizeProxy: function() {
            YAHOO.widget.ColumnDD.superclass._resizeProxy.apply(this, arguments);
            var dragEl = this.getDragEl(),
                el = this.getEl();

            YAHOO.util.Dom.setStyle(this.pointer, 'height', (this.table.parentNode.offsetHeight + 10) + 'px');
            YAHOO.util.Dom.setStyle(this.pointer, 'display', 'block');
            var xy = YAHOO.util.Dom.getXY(el);
            YAHOO.util.Dom.setXY(this.pointer, [xy[0], (xy[1] - 5)]);
            
            YAHOO.util.Dom.setStyle(dragEl, 'height', this.datatable.getContainerEl().offsetHeight + "px");
            YAHOO.util.Dom.setStyle(dragEl, 'width', (parseInt(YAHOO.util.Dom.getStyle(dragEl, 'width'),10) + 4) + 'px');
            YAHOO.util.Dom.setXY(this.dragEl, xy);
        },
        onMouseDown: function() {
                this.initConstraints();
                this.resetConstraints();
        },
        clickValidator: function(e) {
            if(!this.column.hidden) {
                var target = YAHOO.util.Event.getTarget(e);
                return ( this.isValidHandleChild(target) &&
                            (this.id == this.handleElId ||
                                this.DDM.handleWasClicked(target, this.id)) );
            }
        },
        onDragOver: function(ev, id) {
            // Validate target as a Column
            var target = this.datatable.getColumn(id);
            if(target) {                
                // Validate target as a top-level parent
                var targetIndex = target.getTreeIndex();
                while((targetIndex === null) && target.getParent()) {
                    target = target.getParent();
                    targetIndex = target.getTreeIndex();
                }
                if(targetIndex !== null) {
                    // Are we placing to left or right of target?
                    var elTarget = target.getThEl();
                    var newIndex = targetIndex;
                    var mouseX = YAHOO.util.Event.getPageX(ev),
                        targetX = YAHOO.util.Dom.getX(elTarget),
                        midX = targetX + ((YAHOO.util.Dom.get(elTarget).offsetWidth)/2),
                        currentIndex =  this.column.getTreeIndex();
                    
                    if (mouseX < midX) {
                       YAHOO.util.Dom.setX(this.pointer, targetX);
                    } else {
                        var targetWidth = parseInt(elTarget.offsetWidth, 10);
                        YAHOO.util.Dom.setX(this.pointer, (targetX + targetWidth));
                        newIndex++;
                    }
                    if (targetIndex > currentIndex) {
                        newIndex--;
                    }
                    if(newIndex < 0) {
                        newIndex = 0;
                    }
                    else if(newIndex > this.datatable.getColumnSet().tree[0].length) {
                        newIndex = this.datatable.getColumnSet().tree[0].length;
                    }
                    this.newIndex = newIndex;
                }
            }
        },
        onDragDrop: function() {
            this.datatable.reorderColumn(this.column, this.newIndex);
        },
        endDrag: function() {
            this.newIndex = null;
            YAHOO.util.Dom.setStyle(this.pointer, 'display', 'none');
        }
    });
}

/****************************************************************************/
/****************************************************************************/
/****************************************************************************/

/**
 * ColumnResizer subclasses DragDrop to support resizeable Columns.
 *
 * @namespace YAHOO.util
 * @class ColumnResizer
 * @extends YAHOO.util.DDProxy
 * @constructor
 * @param oDataTable {YAHOO.widget.DataTable} DataTable instance.
 * @param oColumn {YAHOO.widget.Column} Column instance.
 * @param elTh {HTMLElement} TH element reference.
 * @param sHandleElId {String} DOM ID of the handle element that causes the resize.
 * @param elProxy {HTMLElement} Resizer proxy element.
 */
YAHOO.util.ColumnResizer = function(oDataTable, oColumn, elTh, sHandleId, elProxy) {
    if(oDataTable && oColumn && elTh && sHandleId) {
        this.datatable = oDataTable;
        this.column = oColumn;
        this.headCell = elTh;
        this.headCellLiner = oColumn.getThLinerEl();
        this.resizerLiner = elTh.firstChild;
        this.init(sHandleId, sHandleId, {dragOnly:true, dragElId: elProxy.id});
        this.initFrame(); // Needed for proxy
        this.resetResizerEl(); // Needed when rowspan > 0

        // Set right padding for bug 1858462
        this.setPadding(0, 1, 0, 0);
    }
    else {
        YAHOO.log("Column resizer could not be created","warn",oDataTable.toString());
    }
};

if(YAHOO.util.DD) {
    YAHOO.extend(YAHOO.util.ColumnResizer, YAHOO.util.DDProxy, {
        /////////////////////////////////////////////////////////////////////////////
        //
        // Public methods
        //
        /////////////////////////////////////////////////////////////////////////////
        /**
         * Resets resizer element.
         *
         * @method resetResizerEl
         */
        resetResizerEl : function() {
            var resizerStyle = YAHOO.util.Dom.get(this.handleElId).style;
            resizerStyle.left = "auto";
            resizerStyle.right = 0;
            resizerStyle.top = "auto";
            resizerStyle.bottom = 0;
            resizerStyle.height = this.headCell.offsetHeight+"px";
        },
    
        /////////////////////////////////////////////////////////////////////////////
        //
        // Public DOM event handlers
        //
        /////////////////////////////////////////////////////////////////////////////
    
        /**
         * Handles mouseup events on the Column resizer.
         *
         * @method onMouseUp
         * @param e {string} The mouseup event
         */
        onMouseUp : function(e) {
            // Reset height of all resizer els in case TH's have changed height
            var allKeys = this.datatable.getColumnSet().keys,
                col;
            for(var i=0, len=allKeys.length; i<len; i++) {
                col = allKeys[i];
                if(col._ddResizer) {
                    col._ddResizer.resetResizerEl();
                }
            }
            this.resetResizerEl();
            
            var el = this.headCellLiner;
            var newWidth = el.offsetWidth -
                (parseInt(YAHOO.util.Dom.getStyle(el,"paddingLeft"),10)|0) -
                (parseInt(YAHOO.util.Dom.getStyle(el,"paddingRight"),10)|0);

            this.datatable.fireEvent("columnResizeEvent", {column:this.column,target:this.headCell,width:newWidth});
        },
    
        /**
         * Handles mousedown events on the Column resizer.
         *
         * @method onMouseDown
         * @param e {string} The mousedown event
         */
        onMouseDown : function(e) {
            this.startWidth = this.headCellLiner.offsetWidth;
            this.startX = YAHOO.util.Event.getXY(e)[0];
            this.nLinerPadding = (parseInt(YAHOO.util.Dom.getStyle(this.headCellLiner,"paddingLeft"),10)|0) +
                    (parseInt(YAHOO.util.Dom.getStyle(this.headCellLiner,"paddingRight"),10)|0);
        },
    
        /**
         * Custom clickValidator to ensure Column is not in hidden state.
         *
         * @method clickValidator
         * @param {Event} e
         * @private
         */
        clickValidator : function(e) {
            if(!this.column.hidden) {
                var target = YAHOO.util.Event.getTarget(e);
                return ( this.isValidHandleChild(target) &&
                            (this.id == this.handleElId ||
                                this.DDM.handleWasClicked(target, this.id)) );
            }
        },
    
        /**
         * Handles start drag on the Column resizer.
         *
         * @method startDrag
         * @param e {string} The drag event
         */
        startDrag : function() {
            // Shrinks height of all resizer els to not hold open TH els
            var allKeys = this.datatable.getColumnSet().keys,
                thisKey = this.column.getKeyIndex(),
                col;
            for(var i=0, len=allKeys.length; i<len; i++) {
                col = allKeys[i];
                if(col._ddResizer) {
                    YAHOO.util.Dom.get(col._ddResizer.handleElId).style.height = "1em";
                }
            }
        },

        /**
         * Handles drag events on the Column resizer.
         *
         * @method onDrag
         * @param e {string} The drag event
         */
        onDrag : function(e) {
            var newX = YAHOO.util.Event.getXY(e)[0];
            if(newX > YAHOO.util.Dom.getX(this.headCellLiner)) {
                var offsetX = newX - this.startX;
                var newWidth = this.startWidth + offsetX - this.nLinerPadding;
                if(newWidth > 0) {
                    this.datatable.setColumnWidth(this.column, newWidth);
                }
            }
        }
    });
}

/////////////////////////////////////////////////////////////////////////////
//
// Deprecated
//
/////////////////////////////////////////////////////////////////////////////

/**
 * @property editorOptions
 * @deprecated Pass configs directly to CellEditor constructor. 
 */

