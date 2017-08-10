/**
 * Creates a store, which can be used to set and get information on a
 * user's local machine. This is similar to a browser cookie, except the 
 * allowed store is larger and can be shared across browsers.
 *
 * @module datastore
 * @requires yahoo, dom, event, element
 * @title DataStore Util
 * @beta
 */

/**
 * Class for the YUI DataStore util.
 *
 * @namespace YAHOO.util
 * @class DataStore
 * @uses YAHOO.widget.FlashAdapter
 * @constructor
 * @param containerId {HTMLElement} Container element for the Flash Player instance.
 * @param attributes {Object} Properties for embedding the SWF.
 */
YAHOO.util.DataStore = function(containerID, attributes, swfURL)
{
	YAHOO.util.DataStore.superclass.constructor.call(this, YAHOO.util.DataStore.SWFURL, containerID, attributes);
	
	/**
	 * Fires when an error occurs
	 *
	 * @event error
	 * @param event.type {String} The event type
	 * @param event.message {String} The data 
	 * 
	 */
	this.createEvent("error");
	
	/**
	 * Fires when a store is saved successfully
	 *
	 * @event success
	 * @param event.type {String} The event type
	 * 
	 */
	this.createEvent("success");
	
	
	/**
	 * Fires when the save is pending, due to a request for additional storage
	 *
	 * @event error
	 * @param event.type {String} The event type
	 * 
	 */
	this.createEvent("pending");
	
	
	/**
	 * Fires as the settings dialog displays
	 *
	 * @event error
	 * @param event.type {String} The event type
	 * 
	 */
	this.createEvent("openDialog");
	
	/**
	 * Fires when a settings dialog is not able to be displayed due to 
	 * the SWF not being large enough to show it. In this case, the developer
	 * needs to resize the SWF to width of 215px and height of 138px or above, 
	 * or display an external settings page.
	 *
	 * @event openExternalDialog
	 * @param event.type {String} The event type
	 * 
	 */
	this.createEvent("openExternalDialog");
};

YAHOO.extend(YAHOO.util.DataStore, YAHOO.widget.FlashAdapter,
{


	/**
	 * Public accessor to the unique name of the DataStore instance.
	 *
	 * @method toString
	 * @return {String} Unique name of the DataStore instance.
	 */
	toString: function()
	{
		return "DataStore " + this._id;
	},
	
	
	   /**
	    * Saves data to local storage. It returns a String that can
		* be one of three values: "true" if the storage succeeded; "false" if the user
		* has denied storage on their machine, and "pending" if storage is permitted,
		* but the storage space allotted is not sufficient.
		* <p>The size limit for the passed parameters is ~40Kb.</p>
		* @method setItem
	    * @param item {Object} The data to store
	    * @param location {String} The name of the "cookie" or store 
		* @return {Boolean} Whether or not the save was successful
	    * 
	    */
		setItem: function(data, location) 
		{
			YAHOO.log("SETTING " +data +" to "+ location);
			return this._swf.setItem(data, location);
		} ,
	    
	    /**
	    * Returns the item in storage, if any.
	    * @method getItem
	    * @param location {String} The name of the "cookie" or store
		* @return {Object} The data
	    * 
	    */
		getItem: function(location) 
		{
			return this._swf.getItem(location);
		} ,

	    /**
	    * Removes the item in storage, if any.
	    * @method removeItem
	    * @param location {String} The name of the "cookie" or store
	    * 
	    */
		removeItem: function(location) 
		{
			YAHOO.log("removing " + location);
			return this._swf.removeItem(location);
		} ,
		
	   /**
	    * Removes all data in local storage for this domain.
	    * <p>Be careful when using this method, as it may 
	    * remove stored information that is used by other applications
	    * in this domain </p>
	    * @method clear
	    */		
		clear: function() 
		{
			return this._swf.clear();
		} ,
		
	    /**
	     *  Gets the current size, in KB, of the amount of space taken by the current store.
	     * @method calculateSize
	     */		
		calculateSize: function() 
		{
			return this._swf.calculateSize();
		} ,
		
	    /**
	     * Gets the timestamp of the last store. This value is automatically set when 
	     * data is stored.
	     * @method getLastModified
	     * @return A Date object
	     */
		getLastModified: function() 
		{
			return this._swf.getLastModified();
		} ,
		
		/**
		* This method requests more storage if the amount is above 100KB. (e.g.,
		* if the <code>store()</code> method returns "pending".
		* The request dialog has to be displayed within the Flash player itself
		* so the SWF it is called from must be visible and at least 215px x 138px in size.
		* 
		* @method setSize
		* @param value The size, in KB
		*/		
		setSize: function(value) 
		{
			return this._swf.setSize(value);
		} ,
		
		/**
		 * Displays the settings dialog to allow the user to configure
		 * storage settings manually. If the SWF height and width are smaller than
		 * what is allowable to display the local settings panel,
		 * an openExternalDialog message will be sent to JavaScript.
		 * @method displaySettings
		 */		
		displaySettings: function() 
		{
			return this._swf.displaySettings();
		} 

});


YAHOO.util.DataStore.SWFURL = "datastore.swf";