(function () {
    var Dom = YAHOO.util.Dom,
        Lang = YAHOO.lang,
        Event = YAHOO.util.Event,
        Calendar = YAHOO.widget.Calendar;

/**
 * A Date-specific implementation that differs from TextNode in that it uses
 * YAHOO.widget.Calendar as an in-line editor, if available
 * If Calendar is not available, it behaves as a plain TextNode.
 * @namespace YAHOO.widget
 * @class DateNode
 * @extends YAHOO.widget.TextNode
 * @param oData {object} a string or object containing the data that will
 * be used to render this node.
 * Providing a string is the same as providing an object with a single property named label.
 * All values in the oData will be used to set equally named properties in the node
 * as long as the node does have such properties, they are not undefined, private nor functions.
 * All attributes are made available in noderef.data, which
 * can be used to store custom attributes.  TreeView.getNode(s)ByProperty
 * can be used to retrieve a node by one of the attributes.
 * @param oParent {YAHOO.widget.Node} this node's parent node
 * @param expanded {boolean} the initial expanded/collapsed state (deprecated; use oData.expanded)
 * @constructor
 */
YAHOO.widget.DateNode = function(oData, oParent, expanded) {
    YAHOO.widget.DateNode.superclass.constructor.call(this,oData, oParent, expanded);
};

YAHOO.extend(YAHOO.widget.DateNode, YAHOO.widget.TextNode, {

    /**
     * The node type
     * @property _type
     * @type string
     * @private
     * @default  "DateNode"
     */
    _type: "DateNode",

    /**
    * Configuration object for the Calendar editor, if used.
    * See <a href="http://developer.yahoo.com/yui/calendar/#internationalization">http://developer.yahoo.com/yui/calendar/#internationalization</a>
    * @property calendarConfig
    */
    calendarConfig: null,



    /**
     *  If YAHOO.widget.Calendar is available, it will pop up a Calendar to enter a new date.  Otherwise, it falls back to a plain &lt;input&gt;  textbox
     * @method fillEditorContainer
     * @param editorData {YAHOO.widget.TreeView.editorData}  a shortcut to the static object holding editing information
     * @return void
     */
    fillEditorContainer: function (editorData) {

        var cal, container = editorData.inputContainer;

        if (Lang.isUndefined(Calendar)) {
            Dom.replaceClass(editorData.editorPanel,'ygtv-edit-DateNode','ygtv-edit-TextNode');
            YAHOO.widget.DateNode.superclass.fillEditorContainer.call(this, editorData);
            return;
        }

        if (editorData.nodeType != this._type) {
            editorData.nodeType = this._type;
            editorData.saveOnEnter = false;

            editorData.node.destroyEditorContents(editorData);

            editorData.inputObject = cal = new Calendar(container.appendChild(document.createElement('div')));
            if (this.calendarConfig) {
                cal.cfg.applyConfig(this.calendarConfig,true);
                cal.cfg.fireQueue();
            }
            cal.selectEvent.subscribe(function () {
                this.tree._closeEditor(true);
            },this,true);
        } else {
            cal = editorData.inputObject;
        }

        editorData.oldValue = this.label;
        cal.cfg.setProperty("selected",this.label, false);

        var delim = cal.cfg.getProperty('DATE_FIELD_DELIMITER');
        var pageDate = this.label.split(delim);
        cal.cfg.setProperty('pagedate',pageDate[cal.cfg.getProperty('MDY_MONTH_POSITION') -1] + delim + pageDate[cal.cfg.getProperty('MDY_YEAR_POSITION') -1]);
        cal.cfg.fireQueue();

        cal.render();
        cal.oDomContainer.focus();
    },
     /**
    * Returns the value from the input element.
    * Overrides Node.getEditorValue.
    * @method getEditorValue
     * @param editorData {YAHOO.widget.TreeView.editorData}  a shortcut to the static object holding editing information
     * @return {string} date entered
     */

    getEditorValue: function (editorData) {
        if (Lang.isUndefined(Calendar)) {
            return editorData.inputElement.value;
        } else {
            var cal = editorData.inputObject,
                date = cal.getSelectedDates()[0],
                dd = [];

            dd[cal.cfg.getProperty('MDY_DAY_POSITION') -1] = date.getDate();
            dd[cal.cfg.getProperty('MDY_MONTH_POSITION') -1] = date.getMonth() + 1;
            dd[cal.cfg.getProperty('MDY_YEAR_POSITION') -1] = date.getFullYear();
            return dd.join(cal.cfg.getProperty('DATE_FIELD_DELIMITER'));
        }
    },

    /**
     * Finally displays the newly entered date in the tree.
     * Overrides Node.displayEditedValue.
     * @method displayEditedValue
     * @param value {HTML} date to be displayed and stored in the node.
     * This data is added to the node unescaped via the innerHTML property.
     * @param editorData {YAHOO.widget.TreeView.editorData}  a shortcut to the static object holding editing information
     */
    displayEditedValue: function (value,editorData) {
        var node = editorData.node;
        node.label = value;
        node.getLabelEl().innerHTML = value;
    },

   /**
     * Returns an object which could be used to build a tree out of this node and its children.
     * It can be passed to the tree constructor to reproduce this node as a tree.
     * It will return false if the node or any descendant loads dynamically, regardless of whether it is loaded or not.
     * @method getNodeDefinition
     * @return {Object | false}  definition of the node or false if this node or any descendant is defined as dynamic
     */
    getNodeDefinition: function() {
        var def = YAHOO.widget.DateNode.superclass.getNodeDefinition.call(this);
        if (def === false) { return false; }
        if (this.calendarConfig) { def.calendarConfig = this.calendarConfig; }
        return def;
    }


});
})();
