/**
 * Provides an container for il.UI.Uploader instances.
 * Instances can be added and got from this container.
 *
 * @author nmaerchy <nm@studer-raimann.ch>
 * @version 0.0.1
 */


var il = il || {};
il.UI = il.UI || {};
(function($, UI) {

	UI.UploaderContainer = function ($) {

		var _instances = {};

		/**
		 * Adds the passed in instance to this container.
		 *
		 * @param {il.UI.Uploader} instance the instance to add
		 */
		var addInstance = function (instance) {
			_instances[instance.getInstanceId()] = instance;
		};

		/**
		 * Gets the instance by the passed in id.
		 *
		 * @param {string} id the id of the wanted instance
		 * @returns {il.UI.Uploader} The instance found in this container
		 * @throws Error If no instance with the passed in id exists.
		 */
		var getInstanceById = function (id) {
			if (_instances[id] === undefined) {
				throw new Error("No instance found with id: " + id);
			}
			return _instances[id];
		};

		return {
			addInstance: addInstance,
			getInstanceById: getInstanceById
		}

	}($);

})($, il.UI);
