il.MediaPool = {
	previewurl: '',

	setPreviewUrl: function (url) {
		il.MediaPool.ajaxurl = url;
		$('#ilMepPreview').on('shown.bs.modal', function () {
			il.MediaPool.resizePreview();
		});
		$('#ilMepPreview').on('hidden.bs.modal', function () {
			$('#ilMepPreviewContent').attr("src", "about:blank");
		});
	},

	preview: function (id) {
		$('#ilMepPreviewContent').attr("src", il.MediaPool.ajaxurl + "&mepitem_id="+ id);
		$('#ilMepPreview').modal('show');
	},

	resizePreview: function () {
		var vp = il.Util.getViewportRegion();
		var ifr = il.Util.getRegion('#ilMepPreviewContent');
		console.log(vp);
		console.log(ifr);
		$('#ilMepPreviewContent').css("height", (vp.height - ifr.top + vp.top - 60) + "px");
	}
};