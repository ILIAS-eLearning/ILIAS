il.MediaPool = {
	previewurl: '',

	setPreviewUrl: function (url) {
		il.MediaPool.ajaxurl = url;
		$('#ilMepPreviewContent').closest('.il-modal-roundtrip').on('shown.bs.modal', function () {
			il.MediaPool.resizePreview();
		});
		$('#ilMepPreviewContent').closest('.il-modal-roundtrip').on('hidden.bs.modal', function () {
			$('#ilMepPreviewContent').attr("src", "about:blank");
		});
	},

	preview: function (id) {
		const iframe = document.getElementById('ilMepPreviewContent');
		iframe.style.minHeight = '50vh';
		const signal = iframe.dataset.signal;
		iframe.src = il.MediaPool.ajaxurl + '&mepitem_id=' + id;
		$(document).trigger(
			signal,
			{
				id: signal,
				triggerer: $(document),
				options: JSON.parse('[]'),
			},
		);
	},

	resizePreview: function () {
		var vp = il.Util.getViewportRegion();
		var ifr = il.Util.getRegion('#ilMepPreviewContent');
		console.log(vp);
		console.log(ifr);
		$('#ilMepPreviewContent').css("height", (vp.height - ifr.top + vp.top - 60) + "px");
	}
};
