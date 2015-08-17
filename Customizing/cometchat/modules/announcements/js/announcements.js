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
            ap = hour>11 ? "PM" : "AM";
            hour = hour==0 ? 12 : hour>12 ? hour-12 : hour;
	}else{
            hour = hour<10 ? "0"+hour : hour;
        }
	minute = minute<10 ? "0"+minute : minute;
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
	jqcc('.chattime').each(function(key,value){
		var ts = new Date(jqcc(this).attr('timestamp') * 1000);
		var timest = getTimeDisplay(ts);
		jqcc(this).html(timest);
	});
});

