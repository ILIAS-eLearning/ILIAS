function momentFromNowToTime(time) {
	var currentTime = new Date().getTime();

	if (isNaN(time) || time > currentTime) {
		time = currentTime;
	}

	return moment(time).fromNow();
}