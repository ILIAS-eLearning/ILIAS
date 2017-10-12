/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

il.ExcIDl = {
	ajax_url: '',
	
	init: function (url) {
		console.log("init url:" + url);
		this.ajax_url = url;
		il.ExcIDl.initModal();
	},
	
	trigger: function(user_id, ass_id) {
		console.log("trigger");
		il.Util.sendAjaxGetRequestToUrl(
			il.ExcIDl.ajax_url,
			{idlid: ass_id+"_"+user_id},
			{},
			il.ExcIDl.showModal
		);
		return false;
	},
	
	initModal: function() {
		console.log("init modal");
		// add form action
		$('form[name="ilExcIDlForm"]').submit(function() {			
			var submit_btn = $(document.activeElement).attr("name");
			if(submit_btn)
			{
				var values = {};
				var cmd = null;
				var sel = [];
				var ids = [];
				$.each($(this).serializeArray(), function(i, field) {
					if(submit_btn == "select_cmd2" && field.name == "selected_cmd2")
					{
						cmd = field.value;
					}
					else if(submit_btn == "select_cmd" && field.name == "selected_cmd")
					{
						cmd = field.value;
					}					
					// extract user/team ids
					if(field.name.substr(0, 6) == "member")
					{
						sel.push(field.name.substr(7, field.name.length-8));
					}
					else if(field.name.substr(0, 3) == "ass")
					{
						sel.push(field.name.substr(4, field.name.length-5));
					}
					else if(field.name.substr(0, 5) == "idlid" && field.value != "")
					{						
						var sel_value = field.name.substr(6, field.name.length-7);
						if(sel.indexOf(sel_value) > -1)
						{
							ids.push(field.value);
						}
					}
				});	
				if(cmd == "setIndividualDeadline" && ids.length)
				{
					// :TODO: handle preventDoubleSubmission?
					
					il.Util.sendAjaxGetRequestToUrl(
						il.ExcIDl.ajax_url,
						{idlid: ids.join()},
						{},
						il.ExcIDl.showModal
					);
					return false;
				}
			}
		});		
		// modal clean-up on close
		$('#ilExcIDl').on('hidden.bs.modal', function(e) {
			$("#ilExcIDlBody").html("");			
		});				
	},		
	
	showModal: function(o) {
		console.log("show modal");
		if(o.responseText !== undefined)
		{			
			$("#ilExcIDlBody").html(o.responseText);
			
			il.ExcIDl.parseForm();
			
			$("#ilExcIDl").modal('show');			
		}
	},
	
	parseForm: function() {			
		$('form[name="ilExcIDlForm"]').submit(function() {		
			$.ajax({
				type: "POST",
				url: il.ExcIDl.ajax_url,
				data: $(this).serializeArray(),
				success: il.ExcIDl.handleForm
			  });
			return false;
		});		
	},
	
	handleForm: function(responseText) {		
		if(responseText !== undefined)
		{
			if(responseText != "ok")
			{
				$("#ilExcIDlBody").html(responseText);				
				il.ExcIDl.parseForm();
			}
			else
			{
				window.location.replace(il.ExcIDl.ajax_url + "&dn=1");
			}
		}
	}	
};