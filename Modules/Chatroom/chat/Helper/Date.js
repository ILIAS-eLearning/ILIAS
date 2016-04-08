/**
 * A helper class to generate a timestamp
 *
 * @constructor
 */
var DateHelper = function DateHelper(){};

/**
 * Creates a timestamp
 *
 * @returns {number}
 */
DateHelper.prototype.getTimestamp = function() {
	var date = new Date();

	return date.getTime();
};

/**
 * @type {DateHelper}
 */
module.exports = new DateHelper();