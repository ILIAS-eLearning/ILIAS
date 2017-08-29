/**
 * The drag and drop utility provides a framework for building drag and drop
 * applications.  In addition to enabling drag and drop for specific elements,
 * the drag and drop elements are tracked by the manager class, and the
 * interactions between the various elements are tracked during the drag and
 * the implementing code is notified about these important moments.
 * @module dragdrop
 * @title Drag and Drop
 * @requires yahoo,dom,event
 * @namespace YAHOO.util
 */

// Only load the library once.  Rewriting the manager class would orphan 
// existing drag and drop instances.
if (!YAHOO.util.DragDropMgr) {

/**
 * DragDropMgr is a singleton that tracks the element interaction for 
 * all DragDrop items in the window.  Generally, you will not call 
 * this class directly, but it does have helper methods that could 
 * be useful in your DragDrop implementations.
 * @class DragDropMgr
 * @static
 */
YAHOO.util.DragDropMgr = function() {

    var Event = YAHOO.util.Event,
        Dom = YAHOO.util.Dom;

    return {
        /**
        * This property is used to turn on global use of the shim element on all DragDrop instances, defaults to false for backcompat. (Use: YAHOO.util.DDM.useShim = true)
        * @property useShim
        * @type Boolean
        * @static
        */
        useShim: false,
        /**
        * This property is used to determine if the shim is active over the screen, default false.
        * @private
        * @property _shimActive
        * @type Boolean
        * @static
        */
        _shimActive: false,
        /**
        * This property is used when useShim is set on a DragDrop object to store the current state of DDM.useShim so it can be reset when a drag operation is done.
        * @private
        * @property _shimState
        * @type Boolean
        * @static
        */
        _shimState: false,
        /**
        * This property is used when useShim is set to true, it will set the opacity on the shim to .5 for debugging. Use: (YAHOO.util.DDM._debugShim = true;)
        * @private
        * @property _debugShim
        * @type Boolean
        * @static
        */
        _debugShim: false,
        /**
        * This method will create a shim element (giving it the id of yui-ddm-shim), it also attaches the mousemove and mouseup listeners to it and attaches a scroll listener on the window
        * @private
        * @method _sizeShim
        * @static
        */
        _createShim: function() {
            YAHOO.log('Creating Shim Element', 'info', 'DragDropMgr');
            var s = document.createElement('div');
            s.id = 'yui-ddm-shim';
            if (document.body.firstChild) {
                document.body.insertBefore(s, document.body.firstChild);
            } else {
                document.body.appendChild(s);
            }
            s.style.display = 'none';
            s.style.backgroundColor = 'red';
            s.style.position = 'absolute';
            s.style.zIndex = '99999';
            Dom.setStyle(s, 'opacity', '0');
            this._shim = s;
            Event.on(s, "mouseup",   this.handleMouseUp, this, true);
            Event.on(s, "mousemove", this.handleMouseMove, this, true);
            Event.on(window, 'scroll', this._sizeShim, this, true);
        },
        /**
        * This method will size the shim, called from activate and on window scroll event
        * @private
        * @method _sizeShim
        * @static
        */
        _sizeShim: function() {
            if (this._shimActive) {
                YAHOO.log('Sizing Shim', 'info', 'DragDropMgr');
                var s = this._shim;
                s.style.height = Dom.getDocumentHeight() + 'px';
                s.style.width = Dom.getDocumentWidth() + 'px';
                s.style.top = '0';
                s.style.left = '0';
            }
        },
        /**
        * This method will create the shim element if needed, then show the shim element, size the element and set the _shimActive property to true
        * @private
        * @method _activateShim
        * @static
        */
        _activateShim: function() {
            if (this.useShim) {
                YAHOO.log('Activating Shim', 'info', 'DragDropMgr');
                if (!this._shim) {
                    this._createShim();
                }
                this._shimActive = true;
                var s = this._shim,
                    o = '0';
                if (this._debugShim) {
                    o = '.5';
                }
                Dom.setStyle(s, 'opacity', o);
                this._sizeShim();
                s.style.display = 'block';
            }
        },
        /**
        * This method will hide the shim element and set the _shimActive property to false
        * @private
        * @method _deactivateShim
        * @static
        */
        _deactivateShim: function() {
            YAHOO.log('Deactivating Shim', 'info', 'DragDropMgr');
            this._shim.style.display = 'none';
            this._shimActive = false;
        },
        /**
        * The HTML element created to use as a shim over the document to track mouse movements
        * @private
        * @property _shim
        * @type HTMLElement
        * @static
        */
        _shim: null,
        /**
         * Two dimensional Array of registered DragDrop objects.  The first 
         * dimension is the DragDrop item group, the second the DragDrop 
         * object.
         * @property ids
         * @type {string: string}
         * @private
         * @static
         */
        ids: {},

        /**
         * Array of element ids defined as drag handles.  Used to determine 
         * if the element that generated the mousedown event is actually the 
         * handle and not the html element itself.
         * @property handleIds
         * @type {string: string}
         * @private
         * @static
         */
        handleIds: {},

        /**
         * the DragDrop object that is currently being dragged
         * @property dragCurrent
         * @type DragDrop
         * @private
         * @static
         **/
        dragCurrent: null,

        /**
         * the DragDrop object(s) that are being hovered over
         * @property dragOvers
         * @type Array
         * @private
         * @static
         */
        dragOvers: {},

        /**
         * the X distance between the cursor and the object being dragged
         * @property deltaX
         * @type int
         * @private
         * @static
         */
        deltaX: 0,

        /**
         * the Y distance between the cursor and the object being dragged
         * @property deltaY
         * @type int
         * @private
         * @static
         */
        deltaY: 0,

        /**
         * Flag to determine if we should prevent the default behavior of the
         * events we define. By default this is true, but this can be set to 
         * false if you need the default behavior (not recommended)
         * @property preventDefault
         * @type boolean
         * @static
         */
        preventDefault: true,

        /**
         * Flag to determine if we should stop the propagation of the events 
         * we generate. This is true by default but you may want to set it to
         * false if the html element contains other features that require the
         * mouse click.
         * @property stopPropagation
         * @type boolean
         * @static
         */
        stopPropagation: true,

        /**
         * Internal flag that is set to true when drag and drop has been
         * initialized
         * @property initialized
         * @private
         * @static
         */
        initialized: false,

        /**
         * All drag and drop can be disabled.
         * @property locked
         * @private
         * @static
         */
        locked: false,

        /**
         * Provides additional information about the the current set of
         * interactions.  Can be accessed from the event handlers. It
         * contains the following properties:
         *
         *       out:       onDragOut interactions
         *       enter:     onDragEnter interactions
         *       over:      onDragOver interactions
         *       drop:      onDragDrop interactions
         *       point:     The location of the cursor
         *       draggedRegion: The location of dragged element at the time
         *                      of the interaction
         *       sourceRegion: The location of the source elemtn at the time
         *                     of the interaction
         *       validDrop: boolean
         * @property interactionInfo
         * @type object
         * @static
         */
        interactionInfo: null,

        /**
         * Called the first time an element is registered.
         * @method init
         * @private
         * @static
         */
        init: function() {
            this.initialized = true;
        },

        /**
         * In point mode, drag and drop interaction is defined by the 
         * location of the cursor during the drag/drop
         * @property POINT
         * @type int
         * @static
         * @final
         */
        POINT: 0,

        /**
         * In intersect mode, drag and drop interaction is defined by the 
         * cursor position or the amount of overlap of two or more drag and 
         * drop objects.
         * @property INTERSECT
         * @type int
         * @static
         * @final
         */
        INTERSECT: 1,

        /**
         * In intersect mode, drag and drop interaction is defined only by the 
         * overlap of two or more drag and drop objects.
         * @property STRICT_INTERSECT
         * @type int
         * @static
         * @final
         */
        STRICT_INTERSECT: 2,

        /**
         * The current drag and drop mode.  Default: POINT
         * @property mode
         * @type int
         * @static
         */
        mode: 0,

        /**
         * Runs method on all drag and drop objects
         * @method _execOnAll
         * @private
         * @static
         */
        _execOnAll: function(sMethod, args) {
            for (var i in this.ids) {
                for (var j in this.ids[i]) {
                    var oDD = this.ids[i][j];
                    if (! this.isTypeOfDD(oDD)) {
                        continue;
                    }
                    oDD[sMethod].apply(oDD, args);
                }
            }
        },

        /**
         * Drag and drop initialization.  Sets up the global event handlers
         * @method _onLoad
         * @private
         * @static
         */
        _onLoad: function() {

            this.init();

            YAHOO.log("DragDropMgr onload", "info", "DragDropMgr");
            Event.on(document, "mouseup",   this.handleMouseUp, this, true);
            Event.on(document, "mousemove", this.handleMouseMove, this, true);
            Event.on(window,   "unload",    this._onUnload, this, true);
            Event.on(window,   "resize",    this._onResize, this, true);
            // Event.on(window,   "mouseout",    this._test);

        },

        /**
         * Reset constraints on all drag and drop objs
         * @method _onResize
         * @private
         * @static
         */
        _onResize: function(e) {
            YAHOO.log("window resize", "info", "DragDropMgr");
            this._execOnAll("resetConstraints", []);
        },

        /**
         * Lock all drag and drop functionality
         * @method lock
         * @static
         */
        lock: function() { this.locked = true; },

        /**
         * Unlock all drag and drop functionality
         * @method unlock
         * @static
         */
        unlock: function() { this.locked = false; },

        /**
         * Is drag and drop locked?
         * @method isLocked
         * @return {boolean} True if drag and drop is locked, false otherwise.
         * @static
         */
        isLocked: function() { return this.locked; },

        /**
         * Location cache that is set for all drag drop objects when a drag is
         * initiated, cleared when the drag is finished.
         * @property locationCache
         * @private
         * @static
         */
        locationCache: {},

        /**
         * Set useCache to false if you want to force object the lookup of each
         * drag and drop linked element constantly during a drag.
         * @property useCache
         * @type boolean
         * @static
         */
        useCache: true,

        /**
         * The number of pixels that the mouse needs to move after the 
         * mousedown before the drag is initiated.  Default=3;
         * @property clickPixelThresh
         * @type int
         * @static
         */
        clickPixelThresh: 3,

        /**
         * The number of milliseconds after the mousedown event to initiate the
         * drag if we don't get a mouseup event. Default=1000
         * @property clickTimeThresh
         * @type int
         * @static
         */
        clickTimeThresh: 1000,

        /**
         * Flag that indicates that either the drag pixel threshold or the 
         * mousdown time threshold has been met
         * @property dragThreshMet
         * @type boolean
         * @private
         * @static
         */
        dragThreshMet: false,

        /**
         * Timeout used for the click time threshold
         * @property clickTimeout
         * @type Object
         * @private
         * @static
         */
        clickTimeout: null,

        /**
         * The X position of the mousedown event stored for later use when a 
         * drag threshold is met.
         * @property startX
         * @type int
         * @private
         * @static
         */
        startX: 0,

        /**
         * The Y position of the mousedown event stored for later use when a 
         * drag threshold is met.
         * @property startY
         * @type int
         * @private
         * @static
         */
        startY: 0,

        /**
         * Flag to determine if the drag event was fired from the click timeout and
         * not the mouse move threshold.
         * @property fromTimeout
         * @type boolean
         * @private
         * @static
         */
        fromTimeout: false,

        /**
         * Each DragDrop instance must be registered with the DragDropMgr.  
         * This is executed in DragDrop.init()
         * @method regDragDrop
         * @param {DragDrop} oDD the DragDrop object to register
         * @param {String} sGroup the name of the group this element belongs to
         * @static
         */
        regDragDrop: function(oDD, sGroup) {
            if (!this.initialized) { this.init(); }
            
            if (!this.ids[sGroup]) {
                this.ids[sGroup] = {};
            }
            this.ids[sGroup][oDD.id] = oDD;
        },

        /**
         * Removes the supplied dd instance from the supplied group. Executed
         * by DragDrop.removeFromGroup, so don't call this function directly.
         * @method removeDDFromGroup
         * @private
         * @static
         */
        removeDDFromGroup: function(oDD, sGroup) {
            if (!this.ids[sGroup]) {
                this.ids[sGroup] = {};
            }

            var obj = this.ids[sGroup];
            if (obj && obj[oDD.id]) {
                delete obj[oDD.id];
            }
        },

        /**
         * Unregisters a drag and drop item.  This is executed in 
         * DragDrop.unreg, use that method instead of calling this directly.
         * @method _remove
         * @private
         * @static
         */
        _remove: function(oDD) {
            for (var g in oDD.groups) {
                if (g) {
                    var item = this.ids[g];
                    if (item && item[oDD.id]) {
                        delete item[oDD.id];
                    }
                }
                
            }
            delete this.handleIds[oDD.id];
        },

        /**
         * Each DragDrop handle element must be registered.  This is done
         * automatically when executing DragDrop.setHandleElId()
         * @method regHandle
         * @param {String} sDDId the DragDrop id this element is a handle for
         * @param {String} sHandleId the id of the element that is the drag 
         * handle
         * @static
         */
        regHandle: function(sDDId, sHandleId) {
            if (!this.handleIds[sDDId]) {
                this.handleIds[sDDId] = {};
            }
            this.handleIds[sDDId][sHandleId] = sHandleId;
        },

        /**
         * Utility function to determine if a given element has been 
         * registered as a drag drop item.
         * @method isDragDrop
         * @param {String} id the element id to check
         * @return {boolean} true if this element is a DragDrop item, 
         * false otherwise
         * @static
         */
        isDragDrop: function(id) {
            return ( this.getDDById(id) ) ? true : false;
        },

        /**
         * Returns the drag and drop instances that are in all groups the
         * passed in instance belongs to.
         * @method getRelated
         * @param {DragDrop} p_oDD the obj to get related data for
         * @param {boolean} bTargetsOnly if true, only return targetable objs
         * @return {DragDrop[]} the related instances
         * @static
         */
        getRelated: function(p_oDD, bTargetsOnly) {
            var oDDs = [];
            for (var i in p_oDD.groups) {
                for (var j in this.ids[i]) {
                    var dd = this.ids[i][j];
                    if (! this.isTypeOfDD(dd)) {
                        continue;
                    }
                    if (!bTargetsOnly || dd.isTarget) {
                        oDDs[oDDs.length] = dd;
                    }
                }
            }

            return oDDs;
        },

        /**
         * Returns true if the specified dd target is a legal target for 
         * the specifice drag obj
         * @method isLegalTarget
         * @param {DragDrop} the drag obj
         * @param {DragDrop} the target
         * @return {boolean} true if the target is a legal target for the 
         * dd obj
         * @static
         */
        isLegalTarget: function (oDD, oTargetDD) {
            var targets = this.getRelated(oDD, true);
            for (var i=0, len=targets.length;i<len;++i) {
                if (targets[i].id == oTargetDD.id) {
                    return true;
                }
            }

            return false;
        },

        /**
         * My goal is to be able to transparently determine if an object is
         * typeof DragDrop, and the exact subclass of DragDrop.  typeof 
         * returns "object", oDD.constructor.toString() always returns
         * "DragDrop" and not the name of the subclass.  So for now it just
         * evaluates a well-known variable in DragDrop.
         * @method isTypeOfDD
         * @param {Object} the object to evaluate
         * @return {boolean} true if typeof oDD = DragDrop
         * @static
         */
        isTypeOfDD: function (oDD) {
            return (oDD && oDD.__ygDragDrop);
        },

        /**
         * Utility function to determine if a given element has been 
         * registered as a drag drop handle for the given Drag Drop object.
         * @method isHandle
         * @param {String} id the element id to check
         * @return {boolean} true if this element is a DragDrop handle, false 
         * otherwise
         * @static
         */
        isHandle: function(sDDId, sHandleId) {
            return ( this.handleIds[sDDId] && 
                            this.handleIds[sDDId][sHandleId] );
        },

        /**
         * Returns the DragDrop instance for a given id
         * @method getDDById
         * @param {String} id the id of the DragDrop object
         * @return {DragDrop} the drag drop object, null if it is not found
         * @static
         */
        getDDById: function(id) {
            for (var i in this.ids) {
                if (this.ids[i][id]) {
                    return this.ids[i][id];
                }
            }
            return null;
        },

        /**
         * Fired after a registered DragDrop object gets the mousedown event.
         * Sets up the events required to track the object being dragged
         * @method handleMouseDown
         * @param {Event} e the event
         * @param oDD the DragDrop object being dragged
         * @private
         * @static
         */
        handleMouseDown: function(e, oDD) {
            //this._activateShim();

            this.currentTarget = YAHOO.util.Event.getTarget(e);

            this.dragCurrent = oDD;

            var el = oDD.getEl();

            // track start position
            this.startX = YAHOO.util.Event.getPageX(e);
            this.startY = YAHOO.util.Event.getPageY(e);

            this.deltaX = this.startX - el.offsetLeft;
            this.deltaY = this.startY - el.offsetTop;

            this.dragThreshMet = false;

            this.clickTimeout = setTimeout( 
                    function() { 
                        var DDM = YAHOO.util.DDM;
                        DDM.startDrag(DDM.startX, DDM.startY);
                        DDM.fromTimeout = true;
                    }, 
                    this.clickTimeThresh );
        },

        /**
         * Fired when either the drag pixel threshold or the mousedown hold 
         * time threshold has been met.
         * @method startDrag
         * @param x {int} the X position of the original mousedown
         * @param y {int} the Y position of the original mousedown
         * @static
         */
        startDrag: function(x, y) {
            if (this.dragCurrent && this.dragCurrent.useShim) {
                this._shimState = this.useShim;
                this.useShim = true;
            }
            this._activateShim();
            YAHOO.log("firing drag start events", "info", "DragDropMgr");
            clearTimeout(this.clickTimeout);
            var dc = this.dragCurrent;
            if (dc && dc.events.b4StartDrag) {
                dc.b4StartDrag(x, y);
                dc.fireEvent('b4StartDragEvent', { x: x, y: y });
            }
            if (dc && dc.events.startDrag) {
                dc.startDrag(x, y);
                dc.fireEvent('startDragEvent', { x: x, y: y });
            }
            this.dragThreshMet = true;
        },

        /**
         * Internal function to handle the mouseup event.  Will be invoked 
         * from the context of the document.
         * @method handleMouseUp
         * @param {Event} e the event
         * @private
         * @static
         */
        handleMouseUp: function(e) {
            if (this.dragCurrent) {
                clearTimeout(this.clickTimeout);

                if (this.dragThreshMet) {
                    YAHOO.log("mouseup detected - completing drag", "info", "DragDropMgr");
                    if (this.fromTimeout) {
                        YAHOO.log('fromTimeout is true (mouse didn\'t move), call handleMouseMove so we can get the dragOver event', 'info', 'DragDropMgr');
                        this.fromTimeout = false;
                        this.handleMouseMove(e);
                    }
                    this.fromTimeout = false;
                    this.fireEvents(e, true);
                } else {
                    YAHOO.log("drag threshold not met", "info", "DragDropMgr");
                }

                this.stopDrag(e);

                this.stopEvent(e);
            }
        },

        /**
         * Utility to stop event propagation and event default, if these 
         * features are turned on.
         * @method stopEvent
         * @param {Event} e the event as returned by this.getEvent()
         * @static
         */
        stopEvent: function(e) {
            if (this.stopPropagation) {
                YAHOO.util.Event.stopPropagation(e);
            }

            if (this.preventDefault) {
                YAHOO.util.Event.preventDefault(e);
            }
        },

        /** 
         * Ends the current drag, cleans up the state, and fires the endDrag
         * and mouseUp events.  Called internally when a mouseup is detected
         * during the drag.  Can be fired manually during the drag by passing
         * either another event (such as the mousemove event received in onDrag)
         * or a fake event with pageX and pageY defined (so that endDrag and
         * onMouseUp have usable position data.).  Alternatively, pass true
         * for the silent parameter so that the endDrag and onMouseUp events
         * are skipped (so no event data is needed.)
         *
         * @method stopDrag
         * @param {Event} e the mouseup event, another event (or a fake event) 
         *                  with pageX and pageY defined, or nothing if the 
         *                  silent parameter is true
         * @param {boolean} silent skips the enddrag and mouseup events if true
         * @static
         */
        stopDrag: function(e, silent) {
            // YAHOO.log("mouseup - removing event handlers");
            var dc = this.dragCurrent;
            // Fire the drag end event for the item that was dragged
            if (dc && !silent) {
                if (this.dragThreshMet) {
                    YAHOO.log("firing endDrag events", "info", "DragDropMgr");
                    if (dc.events.b4EndDrag) {
                        dc.b4EndDrag(e);
                        dc.fireEvent('b4EndDragEvent', { e: e });
                    }
                    if (dc.events.endDrag) {
                        dc.endDrag(e);
                        dc.fireEvent('endDragEvent', { e: e });
                    }
                }
                if (dc.events.mouseUp) {
                    YAHOO.log("firing dragdrop onMouseUp event", "info", "DragDropMgr");
                    dc.onMouseUp(e);
                    dc.fireEvent('mouseUpEvent', { e: e });
                }
            }

            if (this._shimActive) {
                this._deactivateShim();
                if (this.dragCurrent && this.dragCurrent.useShim) {
                    this.useShim = this._shimState;
                    this._shimState = false;
                }
            }

            this.dragCurrent = null;
            this.dragOvers = {};
        },

        /** 
         * Internal function to handle the mousemove event.  Will be invoked 
         * from the context of the html element.
         *
         * @TODO figure out what we can do about mouse events lost when the 
         * user drags objects beyond the window boundary.  Currently we can 
         * detect this in internet explorer by verifying that the mouse is 
         * down during the mousemove event.  Firefox doesn't give us the 
         * button state on the mousemove event.
         * @method handleMouseMove
         * @param {Event} e the event
         * @private
         * @static
         */
        handleMouseMove: function(e) {
            //YAHOO.log("handlemousemove");

            var dc = this.dragCurrent;
            if (dc) {
                // YAHOO.log("no current drag obj");

                // var button = e.which || e.button;
                // YAHOO.log("which: " + e.which + ", button: "+ e.button);

                // check for IE < 9 mouseup outside of page boundary
                if (YAHOO.env.ua.ie && (YAHOO.env.ua.ie < 9) && !e.button) {
                    YAHOO.log("button failure", "info", "DragDropMgr");
                    this.stopEvent(e);
                    return this.handleMouseUp(e);
                } else {
                    if (e.clientX < 0 || e.clientY < 0) {
                        //This will stop the element from leaving the viewport in FF, Opera & Safari
                        //Not turned on yet
                        //YAHOO.log("Either clientX or clientY is negative, stop the event.", "info", "DragDropMgr");
                        //this.stopEvent(e);
                        //return false;
                    }
                }

                if (!this.dragThreshMet) {
                    var diffX = Math.abs(this.startX - YAHOO.util.Event.getPageX(e));
                    var diffY = Math.abs(this.startY - YAHOO.util.Event.getPageY(e));
                    // YAHOO.log("diffX: " + diffX + "diffY: " + diffY);
                    if (diffX > this.clickPixelThresh || 
                                diffY > this.clickPixelThresh) {
                        YAHOO.log("pixel threshold met", "info", "DragDropMgr");
                        this.startDrag(this.startX, this.startY);
                    }
                }

                if (this.dragThreshMet) {
                    if (dc && dc.events.b4Drag) {
                        dc.b4Drag(e);
                        dc.fireEvent('b4DragEvent', { e: e});
                    }
                    if (dc && dc.events.drag) {
                        dc.onDrag(e);
                        dc.fireEvent('dragEvent', { e: e});
                    }
                    if (dc) {
                        this.fireEvents(e, false);
                    }
                }

                this.stopEvent(e);
            }
        },
        
        /**
         * Iterates over all of the DragDrop elements to find ones we are 
         * hovering over or dropping on
         * @method fireEvents
         * @param {Event} e the event
         * @param {boolean} isDrop is this a drop op or a mouseover op?
         * @private
         * @static
         */
        fireEvents: function(e, isDrop) {
            var dc = this.dragCurrent;

            // If the user did the mouse up outside of the window, we could 
            // get here even though we have ended the drag.
            // If the config option dragOnly is true, bail out and don't fire the events
            if (!dc || dc.isLocked() || dc.dragOnly) {
                return;
            }

            var x = YAHOO.util.Event.getPageX(e),
                y = YAHOO.util.Event.getPageY(e),
                pt = new YAHOO.util.Point(x,y),
                pos = dc.getTargetCoord(pt.x, pt.y),
                el = dc.getDragEl(),
                events = ['out', 'over', 'drop', 'enter'],
                curRegion = new YAHOO.util.Region( pos.y, 
                                               pos.x + el.offsetWidth,
                                               pos.y + el.offsetHeight, 
                                               pos.x ),
            
                oldOvers = [], // cache the previous dragOver array
                inGroupsObj  = {},
                b4Results = {},
                inGroups  = [],
                data = {
                    outEvts: [],
                    overEvts: [],
                    dropEvts: [],
                    enterEvts: []
                };


            // Check to see if the object(s) we were hovering over is no longer 
            // being hovered over so we can fire the onDragOut event
            for (var i in this.dragOvers) {

                var ddo = this.dragOvers[i];

                if (! this.isTypeOfDD(ddo)) {
                    continue;
                }
                if (! this.isOverTarget(pt, ddo, this.mode, curRegion)) {
                    data.outEvts.push( ddo );
                }

                oldOvers[i] = true;
                delete this.dragOvers[i];
            }

            for (var sGroup in dc.groups) {
                // YAHOO.log("Processing group " + sGroup);
                
                if ("string" != typeof sGroup) {
                    continue;
                }

                for (i in this.ids[sGroup]) {
                    var oDD = this.ids[sGroup][i];
                    if (! this.isTypeOfDD(oDD)) {
                        continue;
                    }

                    if (oDD.isTarget && !oDD.isLocked() && oDD != dc) {
                        if (this.isOverTarget(pt, oDD, this.mode, curRegion)) {
                            inGroupsObj[sGroup] = true;
                            // look for drop interactions
                            if (isDrop) {
                                data.dropEvts.push( oDD );
                            // look for drag enter and drag over interactions
                            } else {

                                // initial drag over: dragEnter fires
                                if (!oldOvers[oDD.id]) {
                                    data.enterEvts.push( oDD );
                                // subsequent drag overs: dragOver fires
                                } else {
                                    data.overEvts.push( oDD );
                                }

                                this.dragOvers[oDD.id] = oDD;
                            }
                        }
                    }
                }
            }

            this.interactionInfo = {
                out:       data.outEvts,
                enter:     data.enterEvts,
                over:      data.overEvts,
                drop:      data.dropEvts,
                point:     pt,
                draggedRegion:    curRegion,
                sourceRegion: this.locationCache[dc.id],
                validDrop: isDrop
            };

            
            for (var inG in inGroupsObj) {
                inGroups.push(inG);
            }

            // notify about a drop that did not find a target
            if (isDrop && !data.dropEvts.length) {
                YAHOO.log(dc.id + " dropped, but not on a target", "info", "DragDropMgr");
                this.interactionInfo.validDrop = false;
                if (dc.events.invalidDrop) {
                    dc.onInvalidDrop(e);
                    dc.fireEvent('invalidDropEvent', { e: e });
                }
            }
            for (i = 0; i < events.length; i++) {
                var tmp = null;
                if (data[events[i] + 'Evts']) {
                    tmp = data[events[i] + 'Evts'];
                }
                if (tmp && tmp.length) {
                    var type = events[i].charAt(0).toUpperCase() + events[i].substr(1),
                        ev = 'onDrag' + type,
                        b4 = 'b4Drag' + type,
                        cev = 'drag' + type + 'Event',
                        check = 'drag' + type;
                    if (this.mode) {
                        YAHOO.log(dc.id + ' ' + ev + ': ' + tmp, "info", "DragDropMgr");
                        if (dc.events[b4]) {
                            dc[b4](e, tmp, inGroups);
                            b4Results[ev] = dc.fireEvent(b4 + 'Event', { event: e, info: tmp, group: inGroups });
                            
                        }
                        if (dc.events[check] && (b4Results[ev] !== false)) {
                            dc[ev](e, tmp, inGroups);
                            dc.fireEvent(cev, { event: e, info: tmp, group: inGroups });
                        }
                    } else {
                        for (var b = 0, len = tmp.length; b < len; ++b) {
                            YAHOO.log(dc.id + ' ' + ev + ': ' + tmp[b].id, "info", "DragDropMgr");
                            if (dc.events[b4]) {
                                dc[b4](e, tmp[b].id, inGroups[0]);
                                b4Results[ev] = dc.fireEvent(b4 + 'Event', { event: e, info: tmp[b].id, group: inGroups[0] });
                            }
                            if (dc.events[check] && (b4Results[ev] !== false)) {
                                dc[ev](e, tmp[b].id, inGroups[0]);
                                dc.fireEvent(cev, { event: e, info: tmp[b].id, group: inGroups[0] });
                            }
                        }
                    }
                }
            }
        },

        /**
         * Helper function for getting the best match from the list of drag 
         * and drop objects returned by the drag and drop events when we are 
         * in INTERSECT mode.  It returns either the first object that the 
         * cursor is over, or the object that has the greatest overlap with 
         * the dragged element.
         * @method getBestMatch
         * @param  {DragDrop[]} dds The array of drag and drop objects 
         * targeted
         * @return {DragDrop}       The best single match
         * @static
         */
        getBestMatch: function(dds) {
            var winner = null;

            var len = dds.length;

            if (len == 1) {
                winner = dds[0];
            } else {
                // Loop through the targeted items
                for (var i=0; i<len; ++i) {
                    var dd = dds[i];
                    // If the cursor is over the object, it wins.  If the 
                    // cursor is over multiple matches, the first one we come
                    // to wins.
                    if (this.mode == this.INTERSECT && dd.cursorIsOver) {
                        winner = dd;
                        break;
                    // Otherwise the object with the most overlap wins
                    } else {
                        if (!winner || !winner.overlap || (dd.overlap &&
                            winner.overlap.getArea() < dd.overlap.getArea())) {
                            winner = dd;
                        }
                    }
                }
            }

            return winner;
        },

        /**
         * Refreshes the cache of the top-left and bottom-right points of the 
         * drag and drop objects in the specified group(s).  This is in the
         * format that is stored in the drag and drop instance, so typical 
         * usage is:
         * <code>
         * YAHOO.util.DragDropMgr.refreshCache(ddinstance.groups);
         * </code>
         * Alternatively:
         * <code>
         * YAHOO.util.DragDropMgr.refreshCache({group1:true, group2:true});
         * </code>
         * @TODO this really should be an indexed array.  Alternatively this
         * method could accept both.
         * @method refreshCache
         * @param {Object} groups an associative array of groups to refresh
         * @static
         */
        refreshCache: function(groups) {
            YAHOO.log("refreshing element location cache", "info", "DragDropMgr");

            // refresh everything if group array is not provided
            var g = groups || this.ids;

            for (var sGroup in g) {
                if ("string" != typeof sGroup) {
                    continue;
                }
                for (var i in this.ids[sGroup]) {
                    var oDD = this.ids[sGroup][i];

                    if (this.isTypeOfDD(oDD)) {
                        var loc = this.getLocation(oDD);
                        if (loc) {
                            this.locationCache[oDD.id] = loc;
                        } else {
                            delete this.locationCache[oDD.id];
YAHOO.log("Could not get the loc for " + oDD.id, "warn", "DragDropMgr");
                        }
                    }
                }
            }
        },

        /**
         * This checks to make sure an element exists and is in the DOM.  The
         * main purpose is to handle cases where innerHTML is used to remove
         * drag and drop objects from the DOM.  IE provides an 'unspecified
         * error' when trying to access the offsetParent of such an element
         * @method verifyEl
         * @param {HTMLElement} el the element to check
         * @return {boolean} true if the element looks usable
         * @static
         */
        verifyEl: function(el) {
            try {
                if (el) {
                    var parent = el.offsetParent;
                    if (parent) {
                        return true;
                    }
                }
            } catch(e) {
                YAHOO.log("detected problem with an element", "info", "DragDropMgr");
            }

            return false;
        },
        
        /**
         * Returns a Region object containing the drag and drop element's position
         * and size, including the padding configured for it
         * @method getLocation
         * @param {DragDrop} oDD the drag and drop object to get the 
         *                       location for
         * @return {YAHOO.util.Region} a Region object representing the total area
         *                             the element occupies, including any padding
         *                             the instance is configured for.
         * @static
         */
        getLocation: function(oDD) {
            if (! this.isTypeOfDD(oDD)) {
                YAHOO.log(oDD + " is not a DD obj", "info", "DragDropMgr");
                return null;
            }

            var el = oDD.getEl(), pos, x1, x2, y1, y2, t, r, b, l;

            try {
                pos= YAHOO.util.Dom.getXY(el);
            } catch (e) { }

            if (!pos) {
                YAHOO.log("getXY failed", "info", "DragDropMgr");
                return null;
            }

            x1 = pos[0];
            x2 = x1 + el.offsetWidth;
            y1 = pos[1];
            y2 = y1 + el.offsetHeight;

            t = y1 - oDD.padding[0];
            r = x2 + oDD.padding[1];
            b = y2 + oDD.padding[2];
            l = x1 - oDD.padding[3];

            return new YAHOO.util.Region( t, r, b, l );
        },

        /**
         * Checks the cursor location to see if it over the target
         * @method isOverTarget
         * @param {YAHOO.util.Point} pt The point to evaluate
         * @param {DragDrop} oTarget the DragDrop object we are inspecting
         * @param {boolean} intersect true if we are in intersect mode
         * @param {YAHOO.util.Region} pre-cached location of the dragged element
         * @return {boolean} true if the mouse is over the target
         * @private
         * @static
         */
        isOverTarget: function(pt, oTarget, intersect, curRegion) {
            // use cache if available
            var loc = this.locationCache[oTarget.id];
            if (!loc || !this.useCache) {
                YAHOO.log("cache not populated", "info", "DragDropMgr");
                loc = this.getLocation(oTarget);
                this.locationCache[oTarget.id] = loc;

                YAHOO.log("cache: " + loc, "info", "DragDropMgr");
            }

            if (!loc) {
                YAHOO.log("could not get the location of the element", "info", "DragDropMgr");
                return false;
            }

            //YAHOO.log("loc: " + loc + ", pt: " + pt);
            oTarget.cursorIsOver = loc.contains( pt );

            // DragDrop is using this as a sanity check for the initial mousedown
            // in this case we are done.  In POINT mode, if the drag obj has no
            // contraints, we are done. Otherwise we need to evaluate the 
            // region the target as occupies to determine if the dragged element
            // overlaps with it.
            
            var dc = this.dragCurrent;
            if (!dc || (!intersect && !dc.constrainX && !dc.constrainY)) {

                //if (oTarget.cursorIsOver) {
                    //YAHOO.log("over " + oTarget + ", " + loc + ", " + pt, "warn");
                //}
                return oTarget.cursorIsOver;
            }

            oTarget.overlap = null;


            // Get the current location of the drag element, this is the
            // location of the mouse event less the delta that represents
            // where the original mousedown happened on the element.  We
            // need to consider constraints and ticks as well.

            if (!curRegion) {
                var pos = dc.getTargetCoord(pt.x, pt.y);
                var el = dc.getDragEl();
                curRegion = new YAHOO.util.Region( pos.y, 
                                                   pos.x + el.offsetWidth,
                                                   pos.y + el.offsetHeight, 
                                                   pos.x );
            }

            var overlap = curRegion.intersect(loc);

            if (overlap) {
                oTarget.overlap = overlap;
                return (intersect) ? true : oTarget.cursorIsOver;
            } else {
                return false;
            }
        },

        /**
         * unload event handler
         * @method _onUnload
         * @private
         * @static
         */
        _onUnload: function(e, me) {
            this.unregAll();
        },

        /**
         * Cleans up the drag and drop events and objects.
         * @method unregAll
         * @private
         * @static
         */
        unregAll: function() {
            YAHOO.log("unregister all", "info", "DragDropMgr");

            if (this.dragCurrent) {
                this.stopDrag();
                this.dragCurrent = null;
            }

            this._execOnAll("unreg", []);

            //for (var i in this.elementCache) {
                //delete this.elementCache[i];
            //}
            //this.elementCache = {};

            this.ids = {};
        },

        /**
         * A cache of DOM elements
         * @property elementCache
         * @private
         * @static
         * @deprecated elements are not cached now
         */
        elementCache: {},
        
        /**
         * Get the wrapper for the DOM element specified
         * @method getElWrapper
         * @param {String} id the id of the element to get
         * @return {YAHOO.util.DDM.ElementWrapper} the wrapped element
         * @private
         * @deprecated This wrapper isn't that useful
         * @static
         */
        getElWrapper: function(id) {
            var oWrapper = this.elementCache[id];
            if (!oWrapper || !oWrapper.el) {
                oWrapper = this.elementCache[id] = 
                    new this.ElementWrapper(YAHOO.util.Dom.get(id));
            }
            return oWrapper;
        },

        /**
         * Returns the actual DOM element
         * @method getElement
         * @param {String} id the id of the elment to get
         * @return {Object} The element
         * @deprecated use YAHOO.util.Dom.get instead
         * @static
         */
        getElement: function(id) {
            return YAHOO.util.Dom.get(id);
        },
        
        /**
         * Returns the style property for the DOM element (i.e., 
         * document.getElById(id).style)
         * @method getCss
         * @param {String} id the id of the elment to get
         * @return {Object} The style property of the element
         * @deprecated use YAHOO.util.Dom instead
         * @static
         */
        getCss: function(id) {
            var el = YAHOO.util.Dom.get(id);
            return (el) ? el.style : null;
        },

        /**
         * Inner class for cached elements
         * @class DragDropMgr.ElementWrapper
         * @for DragDropMgr
         * @private
         * @deprecated
         */
        ElementWrapper: function(el) {
                /**
                 * The element
                 * @property el
                 */
                this.el = el || null;
                /**
                 * The element id
                 * @property id
                 */
                this.id = this.el && el.id;
                /**
                 * A reference to the style property
                 * @property css
                 */
                this.css = this.el && el.style;
            },

        /**
         * Returns the X position of an html element
         * @method getPosX
         * @param el the element for which to get the position
         * @return {int} the X coordinate
         * @for DragDropMgr
         * @deprecated use YAHOO.util.Dom.getX instead
         * @static
         */
        getPosX: function(el) {
            return YAHOO.util.Dom.getX(el);
        },

        /**
         * Returns the Y position of an html element
         * @method getPosY
         * @param el the element for which to get the position
         * @return {int} the Y coordinate
         * @deprecated use YAHOO.util.Dom.getY instead
         * @static
         */
        getPosY: function(el) {
            return YAHOO.util.Dom.getY(el); 
        },

        /**
         * Swap two nodes.  In IE, we use the native method, for others we 
         * emulate the IE behavior
         * @method swapNode
         * @param n1 the first node to swap
         * @param n2 the other node to swap
         * @static
         */
        swapNode: function(n1, n2) {
            if (n1.swapNode) {
                n1.swapNode(n2);
            } else {
                var p = n2.parentNode;
                var s = n2.nextSibling;

                if (s == n1) {
                    p.insertBefore(n1, n2);
                } else if (n2 == n1.nextSibling) {
                    p.insertBefore(n2, n1);
                } else {
                    n1.parentNode.replaceChild(n2, n1);
                    p.insertBefore(n1, s);
                }
            }
        },

        /**
         * Returns the current scroll position
         * @method getScroll
         * @private
         * @static
         */
        getScroll: function () {
            var t, l, dde=document.documentElement, db=document.body;
            if (dde && (dde.scrollTop || dde.scrollLeft)) {
                t = dde.scrollTop;
                l = dde.scrollLeft;
            } else if (db) {
                t = db.scrollTop;
                l = db.scrollLeft;
            } else {
                YAHOO.log("could not get scroll property", "info", "DragDropMgr");
            }
            return { top: t, left: l };
        },

        /**
         * Returns the specified element style property
         * @method getStyle
         * @param {HTMLElement} el          the element
         * @param {string}      styleProp   the style property
         * @return {string} The value of the style property
         * @deprecated use YAHOO.util.Dom.getStyle
         * @static
         */
        getStyle: function(el, styleProp) {
            return YAHOO.util.Dom.getStyle(el, styleProp);
        },

        /**
         * Gets the scrollTop
         * @method getScrollTop
         * @return {int} the document's scrollTop
         * @static
         */
        getScrollTop: function () { return this.getScroll().top; },

        /**
         * Gets the scrollLeft
         * @method getScrollLeft
         * @return {int} the document's scrollTop
         * @static
         */
        getScrollLeft: function () { return this.getScroll().left; },

        /**
         * Sets the x/y position of an element to the location of the
         * target element.
         * @method moveToEl
         * @param {HTMLElement} moveEl      The element to move
         * @param {HTMLElement} targetEl    The position reference element
         * @static
         */
        moveToEl: function (moveEl, targetEl) {
            var aCoord = YAHOO.util.Dom.getXY(targetEl);
            YAHOO.log("moveToEl: " + aCoord, "info", "DragDropMgr");
            YAHOO.util.Dom.setXY(moveEl, aCoord);
        },

        /**
         * Gets the client height
         * @method getClientHeight
         * @return {int} client height in px
         * @deprecated use YAHOO.util.Dom.getViewportHeight instead
         * @static
         */
        getClientHeight: function() {
            return YAHOO.util.Dom.getViewportHeight();
        },

        /**
         * Gets the client width
         * @method getClientWidth
         * @return {int} client width in px
         * @deprecated use YAHOO.util.Dom.getViewportWidth instead
         * @static
         */
        getClientWidth: function() {
            return YAHOO.util.Dom.getViewportWidth();
        },

        /**
         * Numeric array sort function
         * @method numericSort
         * @static
         */
        numericSort: function(a, b) { return (a - b); },

        /**
         * Internal counter
         * @property _timeoutCount
         * @private
         * @static
         */
        _timeoutCount: 0,

        /**
         * Trying to make the load order less important.  Without this we get
         * an error if this file is loaded before the Event Utility.
         * @method _addListeners
         * @private
         * @static
         */
        _addListeners: function() {
            var DDM = YAHOO.util.DDM;
            if ( YAHOO.util.Event && document ) {
                DDM._onLoad();
            } else {
                if (DDM._timeoutCount > 2000) {
                    YAHOO.log("DragDrop requires the Event Utility", "error", "DragDropMgr");
                } else {
                    setTimeout(DDM._addListeners, 10);
                    if (document && document.body) {
                        DDM._timeoutCount += 1;
                    }
                }
            }
        },

        /**
         * Recursively searches the immediate parent and all child nodes for 
         * the handle element in order to determine wheter or not it was 
         * clicked.
         * @method handleWasClicked
         * @param node the html element to inspect
         * @static
         */
        handleWasClicked: function(node, id) {
            if (this.isHandle(id, node.id)) {
                YAHOO.log("clicked node is a handle", "info", "DragDropMgr");
                return true;
            } else {
                // check to see if this is a text node child of the one we want
                var p = node.parentNode;
                // YAHOO.log("p: " + p);

                while (p) {
                    if (this.isHandle(id, p.id)) {
                        return true;
                    } else {
                        YAHOO.log(p.id + " is not a handle", "info", "DragDropMgr");
                        p = p.parentNode;
                    }
                }
            }

            return false;
        }

    };

}();

// shorter alias, save a few bytes
YAHOO.util.DDM = YAHOO.util.DragDropMgr;
YAHOO.util.DDM._addListeners();

}

