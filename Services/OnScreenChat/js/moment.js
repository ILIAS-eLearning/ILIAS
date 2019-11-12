(function ($, moment, scope) {
	scope = scope || {};

	scope.ChatDateTimeFormatter = {};

	scope.ChatDateTimeFormatter.setLocale = function (locale) {
		moment.locale(locale);
	};

	scope.ChatDateTimeFormatter.fromNowToTime = function (time) {
		let currentTime = new Date().getTime();

		if (isNaN(time) || time > currentTime) {
			time = currentTime;
		}

		return moment(time).fromNow();
	};

	scope.ChatDateTimeFormatter.format = function (time, format) {
		let currentTime = new Date().getTime();

		if (isNaN(time) || time > currentTime) {
			time = currentTime;
		}

		return moment(time).format(format);
	};

	scope.ChatDateTimeFormatter.formatDate = function (time) {
		let currentTime = new Date().getTime();

		if (isNaN(time) || time > currentTime) {
			time = currentTime;
		}

		let fromNow = moment(time).fromNow();

		return moment(time).calendar(null, {
			lastDay: '[' + il.Language.txt('yesterday') + ']',
			sameDay: '[' + il.Language.txt('today') + ']',
			sameElse: function () {
				return "[" + fromNow + "]";
			}
		});
	};
})(jQuery, moment, window.il);