# UI Filter Service

The filter service wraps the KS element of Standard Input Filters. It's main purpose is to manage the state of the filter and it's inputs in the user session.

The basics methods are `$DIC->uiService()->filter()->standard()` to get a filter instance and `$DIC->uiService()->filter()->getData($filter)` to retrieve the data from the filter.

This service is most probably a temporary solution until session and similar  dependencies are handled differently by the UI framework.

```

class fooGUI {

	function showListAndFilter() {
	
		//Step 0: Declare dependencies
		global $DIC;
		$ui = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();
	 
		//Step 1: Define some input fields to plug into the filter.
		$title_input = $ui->input()->field()->text("Title");
		$select = $ui->input()->field()->select("Selection", ["one" => "One", "two" => "Two", "three" => "Three"]);
		$with_def = $ui->input()->field()->text("With Default")->withValue("Def.Value");
		$init_hide = $ui->input()->field()->text("Hidden initially");
	 
		//Step 2: Define the filter and attach the inputs.
		$action = $DIC->ctrl()->getLinkTargetByClass("ilsystemstyledocumentationgui", "entries", "", true);
		$filter = $DIC->uiService()->filter()->standard("filter_ID", $action, [
			"title" => $title_input,
			"select" => $select,
			"with_def" => $with_def,
			"init_hide" => $init_hide,
		],
			[true, true, true, false], true, true);
	 
		//Step 3: Get filter data
		$filter_data = $DIC->uiService()->filter()->getData($filter);
	 
		//Step 4: Render the filter
		return $renderer->render($filter)."Filter Data: ".print_r($filter_data, true);	
	}

}


```