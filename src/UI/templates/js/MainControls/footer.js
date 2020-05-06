$(document).ready(function() {
	$('.il-maincontrols-footer .il-footer-content .il-footer-permanent-url').click(
		function() {
			document.getElementById('current_perma_link').select();
			document.execCommand('copy');
			return false;
		}
	);
});