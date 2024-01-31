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

		let fromNow = moment(time).format('LL');

		return moment(time).calendar(null, {
			sameDay: '[' + il.Language.txt('today') + ']',
			lastDay: '[' + il.Language.txt('yesterday') + ']',
			lastWeek: function (now) {
				return "[" + fromNow + "]";
			},
			sameElse: function (now) {
				return "[" + fromNow + "]";
			}
		});
	};
})(jQuery, moment, window.il);