il.News = {

	edit: function(id) {
		var t = il.News;

		il.Modal.dialogue({
			id:       "il_news_edit_modal",
			show: true,
			header: "Edit",
			buttons:  {
			}
		});
		$("#il_news_edit_modal .modal-body").html("");

		return false;
	}

};