$(document).ready(function() {
	$(document).on("click","#catVideoLeftId, #catVideoCenterId, #catVideoRightId", function(e) {
		
		window.open($("#catVideoLinkUrlId").html(), '_blank');
	});
});