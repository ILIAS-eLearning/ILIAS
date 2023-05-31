/*global il:false, jQuery:false*/
(function(w, $) {

	var test_end = -1,
		local_timer_start = -1,
		unsaved = true,
		interval = 0;

	if (w.performance) {
	  local_timer_start = performance.now();
	}

	var server_date = (new Date({YEAR}, {MONTHNOW}, {DAYNOW}, {HOURNOW}, {MINUTENOW}, {SECONDNOW})).getTime() / 1000,
		// first tick happens immediately, and older browser use tick-based
		// counter which increments as soon as jQuery fires $(document).ready
		now = server_date - 1,
		test_start = (new Date({YEAR}, {MONTH}, {DAY}, {HOUR}, {MINUTE}, {SECOND})).getTime() / 1000,
		test_time_min = {PTIME_M},
		test_time_sec = {PTIME_S},
		minute = "{STRING_MINUTE}",
		minutes = "{STRING_MINUTES}",
		second = "{STRING_SECOND}",
		seconds = "{STRING_SECONDS}",
		timeleft = "{STRING_TIMELEFT}",
		redirectUrl = "{REDIRECT_URL}",
		and = "{AND}",
		time_left_span,
		check_url = "{CHECK_URL}",
		check_interval = 60000;

		<!-- BEGIN enddate -->
			test_end = (new Date({ENDYEAR}, {ENDMONTH}, {ENDDAY}, {ENDHOUR}, {ENDMINUTE}, {ENDSECOND})).getTime() / 1000;
		<!-- END enddate -->

	/**
	 * invoke test player's auto-save if available
	 */
	function autoSave() {
		unsaved = false;
		if (typeof il.TestPlayerQuestionEditControl !== 'undefined') {
			il.TestPlayerQuestionEditControl.saveOnTimeReached();
		}
	}

	/**
	 * submit form to redirectUrl
	 */
	function redirect() {
		/**
		 * Check again for added time before finally
		 * submitting the test results.
		 */
		$.ajax({
			type: 'GET',
			url: check_url,
			dataType: 'text',
			timeout: 1000
		})
		.done((response) => {
			if (response > (test_time_min * 60 + test_time_sec)) {
				test_time_sec = response % 60;
				test_time_min = (response - test_time_sec) / 60;
				setWorkingTime();
			} else {
				$("#listofquestions").attr('action', redirectUrl).submit();
			}
		})
		.fail(
			$("#listofquestions").attr('action', redirectUrl).submit()
		);
	}

	/**
	 * Format a "time left" string from parameters provided.
	 * @param {Number}  avail Time available in full seconds.
	 * @param {Number}  avail_m Full minutes available.
	 * @param {Number}  avail_s Seconds available subtracted by full minutes (i.e. avail mod 60).
	 * @return {String} Text telling how much time is left to finish the test in user's language
	 */
	function formatString(avail, avail_m, avail_s) {
		var output = avail_m + " ";
		if (avail_m === 1) {
			output += minute;
		} else {
			output += minutes;
		}
		// show seconds if less than 5min (300s) left
		if (avail < 300) {
			if (avail_s < 10) {
				output += " " + and + " 0" + avail_s + " ";
			} else {
				output += " " + and + " " + avail_s + " ";
			}
			if (avail_m == 0) {
				if (avail_s < 10) {
					output = "0" + avail_s + " ";
				} else {
					output = avail_s + " ";
				}
			}
			if (avail_s == 1) {
				output += second;
			} else {
				output += seconds;
			}
		}
		return output;
	}

	/**
	 * Calculate remaining working time and dispatch actions based on that
	 */
	function setWorkingTime() {
		// time since start in seconds
		var diff = Math.floor(now - test_start),
			// available time
			avail = test_time_min * 60 + test_time_sec - diff,
			avail_m, avail_s, output;

		if (avail < 0) {
			avail = 0;
		}
		if (test_end > -1) {
			var diffToEnd = Math.floor(test_end - now);
			if ((diffToEnd > 0) && (diffToEnd < avail))
			{
				avail = diffToEnd;
			}
			else if (diffToEnd < 0)
			{
				avail = 0;
			}
		}
		if ((avail <= 0) && unsaved) {
			autoSave();
		}
		if((avail <= 0) && redirectUrl !== "") {
			redirect();
		}

		avail_m = Math.floor(avail / 60);
		avail_s = avail - (avail_m * 60);
		output = formatString(avail, avail_m, avail_s);

		time_left_span.html( timeleft.replace(/%s/, output) );
	}

	/**
	 * MUST be invoked every 1000ms in older browsers
	 * (SHOULD for those that support window.performance)
	 */
	function tick() {
		if (local_timer_start >= 0) {
			// use performance API
			var local_timer_now = performance.now();
			if (local_timer_now >= local_timer_start) {
				// floor in order to ensure the test is not submitted before end
				// in which case ILIAS would display "autosave [failed|succeeded]"
				// and not "the test ended ..."
				now = Math.floor(server_date + (local_timer_now - local_timer_start) / 1000);
			} else {
				// result by performance API does not make sense, maybe it's broken
				// in this browser/version or blocked by a privacy plugin
				now++;
			}
		} else {
			// performance API unsupported by client
			now++;
		}
		setWorkingTime();
	}

	/**
	 * This is invoked regularly (see 'check_interval') to check if the
	 * allocated time for time-restricted tests has been changed during
	 * the test and update the timer accordingly.
	 */
	function checkWorkingTime() {
		$.ajax({
		    type: 'GET',
		    url: check_url,
		    dataType: 'text',
		    timeout: 1000
		})
		.done((response) => {
			if (response > 0) {
				test_time_sec = response % 60;
				test_time_min = (response - test_time_sec) / 60;
				setWorkingTime();
			};
		})
		.fail();
	}

	$(function() {
		time_left_span = $('#timeleft');
		tick();
		interval = w.setInterval(tick, 1000);
		w.setInterval(checkWorkingTime, check_interval);
	});

}(window, jQuery));
