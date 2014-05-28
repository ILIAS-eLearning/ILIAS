<script type="text/javascript">
YAHOO.util.Event.onContentReady("{POST_VAR}-bc", function () {

	// added color initialisation
	// Init color label
	YAHOO.util.Event.onContentReady("{POST_VAR}-current-color", function () {
		YAHOO.util.Dom.setStyle("{POST_VAR}-current-color", "backgroundColor", "{INIT_COLOR}");
	});

	// Update color label on key up
	YAHOO.util.Event.on('{POST_VAR}', 'keyup', function (e) {
		var field = document.getElementById("{POST_VAR}");
		YAHOO.util.Dom.setStyle("{POST_VAR}-current-color", "backgroundColor", '#' + field.value);
	});


        // Create a Menu instance to house the ColorPicker instance
        var oColorPickerMenu = new YAHOO.widget.Menu("{POST_VAR}-color-picker-menu");

        // Create a Button instance of type "split"
        var oButton = new YAHOO.widget.Button({ 
                                            type: "split", 
                                            id: "{POST_VAR}-color-picker-button", 
                                            label: "<em id=\"{POST_VAR}-current-color\">&nbsp;&nbsp;&nbsp;&nbsp;</em>", 
                                            menu: oColorPickerMenu, 
                                            container: this });
											
		oButton.on("appendTo", function () {
            oColorPickerMenu.setBody("&#32;");
            oColorPickerMenu.body.id = "{POST_VAR}-color-picker-container";

            // Render the Menu into the Button instance's parent element

            oColorPickerMenu.render(this.get("container"));			
		});
      
        function onButtonOption() {

            /*
                Create an empty body element for the Menu instance in order to 
                reserve space to render the ColorPicker instance into.
            */

/*            oColorPickerMenu.setBody("&#32;");
            oColorPickerMenu.body.id = "{POST_VAR}-color-picker-container";

            // Render the Menu into the Button instance's parent element

            oColorPickerMenu.render(this.get("container"));
*/
            /*
                 Create a new ColorPicker instance, placing it inside the body 
                 element of the Menu instance.
            */

            var oColorPicker = new YAHOO.widget.ColorPicker(oColorPickerMenu.body.id, {
                                    showcontrols: false,
									showhsvcontrols: false,
									showrgbcontrols: false,
									showhexcontrols: false,
                                    images: {
                                    	PICKER_THUMB: "{THUMB_PATH}",
                                    	HUE_THUMB: "{HUE_THUMB_PATH}"
                                    }
                                });
                                
			// Init default color
			oColorPicker.setValue(YAHOO.util.Color.hex2rgb("{INIT_COLOR_SHORT}"),false);
            
		     // Align the Menu to its Button
            oColorPickerMenu.align();

            /*
                Add a listener for the ColorPicker instance's "rgbChange" event
                to update the background color and text of the Button's 
                label to reflect the change in the value of the ColorPicker.
            */

            oColorPicker.on("rgbChange", function (p_oEvent) {

                var sColor = "#" + this.get("hex");
                var sColorShort = this.get("hex");
                
                oButton.set("value", sColor);


                YAHOO.util.Dom.setStyle("{POST_VAR}-current-color", "backgroundColor", sColor);
                //YAHOO.util.Dom.get("{POST_VAR}-current-color").innerHTML = "Current color is " + sColor;
                
                // Set value of assigned text field
                YAHOO.util.Dom.get("{COLOR_ID}").value = sColorShort;
            
            });
            
            

            // Remove this event listener so that this code runs only once
            this.unsubscribe("option", onButtonOption);
        }

      /* 
      * Init label background color
      */
      
      YAHOO.util.Dom.setStyle("{POST_VAR}-current-color", "backgroundColor", "{INIT_COLOR}");
      //YAHOO.util.Dom.get("{POST_VAR}-current-color").innerHTML = "Current color is " + "{INIT_COLOR}";
      
        
        /*
            Add a listener for the "option" event.  This listener will be
            used to defer the creation the ColorPicker instance until the 
            first time the Button's Menu instance is requested to be displayed
            by the user.
        */
        
        oButton.on("option", onButtonOption);
    
    });

		function colorchanged(e) {
			if ((this.value) && (this.value.length == 6))
			{
				this.value = this.value.toUpperCase();
			  YAHOO.util.Dom.setStyle("current-color", "backgroundColor", '#' + this.value);
			}
		}
		YAHOO.util.Event.addListener("{COLOR_ID}", "change", colorchanged);
</script>
<style type="text/css">
<!--

#{POST_VAR}-color-picker-container .yui-picker-controls,
#{POST_VAR}-color-picker-container .yui-picker-swatch,
#{POST_VAR}-color-picker-container .yui-picker-websafe-swatch {

	display: none;

}

#{POST_VAR}-color-picker-menu .bd {

	width: 220px;    
	height: 190px;

}

-->
</style>

