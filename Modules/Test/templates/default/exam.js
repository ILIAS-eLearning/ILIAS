function seb_init() {
	if ($("body.kiosk").length) {
		appendUserData();		
	}
	else {
		addUser();		
	}
}

function addUser() {
	logout = "<a href='./logout.php'>logout</a>";
	$("header div.row").append("<div class=\"sebObject\"><span class=\"sebFullname\">"+seb_object.user.firstname + " " + seb_object.user.lastname + "</span><span class=\"sebLogin\"> " + getExtraData() + "</span>  >>  " + logout + "</div>");
}

function appendUserData() {
	$("#kioskParticipant").append(getExtraData());
}

function getExtraData() {
	return (seb_object.user.matriculation != "") ? " (" + seb_object.user.login + ", " + seb_object.user.matriculation + ")" : " (" + seb_object.user.login + ")";
}

window.addEventListener("load", seb_init, false);
