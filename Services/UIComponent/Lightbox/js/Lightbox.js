il.Lightbox = {

	current_wrapper: {},
	on_deactivation_func: {},

	init: function() {
	},

	activateView: function(id) {
		// copy hidden lightbox content div to the end
		$('#' + id).appendTo('body');
		
		// hide content
		$("#ilAll").addClass("ilNoDisplay");
		
		// show lightbox content div
		$('#' + id).removeClass("ilNoDisplay");
	},

	onDeactivation: function (lightbox_id, func) {
		this.on_deactivation_func[lightbox_id] = func;
	},
	
	deactivateView: function(id) {
		// hide lightbox content div
		$('#' + id).addClass("ilNoDisplay");

		// show content
		$("#ilAll").removeClass("ilNoDisplay");
		
		this.unloadWrapperFromLightbox(id);
		if (il.Lightbox.on_deactivation_func[id] !== undefined && il.Lightbox.on_deactivation_func[id] != null) {
			il.Lightbox.on_deactivation_func[id](id);
		}
	},

	// load content from a wrapper container into the lighbox
	loadWrapperToLightbox: function(wrapper_id, lightbox_id) {
		this.current_wrapper[lightbox_id] = wrapper_id;
		
		$("#" + wrapper_id).children().appendTo('#' + lightbox_id + ' .ilLightboxContent');
	},
	
	// move the lightbox content back to the container
	unloadWrapperFromLightbox: function(lightbox_id) {
		
		if (this.current_wrapper[lightbox_id] != "") {
			$('#' + lightbox_id + ' .ilLightboxContent').children().appendTo('#' + this.current_wrapper[lightbox_id]);
		}
		this.current_wrapper[lightbox_id] = '';
	},

}
il.Util.addOnLoad(il.Lightbox.init);
