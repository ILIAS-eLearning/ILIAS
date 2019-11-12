function momentFromNowToTime(time) {
	let currentTime = new Date().getTime();

	if (isNaN(time) || time > currentTime) {
		time = currentTime;
	}

	return moment(time).fromNow();
}

function momentFormatDate(time, format) {
	let currentTime = new Date().getTime();

	if (isNaN(time) || time > currentTime) {
		time = currentTime;
	}

	let fromNow = moment(time).fromNow();

	return moment(time).calendar(null, {
		// when the date is closer, specify custom values
		lastDay:  '[' + il.Language.txt('yesterday') + ']',
		sameDay:  '[' + il.Language.txt('today') + ']',
		sameElse: function () {
			return "[" + fromNow + "]";
		}
	})
}