(function() {

var Event=YAHOO.util.Event; 
var Dom=YAHOO.util.Dom;

/**
 * Defines the interface and base operation of items that that can be 
 * dragged or can be drop targets.  It was designed to be extended, overriding
 * the event handlers for startDrag, onDrag, onDragOver, onDragOut.
 * Up to three html elements can be associated with a DragDrop instance:
 * <ul>
 * <li>linked element: the element that is passed into the constructor.
 * This is the element which defines the boundaries for interaction with 
 * other DragDrop objects.</li>
 * <li>handle element(s): The drag operation only occurs if the element that 
 * was clicked matches a handle element.  By default this is the linked 
 * element, but there are times that you will want only a portion of the 
 * linked element to initiate the drag operation, and the setHandleElId() 
 * method provides a way to define this.</li>
 * <li>drag element: this represents an the element that would be moved along
 * with the cursor during a drag operation.  By default, this is the linked
 * element itself as in {@link YAHOO.util.DD}.  setDragElId() lets you define
 * a separate element that would be moved, as in {@link YAHOO.util.DDProxy}
 * </li>
 * </ul>
 * This class should not be instantiated until the onload event to ensure that
 * the associated elements are available.
 * The following would define a DragDrop obj that would interact with any 
 * other DragDrop obj in the "group1" group:
 * <pre>
 *  dd = new YAHOO.util.DragDrop("div1", "group1");
 * </pre>
 * Since none of the event handlers have been implemented, nothing would 
 * actually happen if you were to run the code above.  Normally you would 
 * override this class or one of the default implementations, but you can 
 * also override the methods you want on an instance of the class...
 * <pre>
 *  dd.onDragDrop = function(e, id) {
 *  &nbsp;&nbsp;alert("dd was dropped on " + id);
 *  }
 * </pre>
 * @namespace YAHOO.util
 * @class DragDrop
 * @constructor
 * @param {String} id of the element that is linked to this instance
 * @param {String} sGroup the group of related DragDrop objects
 * @param {object} config an object containing configurable attributes
 *                Valid properties for DragDrop: 
 *                    padding, isTarget, maintainOffset, primaryButtonOnly,
 */
YAHOO.util.DragDrop = function(id, sGroup, config) {
    if (id) {
        this.init(id, sGroup, config); 
    }
};

YAHOO.util.DragDrop.prototype = {
    /**
     * An Object Literal containing the events that we will be using: mouseDown, b4MouseDown, mouseUp, b4StartDrag, startDrag, b4EndDrag, endDrag, mouseUp, drag, b4Drag, invalidDrop, b4DragOut, dragOut, dragEnter, b4DragOver, dragOver, b4DragDrop, dragDrop
     * By setting any of these to false, then event will not be fired.
     * @property events
     * @type object
     */
    events: null,
    /**
    * @method on
    * @description Shortcut for EventProvider.subscribe, see <a href="YAHOO.util.EventProvider.html#subscribe">YAHOO.util.EventProvider.subscribe</a>
    */
    on: function() {
        this.subscribe.apply(this, arguments);
    },
    /**
     * The id of the element associated with this object.  This is what we 
     * refer to as the "linked element" because the size and position of 
     * this element is used to determine when the drag and drop objects have 
     * interacted.
     * @property id
     * @type String
     */
    id: null,

    /**
     * Configuration attributes passed into the constructor
     * @property config
     * @type object
     */
    config: null,

    /**
     * The id of the element that will be dragged.  By default this is same 
     * as the linked element , but could be changed to another element. Ex: 
     * YAHOO.util.DDProxy
     * @property dragElId
     * @type String
     * @private
     */
    dragElId: null, 

    /**
     * the id of the element that initiates the drag operation.  By default 
     * this is the linked element, but could be changed to be a child of this
     * element.  This lets us do things like only starting the drag when the 
     * header element within the linked html element is clicked.
     * @property handleElId
     * @type String
     * @private
     */
    handleElId: null, 

    /**
     * An associative array of HTML tags that will be ignored if clicked.
     * @property invalidHandleTypes
     * @type {string: string}
     */
    invalidHandleTypes: null, 

    /**
     * An associative array of ids for elements that will be ignored if clicked
     * @property invalidHandleIds
     * @type {string: string}
     */
    invalidHandleIds: null, 

    /**
     * An indexted array of css class names for elements that will be ignored
     * if clicked.
     * @property invalidHandleClasses
     * @type string[]
     */
    invalidHandleClasses: null, 

    /**
     * The linked element's absolute X position at the time the drag was 
     * started
     * @property startPageX
     * @type int
     * @private
     */
    startPageX: 0,

    /**
     * The linked element's absolute X position at the time the drag was 
     * started
     * @property startPageY
     * @type int
     * @private
     */
    startPageY: 0,

    /**
     * The group defines a logical collection of DragDrop objects that are 
     * related.  Instances only get events when interacting with other 
     * DragDrop object in the same group.  This lets us define multiple 
     * groups using a single DragDrop subclass if we want.
     * @property groups
     * @type {string: string}
     */
    groups: null,

    /**
     * Individual drag/drop instances can be locked.  This will prevent 
     * onmousedown start drag.
     * @property locked
     * @type boolean
     * @private
     */
    locked: false,

    /**
     * Lock this instance
     * @method lock
     */
    lock: function() { this.locked = true; },

    /**
     * Unlock this instace
     * @method unlock
     */
    unlock: function() { this.locked = false; },

    /**
     * By default, all instances can be a drop target.  This can be disabled by
     * setting isTarget to false.
     * @property isTarget
     * @type boolean
     */
    isTarget: true,

    /**
     * The padding configured for this drag and drop object for calculating
     * the drop zone intersection with this object.
     * @property padding
     * @type int[]
     */
    padding: null,
    /**
     * If this flag is true, do not fire drop events. The element is a drag only element (for movement not dropping)
     * @property dragOnly
     * @type Boolean
     */
    dragOnly: false,

    /**
     * If this flag is true, a shim will be placed over the screen/viewable area to track mouse events. Should help with dragging elements over iframes and other controls.
     * @property useShim
     * @type Boolean
     */
    useShim: false,

    /**
     * Cached reference to the linked element
     * @property _domRef
     * @private
     */
    _domRef: null,

    /**
     * Internal typeof flag
     * @property __ygDragDrop
     * @private
     */
    __ygDragDrop: true,

    /**
     * Set to true when horizontal contraints are applied
     * @property constrainX
     * @type boolean
     * @private
     */
    constrainX: false,

    /**
     * Set to true when vertical contraints are applied
     * @property constrainY
     * @type boolean
     * @private
     */
    constrainY: false,

    /**
     * The left constraint
     * @property minX
     * @type int
     * @private
     */
    minX: 0,

    /**
     * The right constraint
     * @property maxX
     * @type int
     * @private
     */
    maxX: 0,

    /**
     * The up constraint 
     * @property minY
     * @type int
     * @type int
     * @private
     */
    minY: 0,

    /**
     * The down constraint 
     * @property maxY
     * @type int
     * @private
     */
    maxY: 0,

    /**
     * The difference between the click position and the source element's location
     * @property deltaX
     * @type int
     * @private
     */
    deltaX: 0,

    /**
     * The difference between the click position and the source element's location
     * @property deltaY
     * @type int
     * @private
     */
    deltaY: 0,

    /**
     * Maintain offsets when we resetconstraints.  Set to true when you want
     * the position of the element relative to its parent to stay the same
     * when the page changes
     *
     * @property maintainOffset
     * @type boolean
     */
    maintainOffset: false,

    /**
     * Array of pixel locations the element will snap to if we specified a 
     * horizontal graduation/interval.  This array is generated automatically
     * when you define a tick interval.
     * @property xTicks
     * @type int[]
     */
    xTicks: null,

    /**
     * Array of pixel locations the element will snap to if we specified a 
     * vertical graduation/interval.  This array is generated automatically 
     * when you define a tick interval.
     * @property yTicks
     * @type int[]
     */
    yTicks: null,

    /**
     * By default the drag and drop instance will only respond to the primary
     * button click (left button for a right-handed mouse).  Set to true to
     * allow drag and drop to start with any mouse click that is propogated
     * by the browser
     * @property primaryButtonOnly
     * @type boolean
     */
    primaryButtonOnly: true,

    /**
     * The availabe property is false until the linked dom element is accessible.
     * @property available
     * @type boolean
     */
    available: false,

    /**
     * By default, drags can only be initiated if the mousedown occurs in the
     * region the linked element is.  This is done in part to work around a
     * bug in some browsers that mis-report the mousedown if the previous
     * mouseup happened outside of the window.  This property is set to true
     * if outer handles are defined.
     *
     * @property hasOuterHandles
     * @type boolean
     * @default false
     */
    hasOuterHandles: false,

    /**
     * Property that is assigned to a drag and drop object when testing to
     * see if it is being targeted by another dd object.  This property
     * can be used in intersect mode to help determine the focus of
     * the mouse interaction.  DDM.getBestMatch uses this property first to
     * determine the closest match in INTERSECT mode when multiple targets
     * are part of the same interaction.
     * @property cursorIsOver
     * @type boolean
     */
    cursorIsOver: false,

    /**
     * Property that is assigned to a drag and drop object when testing to
     * see if it is being targeted by another dd object.  This is a region
     * that represents the area the draggable element overlaps this target.
     * DDM.getBestMatch uses this property to compare the size of the overlap
     * to that of other targets in order to determine the closest match in
     * INTERSECT mode when multiple targets are part of the same interaction.
     * @property overlap 
     * @type YAHOO.util.Region
     */
    overlap: null,

    /**
     * Code that executes immediately before the startDrag event
     * @method b4StartDrag
     * @private
     */
    b4StartDrag: function(x, y) { },

    /**
     * Abstract method called after a drag/drop object is clicked
     * and the drag or mousedown time thresholds have beeen met.
     * @method startDrag
     * @param {int} X click location
     * @param {int} Y click location
     */
    startDrag: function(x, y) { /* override this */ },

    /**
     * Code that executes immediately before the onDrag event
     * @method b4Drag
     * @private
     */
    b4Drag: function(e) { },

    /**
     * Abstract method called during the onMouseMove event while dragging an 
     * object.
     * @method onDrag
     * @param {Event} e the mousemove event
     */
    onDrag: function(e) { /* override this */ },

    /**
     * Abstract method called when this element fist begins hovering over 
     * another DragDrop obj
     * @method onDragEnter
     * @param {Event} e the mousemove event
     * @param {String|DragDrop[]} id In POINT mode, the element
     * id this is hovering over.  In INTERSECT mode, an array of one or more 
     * dragdrop items being hovered over.
     */
    onDragEnter: function(e, id) { /* override this */ },

    /**
     * Code that executes immediately before the onDragOver event
     * @method b4DragOver
     * @private
     */
    b4DragOver: function(e) { },

    /**
     * Abstract method called when this element is hovering over another 
     * DragDrop obj
     * @method onDragOver
     * @param {Event} e the mousemove event
     * @param {String|DragDrop[]} id In POINT mode, the element
     * id this is hovering over.  In INTERSECT mode, an array of dd items 
     * being hovered over.
     */
    onDragOver: function(e, id) { /* override this */ },

    /**
     * Code that executes immediately before the onDragOut event
     * @method b4DragOut
     * @private
     */
    b4DragOut: function(e) { },

    /**
     * Abstract method called when we are no longer hovering over an element
     * @method onDragOut
     * @param {Event} e the mousemove event
     * @param {String|DragDrop[]} id In POINT mode, the element
     * id this was hovering over.  In INTERSECT mode, an array of dd items 
     * that the mouse is no longer over.
     */
    onDragOut: function(e, id) { /* override this */ },

    /**
     * Code that executes immediately before the onDragDrop event
     * @method b4DragDrop
     * @private
     */
    b4DragDrop: function(e) { },

    /**
     * Abstract method called when this item is dropped on another DragDrop 
     * obj
     * @method onDragDrop
     * @param {Event} e the mouseup event
     * @param {String|DragDrop[]} id In POINT mode, the element
     * id this was dropped on.  In INTERSECT mode, an array of dd items this 
     * was dropped on.
     */
    onDragDrop: function(e, id) { /* override this */ },

    /**
     * Abstract method called when this item is dropped on an area with no
     * drop target
     * @method onInvalidDrop
     * @param {Event} e the mouseup event
     */
    onInvalidDrop: function(e) { /* override this */ },

    /**
     * Code that executes immediately before the endDrag event
     * @method b4EndDrag
     * @private
     */
    b4EndDrag: function(e) { },

    /**
     * Fired when we are done dragging the object
     * @method endDrag
     * @param {Event} e the mouseup event
     */
    endDrag: function(e) { /* override this */ },

    /**
     * Code executed immediately before the onMouseDown event
     * @method b4MouseDown
     * @param {Event} e the mousedown event
     * @private
     */
    b4MouseDown: function(e) {  },

    /**
     * Event handler that fires when a drag/drop obj gets a mousedown
     * @method onMouseDown
     * @param {Event} e the mousedown event
     */
    onMouseDown: function(e) { /* override this */ },

    /**
     * Event handler that fires when a drag/drop obj gets a mouseup
     * @method onMouseUp
     * @param {Event} e the mouseup event
     */
    onMouseUp: function(e) { /* override this */ },
   
    /**
     * Override the onAvailable method to do what is needed after the initial
     * position was determined.
     * @method onAvailable
     */
    onAvailable: function () { 
        //this.logger.log("onAvailable (base)"); 
    },

    /**
     * Returns a reference to the linked element
     * @method getEl
     * @return {HTMLElement} the html element 
     */
    getEl: function() { 
        if (!this._domRef) {
            this._domRef = Dom.get(this.id); 
        }

        return this._domRef;
    },

    /**
     * Returns a reference to the actual element to drag.  By default this is
     * the same as the html element, but it can be assigned to another 
     * element. An example of this can be found in YAHOO.util.DDProxy
     * @method getDragEl
     * @return {HTMLElement} the html element 
     */
    getDragEl: function() {
        return Dom.get(this.dragElId);
    },

    /**
     * Sets up the DragDrop object.  Must be called in the constructor of any
     * YAHOO.util.DragDrop subclass
     * @method init
     * @param id the id of the linked element
     * @param {String} sGroup the group of related items
     * @param {object} config configuration attributes
     */
    init: function(id, sGroup, config) {
        this.initTarget(id, sGroup, config);
        Event.on(this._domRef || this.id, "mousedown", 
                        this.handleMouseDown, this, true);

        // Event.on(this.id, "selectstart", Event.preventDefault);
        for (var i in this.events) {
            this.createEvent(i + 'Event');
        }
        
    },

    /**
     * Initializes Targeting functionality only... the object does not
     * get a mousedown handler.
     * @method initTarget
     * @param id the id of the linked element
     * @param {String} sGroup the group of related items
     * @param {object} config configuration attributes
     */
    initTarget: function(id, sGroup, config) {

        // configuration attributes 
        this.config = config || {};

        this.events = {};

        // create a local reference to the drag and drop manager
        this.DDM = YAHOO.util.DDM;

        // initialize the groups object
        this.groups = {};

        // assume that we have an element reference instead of an id if the
        // parameter is not a string
        if (typeof id !== "string") {
            YAHOO.log("id is not a string, assuming it is an HTMLElement");
            this._domRef = id;
            id = Dom.generateId(id);
        }

        // set the id
        this.id = id;

        // add to an interaction group
        this.addToGroup((sGroup) ? sGroup : "default");

        // We don't want to register this as the handle with the manager
        // so we just set the id rather than calling the setter.
        this.handleElId = id;

        Event.onAvailable(id, this.handleOnAvailable, this, true);

        // create a logger instance
        this.logger = (YAHOO.widget.LogWriter) ? 
                new YAHOO.widget.LogWriter(this.toString()) : YAHOO;

        // the linked element is the element that gets dragged by default
        this.setDragElId(id); 

        // by default, clicked anchors will not start drag operations. 
        // @TODO what else should be here?  Probably form fields.
        this.invalidHandleTypes = { A: "A" };
        this.invalidHandleIds = {};
        this.invalidHandleClasses = [];

        this.applyConfig();
    },

    /**
     * Applies the configuration parameters that were passed into the constructor.
     * This is supposed to happen at each level through the inheritance chain.  So
     * a DDProxy implentation will execute apply config on DDProxy, DD, and 
     * DragDrop in order to get all of the parameters that are available in
     * each object.
     * @method applyConfig
     */
    applyConfig: function() {
        this.events = {
            mouseDown: true,
            b4MouseDown: true,
            mouseUp: true,
            b4StartDrag: true,
            startDrag: true,
            b4EndDrag: true,
            endDrag: true,
            drag: true,
            b4Drag: true,
            invalidDrop: true,
            b4DragOut: true,
            dragOut: true,
            dragEnter: true,
            b4DragOver: true,
            dragOver: true,
            b4DragDrop: true,
            dragDrop: true
        };
        
        if (this.config.events) {
            for (var i in this.config.events) {
                if (this.config.events[i] === false) {
                    this.events[i] = false;
                }
            }
        }


        // configurable properties: 
        //    padding, isTarget, maintainOffset, primaryButtonOnly
        this.padding           = this.config.padding || [0, 0, 0, 0];
        this.isTarget          = (this.config.isTarget !== false);
        this.maintainOffset    = (this.config.maintainOffset);
        this.primaryButtonOnly = (this.config.primaryButtonOnly !== false);
        this.dragOnly = ((this.config.dragOnly === true) ? true : false);
        this.useShim = ((this.config.useShim === true) ? true : false);
    },

    /**
     * Executed when the linked element is available
     * @method handleOnAvailable
     * @private
     */
    handleOnAvailable: function() {
        //this.logger.log("handleOnAvailable");
        this.available = true;
        this.resetConstraints();
        this.onAvailable();
    },

     /**
     * Configures the padding for the target zone in px.  Effectively expands
     * (or reduces) the virtual object size for targeting calculations.  
     * Supports css-style shorthand; if only one parameter is passed, all sides
     * will have that padding, and if only two are passed, the top and bottom
     * will have the first param, the left and right the second.
     * @method setPadding
     * @param {int} iTop    Top pad
     * @param {int} iRight  Right pad
     * @param {int} iBot    Bot pad
     * @param {int} iLeft   Left pad
     */
    setPadding: function(iTop, iRight, iBot, iLeft) {
        // this.padding = [iLeft, iRight, iTop, iBot];
        if (!iRight && 0 !== iRight) {
            this.padding = [iTop, iTop, iTop, iTop];
        } else if (!iBot && 0 !== iBot) {
            this.padding = [iTop, iRight, iTop, iRight];
        } else {
            this.padding = [iTop, iRight, iBot, iLeft];
        }
    },

    /**
     * Stores the initial placement of the linked element.
     * @method setInitialPosition
     * @param {int} diffX   the X offset, default 0
     * @param {int} diffY   the Y offset, default 0
     * @private
     */
    setInitPosition: function(diffX, diffY) {
        var el = this.getEl();

        if (!this.DDM.verifyEl(el)) {
            if (el && el.style && (el.style.display == 'none')) {
                this.logger.log(this.id + " can not get initial position, element style is display: none");
            } else {
                this.logger.log(this.id + " element is broken");
            }
            return;
        }

        var dx = diffX || 0;
        var dy = diffY || 0;

        var p = Dom.getXY( el );

        this.initPageX = p[0] - dx;
        this.initPageY = p[1] - dy;

        this.lastPageX = p[0];
        this.lastPageY = p[1];

        this.logger.log(this.id + " initial position: " + this.initPageX + 
                ", " + this.initPageY);


        this.setStartPosition(p);
    },

    /**
     * Sets the start position of the element.  This is set when the obj
     * is initialized, the reset when a drag is started.
     * @method setStartPosition
     * @param pos current position (from previous lookup)
     * @private
     */
    setStartPosition: function(pos) {
        var p = pos || Dom.getXY(this.getEl());

        this.deltaSetXY = null;

        this.startPageX = p[0];
        this.startPageY = p[1];
    },

    /**
     * Add this instance to a group of related drag/drop objects.  All 
     * instances belong to at least one group, and can belong to as many 
     * groups as needed.
     * @method addToGroup
     * @param sGroup {string} the name of the group
     */
    addToGroup: function(sGroup) {
        this.groups[sGroup] = true;
        this.DDM.regDragDrop(this, sGroup);
    },

    /**
     * Remove's this instance from the supplied interaction group
     * @method removeFromGroup
     * @param {string}  sGroup  The group to drop
     */
    removeFromGroup: function(sGroup) {
        this.logger.log("Removing from group: " + sGroup);
        if (this.groups[sGroup]) {
            delete this.groups[sGroup];
        }

        this.DDM.removeDDFromGroup(this, sGroup);
    },

    /**
     * Allows you to specify that an element other than the linked element 
     * will be moved with the cursor during a drag
     * @method setDragElId
     * @param id {string} the id of the element that will be used to initiate the drag
     */
    setDragElId: function(id) {
        this.dragElId = id;
    },

    /**
     * Allows you to specify a child of the linked element that should be 
     * used to initiate the drag operation.  An example of this would be if 
     * you have a content div with text and links.  Clicking anywhere in the 
     * content area would normally start the drag operation.  Use this method
     * to specify that an element inside of the content div is the element 
     * that starts the drag operation.
     * @method setHandleElId
     * @param id {string} the id of the element that will be used to 
     * initiate the drag.
     */
    setHandleElId: function(id) {
        if (typeof id !== "string") {
            YAHOO.log("id is not a string, assuming it is an HTMLElement");
            id = Dom.generateId(id);
        }
        this.handleElId = id;
        this.DDM.regHandle(this.id, id);
    },

    /**
     * Allows you to set an element outside of the linked element as a drag 
     * handle
     * @method setOuterHandleElId
     * @param id the id of the element that will be used to initiate the drag
     */
    setOuterHandleElId: function(id) {
        if (typeof id !== "string") {
            YAHOO.log("id is not a string, assuming it is an HTMLElement");
            id = Dom.generateId(id);
        }
        this.logger.log("Adding outer handle event: " + id);
        Event.on(id, "mousedown", 
                this.handleMouseDown, this, true);
        this.setHandleElId(id);

        this.hasOuterHandles = true;
    },

    /**
     * Remove all drag and drop hooks for this element
     * @method unreg
     */
    unreg: function() {
        this.logger.log("DragDrop obj cleanup " + this.id);
        Event.removeListener(this.id, "mousedown", 
                this.handleMouseDown);
        this._domRef = null;
        this.DDM._remove(this);
    },

    /**
     * Returns true if this instance is locked, or the drag drop mgr is locked
     * (meaning that all drag/drop is disabled on the page.)
     * @method isLocked
     * @return {boolean} true if this obj or all drag/drop is locked, else 
     * false
     */
    isLocked: function() {
        return (this.DDM.isLocked() || this.locked);
    },

    /**
     * Fired when this object is clicked
     * @method handleMouseDown
     * @param {Event} e 
     * @param {YAHOO.util.DragDrop} oDD the clicked dd object (this dd obj)
     * @private
     */
    handleMouseDown: function(e, oDD) {

        var button = e.which || e.button;
        this.logger.log("button: " + button);

        if (this.primaryButtonOnly && button > 1) {
            this.logger.log("Mousedown was not produced by the primary button");
            return;
        }

        if (this.isLocked()) {
            this.logger.log("Drag and drop is disabled, aborting");
            return;
        }

        this.logger.log("mousedown " + this.id);

        this.logger.log("firing onMouseDown events");

        // firing the mousedown events prior to calculating positions
        var b4Return = this.b4MouseDown(e),
        b4Return2 = true;

        if (this.events.b4MouseDown) {
            b4Return2 = this.fireEvent('b4MouseDownEvent', e);
        }
        var mDownReturn = this.onMouseDown(e),
            mDownReturn2 = true;
        if (this.events.mouseDown) {
            if (mDownReturn === false) {
                //Fixes #2528759 - Mousedown function returned false, don't fire the event and cancel everything.
                 mDownReturn2 = false;
            } else {
                mDownReturn2 = this.fireEvent('mouseDownEvent', e);
            }
        }

        if ((b4Return === false) || (mDownReturn === false) || (b4Return2 === false) || (mDownReturn2 === false)) {
            this.logger.log('b4MouseDown or onMouseDown returned false, exiting drag');
            return;
        }

        this.DDM.refreshCache(this.groups);
        // var self = this;
        // setTimeout( function() { self.DDM.refreshCache(self.groups); }, 0);

        // Only process the event if we really clicked within the linked 
        // element.  The reason we make this check is that in the case that 
        // another element was moved between the clicked element and the 
        // cursor in the time between the mousedown and mouseup events. When 
        // this happens, the element gets the next mousedown event 
        // regardless of where on the screen it happened.  
        var pt = new YAHOO.util.Point(Event.getPageX(e), Event.getPageY(e));
        if (!this.hasOuterHandles && !this.DDM.isOverTarget(pt, this) )  {
                this.logger.log("Click was not over the element: " + this.id);
        } else {
            if (this.clickValidator(e)) {

                this.logger.log("click was a valid handle");

                // set the initial element position
                this.setStartPosition();

                // start tracking mousemove distance and mousedown time to
                // determine when to start the actual drag
                this.DDM.handleMouseDown(e, this);

                // this mousedown is mine
                this.DDM.stopEvent(e);
            } else {

this.logger.log("clickValidator returned false, drag not initiated");

            }
        }
    },

    /**
     * @method clickValidator
     * @description Method validates that the clicked element
     * was indeed the handle or a valid child of the handle
     * @param {Event} e 
     */
    clickValidator: function(e) {
        var target = YAHOO.util.Event.getTarget(e);
        return ( this.isValidHandleChild(target) &&
                    (this.id == this.handleElId || 
                        this.DDM.handleWasClicked(target, this.id)) );
    },

    /**
     * Finds the location the element should be placed if we want to move
     * it to where the mouse location less the click offset would place us.
     * @method getTargetCoord
     * @param {int} iPageX the X coordinate of the click
     * @param {int} iPageY the Y coordinate of the click
     * @return an object that contains the coordinates (Object.x and Object.y)
     * @private
     */
    getTargetCoord: function(iPageX, iPageY) {

        // this.logger.log("getTargetCoord: " + iPageX + ", " + iPageY);

        var x = iPageX - this.deltaX;
        var y = iPageY - this.deltaY;

        if (this.constrainX) {
            if (x < this.minX) { x = this.minX; }
            if (x > this.maxX) { x = this.maxX; }
        }

        if (this.constrainY) {
            if (y < this.minY) { y = this.minY; }
            if (y > this.maxY) { y = this.maxY; }
        }

        x = this.getTick(x, this.xTicks);
        y = this.getTick(y, this.yTicks);

        // this.logger.log("getTargetCoord " + 
                // " iPageX: " + iPageX +
                // " iPageY: " + iPageY +
                // " x: " + x + ", y: " + y);

        return {x:x, y:y};
    },

    /**
     * Allows you to specify a tag name that should not start a drag operation
     * when clicked.  This is designed to facilitate embedding links within a
     * drag handle that do something other than start the drag.
     * @method addInvalidHandleType
     * @param {string} tagName the type of element to exclude
     */
    addInvalidHandleType: function(tagName) {
        var type = tagName.toUpperCase();
        this.invalidHandleTypes[type] = type;
    },

    /**
     * Lets you to specify an element id for a child of a drag handle
     * that should not initiate a drag
     * @method addInvalidHandleId
     * @param {string} id the element id of the element you wish to ignore
     */
    addInvalidHandleId: function(id) {
        if (typeof id !== "string") {
            YAHOO.log("id is not a string, assuming it is an HTMLElement");
            id = Dom.generateId(id);
        }
        this.invalidHandleIds[id] = id;
    },


    /**
     * Lets you specify a css class of elements that will not initiate a drag
     * @method addInvalidHandleClass
     * @param {string} cssClass the class of the elements you wish to ignore
     */
    addInvalidHandleClass: function(cssClass) {
        this.invalidHandleClasses.push(cssClass);
    },

    /**
     * Unsets an excluded tag name set by addInvalidHandleType
     * @method removeInvalidHandleType
     * @param {string} tagName the type of element to unexclude
     */
    removeInvalidHandleType: function(tagName) {
        var type = tagName.toUpperCase();
        // this.invalidHandleTypes[type] = null;
        delete this.invalidHandleTypes[type];
    },
    
    /**
     * Unsets an invalid handle id
     * @method removeInvalidHandleId
     * @param {string} id the id of the element to re-enable
     */
    removeInvalidHandleId: function(id) {
        if (typeof id !== "string") {
            YAHOO.log("id is not a string, assuming it is an HTMLElement");
            id = Dom.generateId(id);
        }
        delete this.invalidHandleIds[id];
    },

    /**
     * Unsets an invalid css class
     * @method removeInvalidHandleClass
     * @param {string} cssClass the class of the element(s) you wish to 
     * re-enable
     */
    removeInvalidHandleClass: function(cssClass) {
        for (var i=0, len=this.invalidHandleClasses.length; i<len; ++i) {
            if (this.invalidHandleClasses[i] == cssClass) {
                delete this.invalidHandleClasses[i];
            }
        }
    },

    /**
     * Checks the tag exclusion list to see if this click should be ignored
     * @method isValidHandleChild
     * @param {HTMLElement} node the HTMLElement to evaluate
     * @return {boolean} true if this is a valid tag type, false if not
     */
    isValidHandleChild: function(node) {

        var valid = true;
        // var n = (node.nodeName == "#text") ? node.parentNode : node;
        var nodeName;
        try {
            nodeName = node.nodeName.toUpperCase();
        } catch(e) {
            nodeName = node.nodeName;
        }
        valid = valid && !this.invalidHandleTypes[nodeName];
        valid = valid && !this.invalidHandleIds[node.id];

        for (var i=0, len=this.invalidHandleClasses.length; valid && i<len; ++i) {
            valid = !Dom.hasClass(node, this.invalidHandleClasses[i]);
        }

        this.logger.log("Valid handle? ... " + valid);

        return valid;

    },

    /**
     * Create the array of horizontal tick marks if an interval was specified
     * in setXConstraint().
     * @method setXTicks
     * @private
     */
    setXTicks: function(iStartX, iTickSize) {
        this.xTicks = [];
        this.xTickSize = iTickSize;
        
        var tickMap = {};

        for (var i = this.initPageX; i >= this.minX; i = i - iTickSize) {
            if (!tickMap[i]) {
                this.xTicks[this.xTicks.length] = i;
                tickMap[i] = true;
            }
        }

        for (i = this.initPageX; i <= this.maxX; i = i + iTickSize) {
            if (!tickMap[i]) {
                this.xTicks[this.xTicks.length] = i;
                tickMap[i] = true;
            }
        }

        this.xTicks.sort(this.DDM.numericSort) ;
        this.logger.log("xTicks: " + this.xTicks.join());
    },

    /**
     * Create the array of vertical tick marks if an interval was specified in 
     * setYConstraint().
     * @method setYTicks
     * @private
     */
    setYTicks: function(iStartY, iTickSize) {
        // this.logger.log("setYTicks: " + iStartY + ", " + iTickSize
               // + ", " + this.initPageY + ", " + this.minY + ", " + this.maxY );
        this.yTicks = [];
        this.yTickSize = iTickSize;

        var tickMap = {};

        for (var i = this.initPageY; i >= this.minY; i = i - iTickSize) {
            if (!tickMap[i]) {
                this.yTicks[this.yTicks.length] = i;
                tickMap[i] = true;
            }
        }

        for (i = this.initPageY; i <= this.maxY; i = i + iTickSize) {
            if (!tickMap[i]) {
                this.yTicks[this.yTicks.length] = i;
                tickMap[i] = true;
            }
        }

        this.yTicks.sort(this.DDM.numericSort) ;
        this.logger.log("yTicks: " + this.yTicks.join());
    },

    /**
     * By default, the element can be dragged any place on the screen.  Use 
     * this method to limit the horizontal travel of the element.  Pass in 
     * 0,0 for the parameters if you want to lock the drag to the y axis.
     * @method setXConstraint
     * @param {int} iLeft the number of pixels the element can move to the left
     * @param {int} iRight the number of pixels the element can move to the 
     * right
     * @param {int} iTickSize optional parameter for specifying that the 
     * element
     * should move iTickSize pixels at a time.
     */
    setXConstraint: function(iLeft, iRight, iTickSize) {
        this.leftConstraint = parseInt(iLeft, 10);
        this.rightConstraint = parseInt(iRight, 10);

        this.minX = this.initPageX - this.leftConstraint;
        this.maxX = this.initPageX + this.rightConstraint;
        if (iTickSize) { this.setXTicks(this.initPageX, iTickSize); }

        this.constrainX = true;
        this.logger.log("initPageX:" + this.initPageX + " minX:" + this.minX + 
                " maxX:" + this.maxX);
    },

    /**
     * Clears any constraints applied to this instance.  Also clears ticks
     * since they can't exist independent of a constraint at this time.
     * @method clearConstraints
     */
    clearConstraints: function() {
        this.logger.log("Clearing constraints");
        this.constrainX = false;
        this.constrainY = false;
        this.clearTicks();
    },

    /**
     * Clears any tick interval defined for this instance
     * @method clearTicks
     */
    clearTicks: function() {
        this.logger.log("Clearing ticks");
        this.xTicks = null;
        this.yTicks = null;
        this.xTickSize = 0;
        this.yTickSize = 0;
    },

    /**
     * By default, the element can be dragged any place on the screen.  Set 
     * this to limit the vertical travel of the element.  Pass in 0,0 for the
     * parameters if you want to lock the drag to the x axis.
     * @method setYConstraint
     * @param {int} iUp the number of pixels the element can move up
     * @param {int} iDown the number of pixels the element can move down
     * @param {int} iTickSize optional parameter for specifying that the 
     * element should move iTickSize pixels at a time.
     */
    setYConstraint: function(iUp, iDown, iTickSize) {
        this.logger.log("setYConstraint: " + iUp + "," + iDown + "," + iTickSize);
        this.topConstraint = parseInt(iUp, 10);
        this.bottomConstraint = parseInt(iDown, 10);

        this.minY = this.initPageY - this.topConstraint;
        this.maxY = this.initPageY + this.bottomConstraint;
        if (iTickSize) { this.setYTicks(this.initPageY, iTickSize); }

        this.constrainY = true;
        
        this.logger.log("initPageY:" + this.initPageY + " minY:" + this.minY + 
                " maxY:" + this.maxY);
    },

    /**
     * resetConstraints must be called if you manually reposition a dd element.
     * @method resetConstraints
     */
    resetConstraints: function() {

        //this.logger.log("resetConstraints");

        // Maintain offsets if necessary
        if (this.initPageX || this.initPageX === 0) {
            //this.logger.log("init pagexy: " + this.initPageX + ", " + 
                               //this.initPageY);
            //this.logger.log("last pagexy: " + this.lastPageX + ", " + 
                               //this.lastPageY);
            // figure out how much this thing has moved
            var dx = (this.maintainOffset) ? this.lastPageX - this.initPageX : 0;
            var dy = (this.maintainOffset) ? this.lastPageY - this.initPageY : 0;

            this.setInitPosition(dx, dy);

        // This is the first time we have detected the element's position
        } else {
            this.setInitPosition();
        }

        if (this.constrainX) {
            this.setXConstraint( this.leftConstraint, 
                                 this.rightConstraint, 
                                 this.xTickSize        );
        }

        if (this.constrainY) {
            this.setYConstraint( this.topConstraint, 
                                 this.bottomConstraint, 
                                 this.yTickSize         );
        }
    },

    /**
     * Normally the drag element is moved pixel by pixel, but we can specify 
     * that it move a number of pixels at a time.  This method resolves the 
     * location when we have it set up like this.
     * @method getTick
     * @param {int} val where we want to place the object
     * @param {int[]} tickArray sorted array of valid points
     * @return {int} the closest tick
     * @private
     */
    getTick: function(val, tickArray) {

        if (!tickArray) {
            // If tick interval is not defined, it is effectively 1 pixel, 
            // so we return the value passed to us.
            return val; 
        } else if (tickArray[0] >= val) {
            // The value is lower than the first tick, so we return the first
            // tick.
            return tickArray[0];
        } else {
            for (var i=0, len=tickArray.length; i<len; ++i) {
                var next = i + 1;
                if (tickArray[next] && tickArray[next] >= val) {
                    var diff1 = val - tickArray[i];
                    var diff2 = tickArray[next] - val;
                    return (diff2 > diff1) ? tickArray[i] : tickArray[next];
                }
            }

            // The value is larger than the last tick, so we return the last
            // tick.
            return tickArray[tickArray.length - 1];
        }
    },

    /**
     * toString method
     * @method toString
     * @return {string} string representation of the dd obj
     */
    toString: function() {
        return ("DragDrop " + this.id);
    }

};
YAHOO.augment(YAHOO.util.DragDrop, YAHOO.util.EventProvider);

/**
* @event mouseDownEvent
* @description Provides access to the mousedown event. The mousedown does not always result in a drag operation.
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/

/**
* @event b4MouseDownEvent
* @description Provides access to the mousedown event, before the mouseDownEvent gets fired. Returning false will cancel the drag.
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/

/**
* @event mouseUpEvent
* @description Fired from inside DragDropMgr when the drag operation is finished.
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/

/**
* @event b4StartDragEvent
* @description Fires before the startDragEvent, returning false will cancel the startDrag Event.
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/

/**
* @event startDragEvent
* @description Occurs after a mouse down and the drag threshold has been met. The drag threshold default is either 3 pixels of mouse movement or 1 full second of holding the mousedown. 
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/

/**
* @event b4EndDragEvent
* @description Fires before the endDragEvent. Returning false will cancel.
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/

/**
* @event endDragEvent
* @description Fires on the mouseup event after a drag has been initiated (startDrag fired).
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/

/**
* @event dragEvent
* @description Occurs every mousemove event while dragging.
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/
/**
* @event b4DragEvent
* @description Fires before the dragEvent.
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/
/**
* @event invalidDropEvent
* @description Fires when the dragged objects is dropped in a location that contains no drop targets.
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/
/**
* @event b4DragOutEvent
* @description Fires before the dragOutEvent
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/
/**
* @event dragOutEvent
* @description Fires when a dragged object is no longer over an object that had the onDragEnter fire. 
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/
/**
* @event dragEnterEvent
* @description Occurs when the dragged object first interacts with another targettable drag and drop object.
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/
/**
* @event b4DragOverEvent
* @description Fires before the dragOverEvent.
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/
/**
* @event dragOverEvent
* @description Fires every mousemove event while over a drag and drop object.
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/
/**
* @event b4DragDropEvent 
* @description Fires before the dragDropEvent
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/
/**
* @event dragDropEvent
* @description Fires when the dragged objects is dropped on another.
* @type YAHOO.util.CustomEvent See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
*/
})();
