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

	return moment(time).format(format);
}