if (document.images)
{
	var path = "./templates/default/images/navbar/";
	
	imag = new Array();
	imag[0]  = path + "desk.gif";
	imag[1]  = path + "course.gif";
	imag[2]  = path + "bookma.gif";
	imag[3]  = path + "search.gif";
	imag[4]  = path + "literat.gif";
	imag[5]  = path + "mail.gif";
	imag[6]  = path + "newsgr.gif";
	imag[7]  = path + "groups.gif";
	imag[8]  = path + "help.gif";
	imag[9]  = path + "feedb.gif";
	imag[10] = path + "logout.gif";
	imag[11] = path + "desk_o.gif";
	imag[12] = path + "course_o.gif";
	imag[13] = path + "bookma_o.gif";
	imag[14] = path + "search_o.gif";
	imag[15] = path + "literat_o.gif";
	imag[16] = path + "mail_o.gif";
	imag[17] = path + "newsgr_o.gif";
	imag[18] = path + "groups_o.gif";
	imag[19] = path + "help_o.gif";
	imag[20] = path + "feedb_o.gif";
	imag[21] = path + "logout_o.gif";
	imag[22] = path + "login.gif";
	imag[23] = path + "login_o.gif";
	imag[24] = path + "editor.gif";
	imag[25] = path + "editor_o.gif";
	imag[26] = path + "admin.gif";
	imag[27] = path + "admin_o.gif";
	imag[28] = path + "termin.gif";
	imag[29] = path + "termin_o.gif";

	im = new Array();
	
	for (var i = 0; i < imag.length; i++)
	{
		im[i] = new Image();
		im[i].src = imag[i];
	}

	function swtch(num,imgname)
	{
		imgname.src = im[num].src;
		window.status = '';
	}

	function swtchon(num,imgname,text)
	{
		imgname.src = im[num].src;
		window.status = text;
	}
}