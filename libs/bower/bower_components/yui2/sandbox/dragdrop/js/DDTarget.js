/**
 * A DragDrop implementation that does not move, but can be a drop 
 * target.  You would get the same result by simply omitting implementation 
 * for the event callbacks, but this way we reduce the processing cost of the 
 * event listener and the callbacks.
 * @class DDTarget
 * @extends YAHOO.util.DragDrop 
 * @constructor
 * @param {String} id the id of the element that is a drop target
 * @param {String} sGroup the group of related DragDrop objects
 * @param {object} config an object containing configurable attributes
 *                 Valid properties for DDTarget in addition to those in 
 *                 DragDrop: 
 *                    none
 */
YAHOO.util.DDTarget = function(id, sGroup, config) {
    if (id) {
        this.initTarget(id, sGroup, config);
    }
};

// YAHOO.util.DDTarget.prototype = new YAHOO.util.DragDrop();
YAHOO.extend(YAHOO.util.DDTarget, YAHOO.util.DragDrop, {
    toString: function() {
        return ("DDTarget " + this.id);
    }
});
