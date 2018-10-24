```

class fooGUI {

	function showListAndFilter() {
	
			//Step 0: Declare dependencies
        	global $DIC;
        	$ui = $DIC->ui()->factory();
        	$renderer = $DIC->ui()->renderer();
        
        	//Step 1: Define some input fields to plug into the filter.
        	$text_input1 = $ui->input()->field()->text("Text 1");
        	$text_input2 = $ui->input()->field()->text("Text 2");
        	$numeric_input1 = $ui->input()->field()->numeric("Number 1");
        	$numeric_input2 = $ui->input()->field()->numeric("Number 2");
                
        	$filter = $DIC->uiService()->filter()->standard(
        		$DIC->ctrl()->getLinkTarget($this, "showListAndFilter"),
        		$text_input1, $text_input2, $numeric_input1, $numeric_input2],
                [true, true, true, true], true);
        	);
        
        	// ...
        	$list = ....
        
        	//Step 4: Render the filter
        	return $renderer->render([$filter, $list]);
	
	
	}

}


```