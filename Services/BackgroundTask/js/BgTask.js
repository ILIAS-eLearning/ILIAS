il.BgTask = {

	ajax: "",

	setAjax: function (url) {
		this.ajax = url;
	},
	
	init: function (handler_id, params) {		
		this.doAjax({hid: handler_id, par: params});
	},
	
	cancel: function (task_id) {						
		il.BgTask.doAjax({"tid": task_id, "cmd":"cancel"});
	},
	
	progress: function (task_id, modal, steps, current) {
					
		// :TODO: layout
		modal.modal.find(".modal-body").html(current+"/"+steps);
					
		setTimeout(function() {			
			il.BgTask.doAjax({"tid": task_id, "cmd":"progress"}, {task_id: task_id, modal: modal});
		}, 500);

	},
	
	doAjax: function (par, args) {
		il.Util.sendAjaxGetRequestToUrl(this.ajax, par, args, this.processAjax); 		
	},	
	
	processAjax: function (o) {		
		// console.log(o.responseText);
		// console.log(o);
		if(o.responseText !== undefined && o.responseText !== "")
		{
			var json = $.parseJSON(o.responseText);
			if(json.status == "fail")
			{
				var $modal = il.Modal.dialogue({
					show: true,
					header: "failure", // :TODO:
					body: json.message,
					buttons: [{
						type:      "button",
						label:     "ok", // :TODO:
						className: "btn btn-default",
						callback:  function (e) {
							$modal.hide();
						}
					}]
				});
			}
			else if(json.status == "bg")
			{
				var $modal = il.Modal.dialogue({
					show: true,
					header: "processing", // :TODO:
					body: json.title,
					buttons: [{
						type:      "button",
						label:     "cancel", // :TODO:
						className: "btn btn-default",
						callback:  function (e) {
							$modal.hide();
							il.BgTask.cancel(json.task_id);
						}						
					}]
				});
				
				// start the bg task
				il.BgTask.doAjax({"tid": json.task_id, "cmd":"process"});
				
				// start the progress loop
				il.BgTask.progress(json.task_id, $modal, json.steps, json.current);
			}
			else if(json.status == "block")
			{
				var $modal = il.Modal.dialogue({
					show: true,
					header: "blocked", // :TODO:
					body: json.title,
					buttons: [
						{
							type:      "button",
							label:     "cancel old", // :TODO:
							className: "btn btn-default",
							callback:  function (e) {	
								$modal.hide();
								il.BgTask.doAjax({"tid": json.task_id, "cmd":"unblock"});
							}	
						},
						{
							type:      "button",
							label:     "cancel new", // :TODO:
							className: "btn btn-default",
							callback:  function (e) {
								$modal.hide();
								il.BgTask.cancel(json.task_id);
							}	
						}
					]
				});
			}
			else if(json.status == "processing")
			{
				// still in progress
				il.BgTask.progress(o.argument.task_id, o.argument.modal, json.steps, json.current);				
			}
			else if(json.status == "finished")
			{
				if(json.result_cmd == "redirect")	
				{
					// no modal if background task was not needed
					if(o.argument !== undefined)
					{
						o.argument.modal.hide();
					}
					window.location.href = json.result;
				}
			}
			else if(json.status == "cancelled")
			{
				// could be cancelled by other task (see "unblock")
				// no modal if background task was not needed
				if(o.argument !== undefined)
				{
					o.argument.modal.hide();
				}
			}
		}
	}
}