/*
 * CometChat
 * Copyright (c) 2014 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/

function getTimeDisplay(ts) {
	var ap = "";
	var hour = ts.getHours();
	var minute = ts.getMinutes();
	var todaysDate = new Date();
	var todays12am = todaysDate.getTime() - (todaysDate.getTime()%(24*60*60*1000));
	var date = ts.getDate();
	var month = ts.getMonth();
	var armyTime = <?php echo $armyTime; ?>;

	if(!armyTime){
		if (hour > 11) { ap = "pm"; } else { ap = "am"; }
		if (hour > 12) { hour = hour - 12; }
		if (hour == 0) { hour = 12; }
	}
	if (minute < 10) { minute = "0" + minute; }

	var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

	var type = 'th';
	if (date == 1 || date == 21 || date == 31) { type = 'st'; }
	else if (date == 2 || date == 22) { type = 'nd'; }
	else if (date == 3 || date == 23) { type = 'rd'; }

	if (ts < todays12am) {
		return hour+":"+minute+ap+' '+date+type+' '+months[month];
	} else {
		return hour+":"+minute+ap;
	}
}

$(function() {
	if (jQuery().slimScroll) {
		$('.announcements').slimScroll({height: '310px',allowPageScroll: false});
		$(".announcements").css("height","310px");
	}
	$('.chattime').each(function(key,value){
		var ts = new Date($(this).attr('timestamp') * 1000);
		var timest = getTimeDisplay(ts);
		$(this).html(timest);
	});
});

