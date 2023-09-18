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

DateHelper.prototype.iso8601TimezoneFormat = function(date) {
	let timezone_offset_i = date.getTimezoneOffset(),
		offset_H = parseInt(Math.abs(timezone_offset_i / 60)),
		offset_i = Math.abs(timezone_offset_i % 60),
		timezone_standard;

	if (offset_H < 10) {
		offset_H = '0' + offset_H;
	}

	if (offset_i < 10) {
		offset_i = '0' + offset_i;
	}

	if (timezone_offset_i < 0) {
		timezone_standard = '+' + offset_H + ':' + offset_i;
	} else if (timezone_offset_i > 0) {
		timezone_standard = '-' + offset_H + ':' + offset_i;
	} else if (timezone_offset_i === 0) {
		timezone_standard = 'Z';
	}

	return timezone_standard
};

DateHelper.prototype.iso8601DatetimeFormat = function(date) {
	let Y = date.getFullYear(),
		m = date.getMonth() + 1,
		d = date.getDate(),
		H = date.getHours(),
		i = date.getMinutes(),
		s = date.getSeconds();

	d = d < 10 ? '0' + d : d;
	m = m < 10 ? '0' + m : m;
	H = H < 10 ? '0' + H : H;
	i = i < 10 ? '0' + i : i;
	s = s < 10 ? '0' + s : s;

	return [
		Y + '-' + m + '-' + d +
		'T' + H + ':' + i + ':' + s
	].join("");
};

/**
 * @type {DateHelper}
 */
module.exports = new DateHelper();