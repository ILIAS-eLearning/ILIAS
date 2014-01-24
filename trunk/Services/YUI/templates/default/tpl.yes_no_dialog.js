<script type="text/javascript">
	var {DIALOGNAME};
	
	function {DIALOGNAME}handleYes() 
	{
		this.hide();
		{YES_ACTION}
	};

	function {DIALOGNAME}handleNo() 
	{
		this.hide();
		{NO_ACTION}
	};

	function {DIALOGNAME}init()
	{
		// Instantiate the Dialog
		{DIALOGNAME} = new YAHOO.widget.SimpleDialog("{DIALOGNAME}", 
			{ 
				width: "300px",
				fixedcenter: true,
				visible: false,
				modal: true,
				draggable: true,
				close: true,
				text: "{DIALOG_MESSAGE}",
				icon: {ICON},
				constraintoviewport: true,
				buttons: [ 
					{ text:"{TEXT_YES}", id: "boris", handler:{DIALOGNAME}handleYes<!-- BEGIN isDefaultYes -->, isDefault:true <!-- END isDefaultYes -->},
					{ text:"{TEXT_NO}",  handler:{DIALOGNAME}handleNo<!-- BEGIN isDefaultNo -->, isDefault:true <!-- END isDefaultNo --> } 
				]
			} 
		);
		{DIALOGNAME}.setHeader("{DIALOG_HEADER}");
		// Render the Dialog
		{DIALOGNAME}.render(document.body);
	}

	YAHOO.util.Event.addListener(window, "load", {DIALOGNAME}init);
</script>