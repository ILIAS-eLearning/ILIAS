function addInternalLink(link,title,input_id) 
{	
	var part = link.substr(5);
	part = part.split('=');
	var type = part[0];
	var id = part[1].substr(1, part[1].length-3);
	
	console.log(input_id);
	
	$("input[name="+input_id+"_ajax_type]").val(type);
	$("input[name="+input_id+"_ajax_id]").val(id);
}