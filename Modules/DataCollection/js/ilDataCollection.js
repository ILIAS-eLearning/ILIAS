/**
 * DataCollection JS object
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */

var ilDataCollection = (function() {

    // URLs are initialized by controller
    var edit_url = '';
    var create_url = '';
    var save_url = '';
    var data_url = '';

    var after_save = function(o) {};

    var strings = {};

    /**
     * Show a edit form af any record in the right ILIAS overlay
     *
     * @param record_id
     * @param after_save callback function executed after saving
     */
    var showEditRecordOverlay = function(record_id, after_save) {
        var callback = {
            success : function(o) {
                showRightPanel(o.responseText);
            },
            failure : handleAjaxFailure
        };
        il.Overlay.hideAllOverlays(window.event, true);
        YAHOO.util.Connect.asyncRequest('GET', this.edit_url + '&record_id=' + record_id, callback);
        if (typeof after_save != 'undefined') this.after_save = after_save;
    };

    /**
     * Show creation form for a new record in the right ILIAS overlay
     *
     * @param table_id
     * @param after_save callback function executed after saving
     */
    var showCreateRecordOverlay = function(table_id, after_save) {
        var callback = {
            success : function(o) {
                showRightPanel(o.responseText);
            },
            failure : handleAjaxFailure
        };
        il.Overlay.hideAllOverlays(window.event, true);
        YAHOO.util.Connect.asyncRequest('GET', this.create_url + '&table_id=' + table_id, callback);
        if (typeof after_save != 'undefined') this.after_save = after_save;
    };

    /**
     * Save record serialized record data (create or update)
     *
     * @param data Serialized record data
     * @param callback_function Callback function executed on success
     */
    var saveRecordData = function(data, callback_function) {
        var callback = {
            success : function(o) {
                showRightPanel(o.responseText);
                if (typeof callback_function != 'undefined') callback_function(o);
                ilDataCollection.after_save(o);
                ilDataCollection.after_save = function(o) {};
            },
            failure : handleAjaxFailure
        };
        YAHOO.util.Connect.asyncRequest('POST', this.save_url, callback, data);
    };

    /**
     * Get JSON data from a given record
     *
     * @param record_id
     * @param callback_function Callback function executed on success
     */
    var getRecordData = function(record_id, callback_function) {
        var callback = {
            success : function(o) {
                if (typeof callback_function != 'undefined') callback_function(o);
            },
            failure : handleAjaxFailureJSON
        };
        YAHOO.util.Connect.asyncRequest('GET', this.data_url + '&record_id=' + record_id, callback);
    }

    var showRightPanel = function(html) {
        il.UICore.showRightPanel();
        il.UICore.setRightPanelContent(html);
    }

    var handleAjaxFailure = function(o) {
        il.UICore.showRightPanel();
        il.UICore.setRightPanelContent('Ajax failure');
        console.log(o);
    };

    var handleAjaxFailureJSON = function(o) {
        il.UICore.showRightPanel();
        il.UICore.setRightPanelContent('Ajax failure');
        console.log(o);
    };

    /**
     * Public properties/functions
     */
    return {
        setEditUrl : function(url) { this.edit_url = url; },
        setCreateUrl : function(url) { this.create_url = url; },
        setSaveUrl : function(url) { this.save_url = url; },
        setDataUrl : function(url) { this.data_url = url; },
        getRecordData : getRecordData,
        showCreateRecordOverlay : showCreateRecordOverlay,
        showEditRecordOverlay : showEditRecordOverlay,
        saveRecordData : saveRecordData,
        strings : strings
    };

}());