il.BgTask = {

	ajax: "",

	setAjax: function (url) {
		this.ajax = url;
	},
	
	init: function (handler_id, params) {		
		this.doAjax({hid: handler_id, par: params});
	},
	
	initMultiForm: function(handler_id) {	
		$('input.ilbgtasksubmit').each(function() {
			$(this).click(function(e) {	
				// gather selected objects
				var form = $(this).closest("form").serializeArray();
				var ref_ids = [];
				$.each(form, function() {					
					if(this.name == "id[]")
					{
						ref_ids.push(parseInt(this.value));
					}					
				});
				// any selection?
				if(ref_ids.length)
				{
					// stop the form submit
					e.preventDefault();
					e.stopPropagation();
					
					// init modal/bgtask
					il.BgTask.init(handler_id, ref_ids);
				}
			})
		})		
	},
	
	cancel: function (task_id) {						
		il.BgTask.doAjax({"tid": task_id, "cmd":"cancel"});
	},
	
	progress: function (task_id, modal, steps, current) {
		
		// update progress bar
		if(current !== undefined)
		{
			var pbar = modal.modal.find("#progress_div_bgtask_" + task_id);
			var perc = Math.round(current/steps*100);

			pbar.css('width', perc + '%');
			pbar.text(perc + '%');
			pbar.attr('aria-valuenow', perc);
		}
				
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
			var json = JSON.parse(o.responseText);
			if(json.status == "fail")
			{
				var $modal = il.Modal.dialogue({
					show: true,
					header: json.title,
					body: json.message,
					buttons: [{
						type:      "button",
						label:     json.button,
						className: "btn btn-default",
						callback:  function (e) {
							$modal.hide();
						}
					}]
				});
			}
			else if(json.status == "bg")
			{
				// progress bar
				var pbar = "<div class=\"progress\"><div id=\"progress_div_bgtask_" + json.task_id + "\" class=\"progress-bar progress-bar-info progress-bar-striped active\"  valmax=\"100\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width:0\"></div>";
				
				var $modal = il.Modal.dialogue({
					show: true,
					header: json.title,
					body: "<p>" + json.message + "</p>" + pbar,
					buttons: [{
						type:      "button",
						label:     json.button,
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
					header: json.title,
					body: json.message,
					buttons: [
						{
							type:      "button",
							label:     json.button_old,
							className: "btn btn-default",
							callback:  function (e) {	
								$modal.hide();
								il.BgTask.doAjax({"tid": json.task_id, "cmd":"unblock"});
							}	
						},
						{
							type:      "button",
							label:     json.button_new,
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