$(document).ready(function() {
	$(document).on('click','.cat_upload_file button', function(e){
		$(e.target).parent().remove();
	});
});