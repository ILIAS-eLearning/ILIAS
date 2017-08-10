(function () {

var Paginator = YAHOO.widget.Paginator,
    l         = YAHOO.lang,
    setId     = YAHOO.util.Dom.generateId;

/**
 * Describe the ui Component
 *
 * @namespace YAHOO.widget.Paginator.ui
 * @class YourComponent
 * @for YAHOO.widget.Paginator
 *
 * @constructor
 * @param p {Pagintor} Paginator instance to attach to
 */
Paginator.ui.YourComponent = function (p) {
    this.paginator = p;

    this.initListeners();
};

/**
 * Decorates Paginator instances with new attributes. Called during
 * Paginator instantiation.
 * @method init
 * @param p {Paginator} Paginator instance to decorate
 * @static
 */
Paginator.ui.YourComponent.init = function (p) {

    // add any config attributes your component needs onto the Paginator
    // instance

    /**
     * Describe your attribute.
     * @attribute myAttr
     * @type {HTML}
     * @default 'foo'
     */
    p.setAttributeConfig('myAttr', {
        value : 'foo',
        validator : l.isString
    });

};

// Instance members and methods
Paginator.ui.YourComponent.prototype = {

    // instance members to store the component state and DOM elements that
    // will represent this component's ui

    /**
     * Describe the node that will be stored in this property
     * @property button
     * @type HTMLElement
     * @private
     */
    button : null, // null until render()



    // instance methods

    /**
     * Subscribe to the Paginator's events that will affect this component's ui
     * @method initListeners
     */
    initListeners : function () {
        var p = this.paginator;

        // Subscribe to any of these that are pertinent to your component's ui
        p.subscribe('recordOffsetChange',this.update,this,true);
        //p.subscribe('rowsPerPageChange', this.update,this,true);
        //p.subscribe('totalRecordsChange',this.update,this,true);

        // subscribe to any change events for attributes this component adds
        // to the Paginator
        p.subscribe('myAttrChange',this.rebuild,this,true);

        // Always subscribe to destroy
        p.subscribe('destroy',this.destroy,this,true);
    },

    /**
     * Generate the nodes and return the appropriate node given the current
     * pagination state.
     * @method render
     * @param id_base {string} used to create unique ids for generated nodes
     * @return {HTMLElement}
     */
    render : function (id_base) {
        var node = this._initUI(id_base);

        this._bindUI();

        return node;
    },

    /**
     * Initialize the DOM nodes managed by this component
     * @method initUI
     * @param id_base {string} used to create unique ids for generated nodes
     * @private
     */
    _initUI : function (id_base) {
        var myVal = this.paginator.get('myAttr');

        this.button = document.createElement('button');
        setId(this.button, id_base + '-first-link');
        this.button.innerHTML = myVal;
        // etc

        this.update();

        return this.button;
    },

    /**
     * Attach DOM event listeners to the nodes managed by this component
     * @method bindUI
     * @private
     */
    _bindUI : function () {
        YAHOO.util.Event.on(this.button,'click',this.onClick,this,true);
    },

    /**
     * Make any necessary changes to the component nodes
     * @method update
     * @param e {CustomEvent} The calling change event
     */
    update : function (e) {
        // It's a good idea to check if there actually was a change
        if (e && e.prevValue === e.newValue) {
            return;
        }

        if (this.paginator.get('recordOffset') < 1) {
            this.button.disabled = true;
        } else {
            this.button.disabled = false;
        }
    },

    /**
     * Make more substantial changes in a separate method if necessary
     * @method rebuild
     * @param e {CustomEvent} the calling change event
     */
    rebuild : function (e) {
        // Making sure there actually was a change
        if (e && e.prevValue === e.newValue) {
            return;
        }

        this.button.disabled = false;
        this.button.innerHTML = this.paginator.get('myAttr');
        // etc

        this.update();
    },

    /**
     * Remove the generated DOM structure
     * @method destroy
     * @private
     */
    destroy : function () {
        YAHOO.util.Event.purgeElement(this.button);
        this.button.parentNode.removeChild(this.button);
        this.button = null;
    },

    /**
     * Listener for a DOM event from a managed element.  Pass new value to
     * Paginator.setStartIndex(..), .setPage(..) etc to fire off changeRequest
     * events.  DO NOT modify the managed element's state here.  That should
     * happen in response to the Paginator's recordOffsetChange event (et al)
     * @method onClick
     * @param e {DOMEvent} The click event
     */
    onClick : function (e) {
        YAHOO.util.Event.stopEvent(e);
        this.paginator.setStartIndex(42);
    }
};

})();
