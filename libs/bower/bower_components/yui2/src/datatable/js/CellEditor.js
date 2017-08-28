(function () {

var lang   = YAHOO.lang,
    util   = YAHOO.util,
    widget = YAHOO.widget,
    ua     = YAHOO.env.ua,
    
    Dom    = util.Dom,
    Ev     = util.Event,
    
    DT     = widget.DataTable;
/****************************************************************************/
/****************************************************************************/
/****************************************************************************/
    
/**
 * The BaseCellEditor class provides base functionality common to all inline cell
 * editors for a DataTable widget.
 *
 * @namespace YAHOO.widget
 * @class BaseCellEditor
 * @uses YAHOO.util.EventProvider 
 * @constructor
 * @param sType {String} Type indicator, to map to YAHOO.widget.DataTable.Editors.
 * @param oConfigs {Object} (Optional) Object literal of configs.
 */
widget.BaseCellEditor = function(sType, oConfigs) {
    this._sId = this._sId || Dom.generateId(null, "yui-ceditor"); // "yui-ceditor" + YAHOO.widget.BaseCellEditor._nCount++;
    YAHOO.widget.BaseCellEditor._nCount++;
    this._sType = sType;
    
    // Validate inputs
    this._initConfigs(oConfigs); 
    
    // Create Custom Events
    this._initEvents();
             
    // UI needs to be drawn
    this._needsRender = true;
};

var BCE = widget.BaseCellEditor;

/////////////////////////////////////////////////////////////////////////////
//
// Static members
//
/////////////////////////////////////////////////////////////////////////////
lang.augmentObject(BCE, {

/**
 * Global instance counter.
 *
 * @property CellEditor._nCount
 * @type Number
 * @static
 * @default 0
 * @private 
 */
_nCount : 0,

/**
 * Class applied to CellEditor container.
 *
 * @property CellEditor.CLASS_CELLEDITOR
 * @type String
 * @static
 * @default "yui-ceditor"
 */
CLASS_CELLEDITOR : "yui-ceditor"

});

BCE.prototype = {
/////////////////////////////////////////////////////////////////////////////
//
// Private members
//
/////////////////////////////////////////////////////////////////////////////
/**
 * Unique id assigned to instance "yui-ceditorN", useful prefix for generating unique
 * DOM ID strings and log messages.
 *
 * @property _sId
 * @type String
 * @private
 */
_sId : null,

/**
 * Editor type.
 *
 * @property _sType
 * @type String
 * @private
 */
_sType : null,

/**
 * DataTable instance.
 *
 * @property _oDataTable
 * @type YAHOO.widget.DataTable
 * @private 
 */
_oDataTable : null,

/**
 * Column instance.
 *
 * @property _oColumn
 * @type YAHOO.widget.Column
 * @default null
 * @private 
 */
_oColumn : null,

/**
 * Record instance.
 *
 * @property _oRecord
 * @type YAHOO.widget.Record
 * @default null
 * @private 
 */
_oRecord : null,

/**
 * TD element.
 *
 * @property _elTd
 * @type HTMLElement
 * @default null
 * @private
 */
_elTd : null,

/**
 * Container for inline editor.
 *
 * @property _elContainer
 * @type HTMLElement
 * @private 
 */
_elContainer : null,

/**
 * Reference to Cancel button, if available.
 *
 * @property _elCancelBtn
 * @type HTMLElement
 * @default null
 * @private 
 */
_elCancelBtn : null,

/**
 * Reference to Save button, if available.
 *
 * @property _elSaveBtn
 * @type HTMLElement
 * @default null
 * @private 
 */
_elSaveBtn : null,








/////////////////////////////////////////////////////////////////////////////
//
// Private methods
//
/////////////////////////////////////////////////////////////////////////////

/**
 * Initialize configs.
 *
 * @method _initConfigs
 * @private   
 */
_initConfigs : function(oConfigs) {
    // Object literal defines CellEditor configs
    if(oConfigs && YAHOO.lang.isObject(oConfigs)) {
        for(var sConfig in oConfigs) {
            if(sConfig) {
                this[sConfig] = oConfigs[sConfig];
            }
        }
    }
},

/**
 * Initialize Custom Events.
 *
 * @method _initEvents
 * @private   
 */
_initEvents : function() {
    this.createEvent("showEvent");
    this.createEvent("keydownEvent");
    this.createEvent("invalidDataEvent");
    this.createEvent("revertEvent");
    this.createEvent("saveEvent");
    this.createEvent("cancelEvent");
    this.createEvent("blurEvent");
    this.createEvent("blockEvent");
    this.createEvent("unblockEvent");
},

/**
 * Initialize container element.
 *
 * @method _initContainerEl
 * @private
 */
_initContainerEl : function() {
    if(this._elContainer) {
        YAHOO.util.Event.purgeElement(this._elContainer, true);
        this._elContainer.innerHTML = "";
    }

    var elContainer = document.createElement("div");
    elContainer.id = this.getId() + "-container"; // Needed for tracking blur event
    elContainer.style.display = "none";
    elContainer.tabIndex = 0;
    
    this.className = lang.isArray(this.className) ? this.className : this.className ? [this.className] : [];
    this.className[this.className.length] = DT.CLASS_EDITOR;
    elContainer.className = this.className.join(" ");
    
    document.body.insertBefore(elContainer, document.body.firstChild);
    this._elContainer = elContainer;
},

/**
 * Initialize container shim element.
 *
 * @method _initShimEl
 * @private
 */
_initShimEl : function() {
    // Iframe shim
    if(this.useIFrame) {
        if(!this._elIFrame) {
            var elIFrame = document.createElement("iframe");
            elIFrame.src = "javascript:false";
            elIFrame.frameBorder = 0;
            elIFrame.scrolling = "no";
            elIFrame.style.display = "none";
            elIFrame.className = DT.CLASS_EDITOR_SHIM;
            elIFrame.tabIndex = -1;
            elIFrame.role = "presentation";
            elIFrame.title = "Presentational iframe shim";
            document.body.insertBefore(elIFrame, document.body.firstChild);
            this._elIFrame = elIFrame;
        }
    }
},

/**
 * Hides CellEditor UI at end of interaction.
 *
 * @method _hide
 */
_hide : function() {
    this.getContainerEl().style.display = "none";
    if(this._elIFrame) {
        this._elIFrame.style.display = "none";
    }
    this.isActive = false;
    this.getDataTable()._oCellEditor =  null;
},











/////////////////////////////////////////////////////////////////////////////
//
// Public properties
//
/////////////////////////////////////////////////////////////////////////////
/**
 * Implementer defined function that can submit the input value to a server. This
 * function must accept the arguments fnCallback and oNewValue. When the submission
 * is complete, the function must also call fnCallback(bSuccess, oNewValue) to 
 * finish the save routine in the CellEditor. This function can also be used to 
 * perform extra validation or input value manipulation. 
 *
 * @property asyncSubmitter
 * @type HTMLFunction
 */
asyncSubmitter : null,

/**
 * Current value.
 *
 * @property value
 * @type MIXED
 */
value : null,

/**
 * Default value in case Record data is undefined. NB: Null values will not trigger
 * the default value.
 *
 * @property defaultValue
 * @type MIXED
 * @default null
 */
defaultValue : null,

/**
 * Validator function for input data, called from the DataTable instance scope,
 * receives the arguments (inputValue, currentValue, editorInstance) and returns
 * either the validated (or type-converted) value or undefined.
 *
 * @property validator
 * @type HTMLFunction
 * @default null
 */
validator : null,

/**
 * If validation is enabled, resets input field of invalid data.
 *
 * @property resetInvalidData
 * @type Boolean
 * @default true
 */
resetInvalidData : true,

/**
 * True if currently active.
 *
 * @property isActive
 * @type Boolean
 */
isActive : false,

/**
 * Text to display on Save button.
 *
 * @property LABEL_SAVE
 * @type HTML
 * @default "Save"
 */
LABEL_SAVE : "Save",

/**
 * Text to display on Cancel button.
 *
 * @property LABEL_CANCEL
 * @type HTML
 * @default "Cancel"
 */
LABEL_CANCEL : "Cancel",

/**
 * True if Save/Cancel buttons should not be displayed in the CellEditor.
 *
 * @property disableBtns
 * @type Boolean
 * @default false
 */
disableBtns : false,

/**
 * True if iframe shim for container element should be enabled.
 *
 * @property useIFrame
 * @type Boolean
 * @default false
 */
useIFrame : false,

/**
 * Custom CSS class or array of classes applied to the container element.
 *
 * @property className
 * @type String || String[]
 */
className : null,





/////////////////////////////////////////////////////////////////////////////
//
// Public methods
//
/////////////////////////////////////////////////////////////////////////////
/**
 * CellEditor instance name, for logging.
 *
 * @method toString
 * @return {String} Unique name of the CellEditor instance.
 */

toString : function() {
    return "CellEditor instance " + this._sId;
},

/**
 * CellEditor unique ID.
 *
 * @method getId
 * @return {String} Unique ID of the CellEditor instance.
 */

getId : function() {
    return this._sId;
},

/**
 * Returns reference to associated DataTable instance.
 *
 * @method getDataTable
 * @return {YAHOO.widget.DataTable} DataTable instance.
 */

getDataTable : function() {
    return this._oDataTable;
},

/**
 * Returns reference to associated Column instance.
 *
 * @method getColumn
 * @return {YAHOO.widget.Column} Column instance.
 */

getColumn : function() {
    return this._oColumn;
},

/**
 * Returns reference to associated Record instance.
 *
 * @method getRecord
 * @return {YAHOO.widget.Record} Record instance.
 */

getRecord : function() {
    return this._oRecord;
},



/**
 * Returns reference to associated TD element.
 *
 * @method getTdEl
 * @return {HTMLElement} TD element.
 */

getTdEl : function() {
    return this._elTd;
},

/**
 * Returns container element.
 *
 * @method getContainerEl
 * @return {HTMLElement} Reference to container element.
 */

getContainerEl : function() {
    return this._elContainer;
},

/**
 * Nulls out the entire CellEditor instance and related objects, removes attached
 * event listeners, and clears out DOM elements inside the container, removes
 * container from the DOM.
 *
 * @method destroy
 */
destroy : function() {
    this.unsubscribeAll();
    
    // Column is late-binding in attach()
    var oColumn = this.getColumn();
    if(oColumn) {
        oColumn.editor = null;
    }
    
    var elContainer = this.getContainerEl();
    if (elContainer) {
        Ev.purgeElement(elContainer, true);
        elContainer.parentNode.removeChild(elContainer);
    }
},

/**
 * Renders DOM elements and attaches event listeners.
 *
 * @method render
 */
render : function() {
    if (!this._needsRender) {
        return;
    }

    this._initContainerEl();
    this._initShimEl();

    // Handle ESC key
    Ev.addListener(this.getContainerEl(), "keydown", function(e, oSelf) {
        // ESC cancels Cell Editor
        if((e.keyCode == 27)) {
            var target = Ev.getTarget(e);
            // workaround for Mac FF3 bug that disabled clicks when ESC hit when
            // select is open. [bug 2273056]
            if (target.nodeName && target.nodeName.toLowerCase() === 'select') {
                target.blur();
            }
            oSelf.cancel();
        }
        // Pass through event
        oSelf.fireEvent("keydownEvent", {editor:oSelf, event:e});
    }, this);

    this.renderForm();

    // Show Save/Cancel buttons
    if(!this.disableBtns) {
        this.renderBtns();
    }
    
    this.doAfterRender();
    this._needsRender = false;
},

/**
 * Renders Save/Cancel buttons.
 *
 * @method renderBtns
 */
renderBtns : function() {
    // Buttons
    var elBtnsDiv = this.getContainerEl().appendChild(document.createElement("div"));
    elBtnsDiv.className = DT.CLASS_BUTTON;

    // Save button
    var elSaveBtn = elBtnsDiv.appendChild(document.createElement("button"));
    elSaveBtn.className = DT.CLASS_DEFAULT;
    elSaveBtn.innerHTML = this.LABEL_SAVE;
    Ev.addListener(elSaveBtn, "click", function(oArgs) {
        this.save();
    }, this, true);
    this._elSaveBtn = elSaveBtn;

    // Cancel button
    var elCancelBtn = elBtnsDiv.appendChild(document.createElement("button"));
    elCancelBtn.innerHTML = this.LABEL_CANCEL;
    Ev.addListener(elCancelBtn, "click", function(oArgs) {
        this.cancel();
    }, this, true);
    this._elCancelBtn = elCancelBtn;
},

/**
 * Attach CellEditor for a new interaction.
 *
 * @method attach
 * @param oDataTable {YAHOO.widget.DataTable} Associated DataTable instance.
 * @param elCell {HTMLElement} Cell to edit.  
 */
attach : function(oDataTable, elCell) {
    // Validate 
    if(oDataTable instanceof YAHOO.widget.DataTable) {
        this._oDataTable = oDataTable;
        
        // Validate cell
        elCell = oDataTable.getTdEl(elCell);
        if(elCell) {
            this._elTd = elCell;

            // Validate Column
            var oColumn = oDataTable.getColumn(elCell);
            if(oColumn) {
                this._oColumn = oColumn;
                
                // Validate Record
                var oRecord = oDataTable.getRecord(elCell);
                if(oRecord) {
                    this._oRecord = oRecord;
                    var value = oRecord.getData(this.getColumn().getField());
                    this.value = (value !== undefined) ? value : this.defaultValue;
                    return true;
                }
            }            
        }
    }
    YAHOO.log("Could not attach CellEditor","error",this.toString());
    return false;
},

/**
 * Moves container into position for display.
 *
 * @method move
 */
move : function() {
    // Move Editor
    var elContainer = this.getContainerEl(),
        elTd = this.getTdEl(),
        x = Dom.getX(elTd),
        y = Dom.getY(elTd);

    //TODO: remove scrolling logic
    // SF doesn't get xy for cells in scrolling table
    // when tbody display is set to block
    if(isNaN(x) || isNaN(y)) {
        var elTbody = this.getDataTable().getTbodyEl();
        x = elTd.offsetLeft + // cell pos relative to table
                Dom.getX(elTbody.parentNode) - // plus table pos relative to document
                elTbody.scrollLeft; // minus tbody scroll
        y = elTd.offsetTop + // cell pos relative to table
                Dom.getY(elTbody.parentNode) - // plus table pos relative to document
                elTbody.scrollTop + // minus tbody scroll
                this.getDataTable().getTheadEl().offsetHeight; // account for fixed THEAD cells
    }

    elContainer.style.left = x + "px";
    elContainer.style.top = y + "px";

    if(this._elIFrame) {
        this._elIFrame.style.left = x + "px";
        this._elIFrame.style.top = y + "px";
    }
},

/**
 * Displays CellEditor UI in the correct position.
 *
 * @method show
 */
show : function() {
    var elContainer = this.getContainerEl(),
        elIFrame = this._elIFrame;
    this.resetForm();
    this.isActive = true;
    elContainer.style.display = "";
    if(elIFrame) {
        elIFrame.style.width = elContainer.offsetWidth + "px";
        elIFrame.style.height = elContainer.offsetHeight + "px";
        elIFrame.style.display = "";
    }
    this.focus();
    this.fireEvent("showEvent", {editor:this});
    YAHOO.log("CellEditor shown", "info", this.toString()); 
},

/**
 * Fires blockEvent
 *
 * @method block
 */
block : function() {
    this.fireEvent("blockEvent", {editor:this});
    YAHOO.log("CellEditor blocked", "info", this.toString()); 
},

/**
 * Fires unblockEvent
 *
 * @method unblock
 */
unblock : function() {
    this.fireEvent("unblockEvent", {editor:this});
    YAHOO.log("CellEditor unblocked", "info", this.toString()); 
},

/**
 * Saves value of CellEditor and hides UI.
 *
 * @method save
 */
save : function() {
    // Get new value
    var inputValue = this.getInputValue();
    var validValue = inputValue;
    
    // Validate new value
    if(this.validator) {
        validValue = this.validator.call(this.getDataTable(), inputValue, this.value, this);
        if(validValue === undefined ) {
            if(this.resetInvalidData) {
                this.resetForm();
            }
            this.fireEvent("invalidDataEvent",
                    {editor:this, oldData:this.value, newData:inputValue});
            YAHOO.log("Could not save Cell Editor input due to invalid data " +
                    lang.dump(inputValue), "warn", this.toString());
            return;
        }
    }
        
    var oSelf = this;
    var finishSave = function(bSuccess, oNewValue) {
        var oOrigValue = oSelf.value;
        if(bSuccess) {
            // Update new value
            oSelf.value = oNewValue;
            oSelf.getDataTable().updateCell(oSelf.getRecord(), oSelf.getColumn(), oNewValue);
            
            // Hide CellEditor
            oSelf._hide();
            
            oSelf.fireEvent("saveEvent",
                    {editor:oSelf, oldData:oOrigValue, newData:oSelf.value});
            YAHOO.log("Cell Editor input saved", "info", this.toString());
        }
        else {
            oSelf.resetForm();
            oSelf.fireEvent("revertEvent",
                    {editor:oSelf, oldData:oOrigValue, newData:oNewValue});
            YAHOO.log("Could not save Cell Editor input " +
                    lang.dump(oNewValue), "warn", oSelf.toString());
        }
        oSelf.unblock();
    };
    
    this.block();
    if(lang.isFunction(this.asyncSubmitter)) {
        this.asyncSubmitter.call(this, finishSave, validValue);
    } 
    else {   
        finishSave(true, validValue);
    }
},

/**
 * Cancels CellEditor input and hides UI.
 *
 * @method cancel
 */
cancel : function() {
    if(this.isActive) {
        this._hide();
        this.fireEvent("cancelEvent", {editor:this});
        YAHOO.log("CellEditor canceled", "info", this.toString());
    }
    else {
        YAHOO.log("Unable to cancel CellEditor", "warn", this.toString());
    }
},

/**
 * Renders form elements.
 *
 * @method renderForm
 */
renderForm : function() {
    // To be implemented by subclass
},

/**
 * Access to add additional event listeners.
 *
 * @method doAfterRender
 */
doAfterRender : function() {
    // To be implemented by subclass
},


/**
 * After rendering form, if disabledBtns is set to true, then sets up a mechanism
 * to save input without them. 
 *
 * @method handleDisabledBtns
 */
handleDisabledBtns : function() {
    // To be implemented by subclass
},

/**
 * Resets CellEditor UI to initial state.
 *
 * @method resetForm
 */
resetForm : function() {
    // To be implemented by subclass
},

/**
 * Sets focus in CellEditor.
 *
 * @method focus
 */
focus : function() {
    // To be implemented by subclass
},

/**
 * Retrieves input value from CellEditor.
 *
 * @method getInputValue
 */
getInputValue : function() {
    // To be implemented by subclass
}

};

lang.augmentProto(BCE, util.EventProvider);


/////////////////////////////////////////////////////////////////////////////
//
// Custom Events
//
/////////////////////////////////////////////////////////////////////////////

/**
 * Fired when a CellEditor is shown.
 *
 * @event showEvent
 * @param oArgs.editor {YAHOO.widget.CellEditor} The CellEditor instance.
 */

/**
 * Fired when a CellEditor has a keydown.
 *
 * @event keydownEvent
 * @param oArgs.editor {YAHOO.widget.CellEditor} The CellEditor instance. 
 * @param oArgs.event {HTMLEvent} The event object.
 */

/**
 * Fired when a CellEditor input is reverted due to invalid data.
 *
 * @event invalidDataEvent
 * @param oArgs.editor {YAHOO.widget.CellEditor} The CellEditor instance. 
 * @param oArgs.newData {Object} New data value from form input field.
 * @param oArgs.oldData {Object} Old data value.
 */

/**
 * Fired when a CellEditor input is reverted due to asyncSubmitter failure.
 *
 * @event revertEvent
 * @param oArgs.editor {YAHOO.widget.CellEditor} The CellEditor instance. 
 * @param oArgs.newData {Object} New data value from form input field.
 * @param oArgs.oldData {Object} Old data value.
 */

/**
 * Fired when a CellEditor input is saved.
 *
 * @event saveEvent
 * @param oArgs.editor {YAHOO.widget.CellEditor} The CellEditor instance. 
 * @param oArgs.newData {Object} New data value from form input field.
 * @param oArgs.oldData {Object} Old data value.
 */

/**
 * Fired when a CellEditor input is canceled.
 *
 * @event cancelEvent
 * @param oArgs.editor {YAHOO.widget.CellEditor} The CellEditor instance. 
 */

/**
 * Fired when a CellEditor has a blur event.
 *
 * @event blurEvent
 * @param oArgs.editor {YAHOO.widget.CellEditor} The CellEditor instance. 
 */














/****************************************************************************/
/****************************************************************************/
/****************************************************************************/
    
/**
 * The CheckboxCellEditor class provides functionality for inline editing
 * DataTable cell data with checkboxes.
 *
 * @namespace YAHOO.widget
 * @class CheckboxCellEditor
 * @extends YAHOO.widget.BaseCellEditor
 * @constructor
 * @param oConfigs {Object} (Optional) Object literal of configs.
 */
widget.CheckboxCellEditor = function(oConfigs) {
    oConfigs = oConfigs || {};
    this._sId = this._sId || Dom.generateId(null, "yui-checkboxceditor"); // "yui-checkboxceditor" + YAHOO.widget.BaseCellEditor._nCount++;
    YAHOO.widget.BaseCellEditor._nCount++;
    widget.CheckboxCellEditor.superclass.constructor.call(this, oConfigs.type || "checkbox", oConfigs);
};

// CheckboxCellEditor extends BaseCellEditor
lang.extend(widget.CheckboxCellEditor, BCE, {

/////////////////////////////////////////////////////////////////////////////
//
// CheckboxCellEditor public properties
//
/////////////////////////////////////////////////////////////////////////////
/**
 * Array of checkbox values. Can either be a simple array (e.g., ["red","green","blue"])
 * or a an array of objects (e.g., [{label:"red", value:"#FF0000"},
 * {label:"green", value:"#00FF00"}, {label:"blue", value:"#0000FF"}]). String
 * values are treated as markup and inserted into the DOM as innerHTML.
 *
 * @property checkboxOptions
 * @type HTML[] | Object[]
 */
checkboxOptions : null,

/**
 * Reference to the checkbox elements.
 *
 * @property checkboxes
 * @type HTMLElement[] 
 */
checkboxes : null,

/**
 * Array of checked values
 *
 * @property value
 * @type String[] 
 */
value : null,

/////////////////////////////////////////////////////////////////////////////
//
// CheckboxCellEditor public methods
//
/////////////////////////////////////////////////////////////////////////////

/**
 * Render a form with input(s) type=checkbox.
 *
 * @method renderForm
 */
renderForm : function() {
    if(lang.isArray(this.checkboxOptions)) {
        var checkboxOption, checkboxValue, checkboxId, elLabel, j, len;
        
        // Create the checkbox buttons in an IE-friendly way...
        for(j=0,len=this.checkboxOptions.length; j<len; j++) {
            checkboxOption = this.checkboxOptions[j];
            checkboxValue = lang.isValue(checkboxOption.value) ?
                    checkboxOption.value : checkboxOption;

            checkboxId = this.getId() + "-chk" + j;
            this.getContainerEl().innerHTML += "<input type=\"checkbox\"" +
                    " id=\"" + checkboxId + "\"" + // Needed for label
                    " value=\"" + checkboxValue + "\" />";
            
            // Create the labels in an IE-friendly way
            elLabel = this.getContainerEl().appendChild(document.createElement("label"));
            elLabel.htmlFor = checkboxId;
            elLabel.innerHTML = lang.isValue(checkboxOption.label) ?
                    checkboxOption.label : checkboxOption;
        }
        
        // Store the reference to the checkbox elements
        var allCheckboxes = [];
        for(j=0; j<len; j++) {
            allCheckboxes[allCheckboxes.length] = this.getContainerEl().childNodes[j*2];
        }
        this.checkboxes = allCheckboxes;

        if(this.disableBtns) {
            this.handleDisabledBtns();
        }
    }
    else {
        YAHOO.log("Could not find checkboxOptions", "error", this.toString());
    }
},

/**
 * After rendering form, if disabledBtns is set to true, then sets up a mechanism
 * to save input without them. 
 *
 * @method handleDisabledBtns
 */
handleDisabledBtns : function() {
    Ev.addListener(this.getContainerEl(), "click", function(v){
        if(Ev.getTarget(v).tagName.toLowerCase() === "input") {
            // Save on blur
            this.save();
        }
    }, this, true);
},

/**
 * Resets CheckboxCellEditor UI to initial state.
 *
 * @method resetForm
 */
resetForm : function() {
    // Normalize to array
    var originalValues = lang.isArray(this.value) ? this.value : [this.value];
    
    // Match checks to value
    for(var i=0, j=this.checkboxes.length; i<j; i++) {
        this.checkboxes[i].checked = false;
        for(var k=0, len=originalValues.length; k<len; k++) {
            if(this.checkboxes[i].value == originalValues[k]) {
                this.checkboxes[i].checked = true;
            }
        }
    }
},

/**
 * Sets focus in CheckboxCellEditor.
 *
 * @method focus
 */
focus : function() {
    this.checkboxes[0].focus();
},

/**
 * Retrieves input value from CheckboxCellEditor.
 *
 * @method getInputValue
 */
getInputValue : function() {
    var checkedValues = [];
    for(var i=0, j=this.checkboxes.length; i<j; i++) {
        if(this.checkboxes[i].checked) {
            checkedValues[checkedValues.length] = this.checkboxes[i].value;
        }
    }  
    return checkedValues;
}

});

// Copy static members to CheckboxCellEditor class
lang.augmentObject(widget.CheckboxCellEditor, BCE);








/****************************************************************************/
/****************************************************************************/
/****************************************************************************/
    
/**
 * The DataCellEditor class provides functionality for inline editing
 * DataTable cell data with a YUI Calendar.
 *
 * @namespace YAHOO.widget
 * @class DateCellEditor
 * @extends YAHOO.widget.BaseCellEditor 
 * @constructor
 * @param oConfigs {Object} (Optional) Object literal of configs.
 */
widget.DateCellEditor = function(oConfigs) {
    oConfigs = oConfigs || {};
    this._sId = this._sId || Dom.generateId(null, "yui-dateceditor"); // "yui-dateceditor" + YAHOO.widget.BaseCellEditor._nCount++;
    YAHOO.widget.BaseCellEditor._nCount++;
    widget.DateCellEditor.superclass.constructor.call(this, oConfigs.type || "date", oConfigs);
};

// CheckboxCellEditor extends BaseCellEditor
lang.extend(widget.DateCellEditor, BCE, {

/////////////////////////////////////////////////////////////////////////////
//
// DateCellEditor public properties
//
/////////////////////////////////////////////////////////////////////////////
/**
 * Reference to Calendar instance.
 *
 * @property calendar
 * @type YAHOO.widget.Calendar
 */
calendar : null,

/**
 * Configs for the calendar instance, to be passed to Calendar constructor.
 *
 * @property calendarOptions
 * @type Object
 */
calendarOptions : null,

/**
 * Default value.
 *
 * @property defaultValue
 * @type Date
 * @default new Date()
 */
defaultValue : new Date(),


/////////////////////////////////////////////////////////////////////////////
//
// DateCellEditor public methods
//
/////////////////////////////////////////////////////////////////////////////

/**
 * Render a Calendar.
 *
 * @method renderForm
 */
renderForm : function() {
    // Calendar widget
    if(YAHOO.widget.Calendar) {
        var calContainer = this.getContainerEl().appendChild(document.createElement("div"));
        calContainer.id = this.getId() + "-dateContainer"; // Needed for Calendar constructor
        var calendar =
                new YAHOO.widget.Calendar(this.getId() + "-date",
                calContainer.id, this.calendarOptions);
        calendar.render();
        calContainer.style.cssFloat = "none";
        
        // Bug 2528576
        calendar.hideEvent.subscribe(function() {this.cancel();}, this, true);

        if(ua.ie) {
            var calFloatClearer = this.getContainerEl().appendChild(document.createElement("div"));
            calFloatClearer.style.clear = "both";
        }
        
        this.calendar = calendar;

        if(this.disableBtns) {
            this.handleDisabledBtns();
        }
    }
    else {
        YAHOO.log("Could not find YUI Calendar", "error", this.toString());
    }
    
},

/**
 * After rendering form, if disabledBtns is set to true, then sets up a mechanism
 * to save input without them. 
 *
 * @method handleDisabledBtns
 */
handleDisabledBtns : function() {
    this.calendar.selectEvent.subscribe(function(v){
        // Save on select
        this.save();
    }, this, true);
},

/**
 * Resets DateCellEditor UI to initial state.
 *
 * @method resetForm
 */
resetForm : function() {
    var value = this.value || (new Date());
    this.calendar.select(value);
    this.calendar.cfg.setProperty("pagedate",value,false);
	this.calendar.render();
	// Bug 2528576
	this.calendar.show();
},

/**
 * Sets focus in DateCellEditor.
 *
 * @method focus
 */
focus : function() {
    // To be impmlemented by subclass
},

/**
 * Retrieves input value from DateCellEditor.
 *
 * @method getInputValue
 */
getInputValue : function() {
    return this.calendar.getSelectedDates()[0];
}

});

// Copy static members to DateCellEditor class
lang.augmentObject(widget.DateCellEditor, BCE);









/****************************************************************************/
/****************************************************************************/
/****************************************************************************/
    
/**
 * The DropdownCellEditor class provides functionality for inline editing
 * DataTable cell data a SELECT element.
 *
 * @namespace YAHOO.widget
 * @class DropdownCellEditor
 * @extends YAHOO.widget.BaseCellEditor 
 * @constructor
 * @param oConfigs {Object} (Optional) Object literal of configs.
 */
widget.DropdownCellEditor = function(oConfigs) {
    oConfigs = oConfigs || {};
    this._sId = this._sId || Dom.generateId(null, "yui-dropdownceditor"); // "yui-dropdownceditor" + YAHOO.widget.BaseCellEditor._nCount++;
    YAHOO.widget.BaseCellEditor._nCount++;
    widget.DropdownCellEditor.superclass.constructor.call(this, oConfigs.type || "dropdown", oConfigs);
};

// DropdownCellEditor extends BaseCellEditor
lang.extend(widget.DropdownCellEditor, BCE, {

/////////////////////////////////////////////////////////////////////////////
//
// DropdownCellEditor public properties
//
/////////////////////////////////////////////////////////////////////////////
/**
 * Array of dropdown values. Can either be a simple array (e.g.,
 * ["Alabama","Alaska","Arizona","Arkansas"]) or a an array of objects (e.g., 
 * [{label:"Alabama", value:"AL"}, {label:"Alaska", value:"AK"},
 * {label:"Arizona", value:"AZ"}, {label:"Arkansas", value:"AR"}]). String
 * values are treated as markup and inserted into the DOM as innerHTML.
 *
 * @property dropdownOptions
 * @type HTML[] | Object[]
 */
dropdownOptions : null,

/**
 * Reference to Dropdown element.
 *
 * @property dropdown
 * @type HTMLElement
 */
dropdown : null,

/**
 * Enables multi-select.
 *
 * @property multiple
 * @type Boolean
 */
multiple : false,

/**
 * Specifies number of visible options.
 *
 * @property size
 * @type Number
 */
size : null,

/////////////////////////////////////////////////////////////////////////////
//
// DropdownCellEditor public methods
//
/////////////////////////////////////////////////////////////////////////////

/**
 * Render a form with select element.
 *
 * @method renderForm
 */
renderForm : function() {
    var elDropdown = this.getContainerEl().appendChild(document.createElement("select"));
    elDropdown.style.zoom = 1;
    if(this.multiple) {
        elDropdown.multiple = "multiple";
    }
    if(lang.isNumber(this.size)) {
        elDropdown.size = this.size;
    }
    this.dropdown = elDropdown;
    
    if(lang.isArray(this.dropdownOptions)) {
        var dropdownOption, elOption;
        for(var i=0, j=this.dropdownOptions.length; i<j; i++) {
            dropdownOption = this.dropdownOptions[i];
            elOption = document.createElement("option");
            elOption.value = (lang.isValue(dropdownOption.value)) ?
                    dropdownOption.value : dropdownOption;
            elOption.innerHTML = (lang.isValue(dropdownOption.label)) ?
                    dropdownOption.label : dropdownOption;
            elOption = elDropdown.appendChild(elOption);
        }
        
        if(this.disableBtns) {
            this.handleDisabledBtns();
        }
    }
},

/**
 * After rendering form, if disabledBtns is set to true, then sets up a mechanism
 * to save input without them. 
 *
 * @method handleDisabledBtns
 */
handleDisabledBtns : function() {
    // Save on blur for multi-select
    if(this.multiple) {
        Ev.addListener(this.dropdown, "blur", function(v){
            // Save on change
            this.save();
        }, this, true);
    }
    // Save on change for single-select
    else {
        if(!ua.ie) {
            Ev.addListener(this.dropdown, "change", function(v){
                // Save on change
                this.save();
            }, this, true);
        }
        else {
            // Bug 2529274: "change" event is not keyboard accessible in IE6
            Ev.addListener(this.dropdown, "blur", function(v){
                this.save();
            }, this, true);
            Ev.addListener(this.dropdown, "click", function(v){
                this.save();
            }, this, true);
        }
    }
},

/**
 * Resets DropdownCellEditor UI to initial state.
 *
 * @method resetForm
 */
resetForm : function() {
    var allOptions = this.dropdown.options,
        i=0, j=allOptions.length;

    // Look for multi-select selections
    if(lang.isArray(this.value)) {
        var allValues = this.value,
            m=0, n=allValues.length,
            hash = {};
        // Reset all selections and stash options in a value hash
        for(; i<j; i++) {
            allOptions[i].selected = false;
            hash[allOptions[i].value] = allOptions[i];
        }
        for(; m<n; m++) {
            if(hash[allValues[m]]) {
                hash[allValues[m]].selected = true;
            }
        }
    }
    // Only need to look for a single selection
    else {
        for(; i<j; i++) {
            if(this.value == allOptions[i].value) {
                allOptions[i].selected = true;
            }
        }
    }
},

/**
 * Sets focus in DropdownCellEditor.
 *
 * @method focus
 */
focus : function() {
    this.getDataTable()._focusEl(this.dropdown);
},

/**
 * Retrieves input value from DropdownCellEditor.
 *
 * @method getInputValue
 */
getInputValue : function() {
    var allOptions = this.dropdown.options;
    
    // Look for multiple selections
    if(this.multiple) {
        var values = [],
            i=0, j=allOptions.length;
        for(; i<j; i++) {
            if(allOptions[i].selected) {
                values.push(allOptions[i].value);
            }
        }
        return values;
    }
    // Only need to look for single selection
    else {
        return allOptions[allOptions.selectedIndex].value;
    }
}

});

// Copy static members to DropdownCellEditor class
lang.augmentObject(widget.DropdownCellEditor, BCE);






/****************************************************************************/
/****************************************************************************/
/****************************************************************************/
    
/**
 * The RadioCellEditor class provides functionality for inline editing
 * DataTable cell data with radio buttons.
 *
 * @namespace YAHOO.widget
 * @class RadioCellEditor
 * @extends YAHOO.widget.BaseCellEditor 
 * @constructor
 * @param oConfigs {Object} (Optional) Object literal of configs.
 */
widget.RadioCellEditor = function(oConfigs) {
    oConfigs = oConfigs || {};
    this._sId = this._sId || Dom.generateId(null, "yui-radioceditor"); // "yui-radioceditor" + YAHOO.widget.BaseCellEditor._nCount++;
    YAHOO.widget.BaseCellEditor._nCount++;
    widget.RadioCellEditor.superclass.constructor.call(this, oConfigs.type || "radio", oConfigs);
};

// RadioCellEditor extends BaseCellEditor
lang.extend(widget.RadioCellEditor, BCE, {

/////////////////////////////////////////////////////////////////////////////
//
// RadioCellEditor public properties
//
/////////////////////////////////////////////////////////////////////////////
/**
 * Reference to radio elements.
 *
 * @property radios
 * @type HTMLElement[]
 */
radios : null,

/**
 * Array of radio values. Can either be a simple array (e.g., ["yes","no","maybe"])
 * or a an array of objects (e.g., [{label:"yes", value:1}, {label:"no", value:-1},
 * {label:"maybe", value:0}]). String values are treated as markup and inserted
 * into the DOM as innerHTML.
 *
 * @property radioOptions
 * @type HTML[] | Object[]
 */
radioOptions : null,

/////////////////////////////////////////////////////////////////////////////
//
// RadioCellEditor public methods
//
/////////////////////////////////////////////////////////////////////////////

/**
 * Render a form with input(s) type=radio.
 *
 * @method renderForm
 */
renderForm : function() {
    if(lang.isArray(this.radioOptions)) {
        var radioOption, radioValue, radioId, elLabel;
        
        // Create the radio buttons in an IE-friendly way
        for(var i=0, len=this.radioOptions.length; i<len; i++) {
            radioOption = this.radioOptions[i];
            radioValue = lang.isValue(radioOption.value) ?
                    radioOption.value : radioOption;
            radioId = this.getId() + "-radio" + i;
            this.getContainerEl().innerHTML += "<input type=\"radio\"" +
                    " name=\"" + this.getId() + "\"" +
                    " value=\"" + radioValue + "\"" +
                    " id=\"" +  radioId + "\" />"; // Needed for label
            
            // Create the labels in an IE-friendly way
            elLabel = this.getContainerEl().appendChild(document.createElement("label"));
            elLabel.htmlFor = radioId;
            elLabel.innerHTML = (lang.isValue(radioOption.label)) ?
                    radioOption.label : radioOption;
        }
        
        // Store the reference to the checkbox elements
        var allRadios = [],
            elRadio;
        for(var j=0; j<len; j++) {
            elRadio = this.getContainerEl().childNodes[j*2];
            allRadios[allRadios.length] = elRadio;
        }
        this.radios = allRadios;

        if(this.disableBtns) {
            this.handleDisabledBtns();
        }
    }
    else {
        YAHOO.log("Could not find radioOptions", "error", this.toString());
    }
},

/**
 * After rendering form, if disabledBtns is set to true, then sets up a mechanism
 * to save input without them. 
 *
 * @method handleDisabledBtns
 */
handleDisabledBtns : function() {
    Ev.addListener(this.getContainerEl(), "click", function(v){
        if(Ev.getTarget(v).tagName.toLowerCase() === "input") {
            // Save on blur
            this.save();
        }
    }, this, true);
},

/**
 * Resets RadioCellEditor UI to initial state.
 *
 * @method resetForm
 */
resetForm : function() {
    for(var i=0, j=this.radios.length; i<j; i++) {
        var elRadio = this.radios[i];
        if(this.value == elRadio.value) {
            elRadio.checked = true;
            return;
        }
    }
},

/**
 * Sets focus in RadioCellEditor.
 *
 * @method focus
 */
focus : function() {
    for(var i=0, j=this.radios.length; i<j; i++) {
        if(this.radios[i].checked) {
            this.radios[i].focus();
            return;
        }
    }
},

/**
 * Retrieves input value from RadioCellEditor.
 *
 * @method getInputValue
 */
getInputValue : function() {
    for(var i=0, j=this.radios.length; i<j; i++) {
        if(this.radios[i].checked) {
            return this.radios[i].value;
        }
    }
}

});

// Copy static members to RadioCellEditor class
lang.augmentObject(widget.RadioCellEditor, BCE);






/****************************************************************************/
/****************************************************************************/
/****************************************************************************/
    
/**
 * The TextareaCellEditor class provides functionality for inline editing
 * DataTable cell data with a TEXTAREA element.
 *
 * @namespace YAHOO.widget
 * @class TextareaCellEditor
 * @extends YAHOO.widget.BaseCellEditor 
 * @constructor
 * @param oConfigs {Object} (Optional) Object literal of configs.
 */
widget.TextareaCellEditor = function(oConfigs) {
    oConfigs = oConfigs || {};
    this._sId = this._sId || Dom.generateId(null, "yui-textareaceditor");// "yui-textareaceditor" + ;
    YAHOO.widget.BaseCellEditor._nCount++;
    widget.TextareaCellEditor.superclass.constructor.call(this, oConfigs.type || "textarea", oConfigs);
};

// TextareaCellEditor extends BaseCellEditor
lang.extend(widget.TextareaCellEditor, BCE, {

/////////////////////////////////////////////////////////////////////////////
//
// TextareaCellEditor public properties
//
/////////////////////////////////////////////////////////////////////////////
/**
 * Reference to textarea element.
 *
 * @property textarea
 * @type HTMLElement
 */
textarea : null,


/////////////////////////////////////////////////////////////////////////////
//
// TextareaCellEditor public methods
//
/////////////////////////////////////////////////////////////////////////////

/**
 * Render a form with textarea.
 *
 * @method renderForm
 */
renderForm : function() {
    var elTextarea = this.getContainerEl().appendChild(document.createElement("textarea"));
    this.textarea = elTextarea;

    if(this.disableBtns) {
        this.handleDisabledBtns();
    }
},

/**
 * After rendering form, if disabledBtns is set to true, then sets up a mechanism
 * to save input without them. 
 *
 * @method handleDisabledBtns
 */
handleDisabledBtns : function() {
    Ev.addListener(this.textarea, "blur", function(v){
        // Save on blur
        this.save();
    }, this, true);        
},

/**
 * Moves TextareaCellEditor UI to a cell.
 *
 * @method move
 */
move : function() {
    this.textarea.style.width = this.getTdEl().offsetWidth + "px";
    this.textarea.style.height = "3em";
    YAHOO.widget.TextareaCellEditor.superclass.move.call(this);
},

/**
 * Resets TextareaCellEditor UI to initial state.
 *
 * @method resetForm
 */
resetForm : function() {
    this.textarea.value = this.value;
},

/**
 * Sets focus in TextareaCellEditor.
 *
 * @method focus
 */
focus : function() {
    // Bug 2303181, Bug 2263600
    this.getDataTable()._focusEl(this.textarea);
    this.textarea.select();
},

/**
 * Retrieves input value from TextareaCellEditor.
 *
 * @method getInputValue
 */
getInputValue : function() {
    return this.textarea.value;
}

});

// Copy static members to TextareaCellEditor class
lang.augmentObject(widget.TextareaCellEditor, BCE);









/****************************************************************************/
/****************************************************************************/
/****************************************************************************/
    
/**
 * The TextboxCellEditor class provides functionality for inline editing
 * DataTable cell data with an INPUT TYPE=TEXT element.
 *
 * @namespace YAHOO.widget
 * @class TextboxCellEditor
 * @extends YAHOO.widget.BaseCellEditor 
 * @constructor
 * @param oConfigs {Object} (Optional) Object literal of configs.
 */
widget.TextboxCellEditor = function(oConfigs) {
    oConfigs = oConfigs || {};
    this._sId = this._sId || Dom.generateId(null, "yui-textboxceditor");// "yui-textboxceditor" + YAHOO.widget.BaseCellEditor._nCount++;
    YAHOO.widget.BaseCellEditor._nCount++;
    widget.TextboxCellEditor.superclass.constructor.call(this, oConfigs.type || "textbox", oConfigs);
};

// TextboxCellEditor extends BaseCellEditor
lang.extend(widget.TextboxCellEditor, BCE, {

/////////////////////////////////////////////////////////////////////////////
//
// TextboxCellEditor public properties
//
/////////////////////////////////////////////////////////////////////////////
/**
 * Reference to the textbox element.
 *
 * @property textbox
 */
textbox : null,

/////////////////////////////////////////////////////////////////////////////
//
// TextboxCellEditor public methods
//
/////////////////////////////////////////////////////////////////////////////

/**
 * Render a form with input type=text.
 *
 * @method renderForm
 */
renderForm : function() {
    var elTextbox;
    // Bug 1802582: SF3/Mac needs a form element wrapping the input
    if(ua.webkit>420) {
        elTextbox = this.getContainerEl().appendChild(document.createElement("form")).appendChild(document.createElement("input"));
    }
    else {
        elTextbox = this.getContainerEl().appendChild(document.createElement("input"));
    }
    elTextbox.type = "text";
    this.textbox = elTextbox;

    // Save on enter by default
    // Bug: 1802582 Set up a listener on each textbox to track on keypress
    // since SF/OP can't preventDefault on keydown
    Ev.addListener(elTextbox, "keypress", function(v){
        if((v.keyCode === 13)) {
            // Prevent form submit
            YAHOO.util.Event.preventDefault(v);
            this.save();
        }
    }, this, true);

    if(this.disableBtns) {
        // By default this is no-op since enter saves by default
        this.handleDisabledBtns();
    }
},

/**
 * Moves TextboxCellEditor UI to a cell.
 *
 * @method move
 */
move : function() {
    this.textbox.style.width = this.getTdEl().offsetWidth + "px";
    widget.TextboxCellEditor.superclass.move.call(this);
},

/**
 * Resets TextboxCellEditor UI to initial state.
 *
 * @method resetForm
 */
resetForm : function() {
    this.textbox.value = lang.isValue(this.value) ? this.value.toString() : "";
},

/**
 * Sets focus in TextboxCellEditor.
 *
 * @method focus
 */
focus : function() {
    // Bug 2303181, Bug 2263600
    this.getDataTable()._focusEl(this.textbox);
    this.textbox.select();
},

/**
 * Returns new value for TextboxCellEditor.
 *
 * @method getInputValue
 */
getInputValue : function() {
    return this.textbox.value;
}

});

// Copy static members to TextboxCellEditor class
lang.augmentObject(widget.TextboxCellEditor, BCE);







/////////////////////////////////////////////////////////////////////////////
//
// DataTable extension
//
/////////////////////////////////////////////////////////////////////////////

/**
 * CellEditor subclasses.
 * @property DataTable.Editors
 * @type Object
 * @static
 */
DT.Editors = {
    checkbox : widget.CheckboxCellEditor,
    "date"   : widget.DateCellEditor,
    dropdown : widget.DropdownCellEditor,
    radio    : widget.RadioCellEditor,
    textarea : widget.TextareaCellEditor,
    textbox  : widget.TextboxCellEditor
};

/****************************************************************************/
/****************************************************************************/
/****************************************************************************/
    
/**
 * Factory class for instantiating a BaseCellEditor subclass.
 *
 * @namespace YAHOO.widget
 * @class CellEditor
 * @extends YAHOO.widget.BaseCellEditor 
 * @constructor
 * @param sType {String} Type indicator, to map to YAHOO.widget.DataTable.Editors.
 * @param oConfigs {Object} (Optional) Object literal of configs.
 */
widget.CellEditor = function(sType, oConfigs) {
    // Point to one of the subclasses
    if(sType && DT.Editors[sType]) {
        lang.augmentObject(BCE, DT.Editors[sType]);
        return new DT.Editors[sType](oConfigs);
    }
    else {
        return new BCE(null, oConfigs);
    }
};

var CE = widget.CellEditor;

// Copy static members to CellEditor class
lang.augmentObject(CE, BCE);


})();
