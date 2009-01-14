<script type="text/javascript">
ilAddOnLoad(
	function()
	{
		window.setTimeout('countdown();', ({TIME_LEFT} - 300000));
	}
);

function countdown() 
{
	alert('{ALERT}');
}

</script>