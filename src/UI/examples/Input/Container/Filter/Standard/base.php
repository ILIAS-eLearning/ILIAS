<?php
/**
 * Example show how to create and render a basic filter.
 */
function base() {
	//Step 0: Declare dependencies
	global $DIC;
	$ui = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$request = $DIC->http()->request();

	//Step 1: Define some input fields to plug into the filter.
	$title_input = $ui->input()->field()->text("Title");
	$desc_input = $ui->input()->field()->text("Description");
	$with_def = $ui->input()->field()->text("With Default")->withValue("Def.Value");
	$init_hide = $ui->input()->field()->text("Hidden initially");

	//Step 3: Define the filter and attach the inputs. The filter is initially activated in this case.
	$action = $DIC->ctrl()->getLinkTargetByClass("ilsystemstyledocumentationgui", "entries", "", true);
	//$action = $_SERVER['REQUEST_URI'];
	$filter = $DIC->uiService()->filter()->standard("filter_ID", $action, [
		"title" => $title_input,
		"description" => $desc_input,
		"with_def" => $with_def,
		"init_hide" => $init_hide,
	],
		[true, true, true, false], true, true);

	// @todo: this is something we need to do better
//	if ($request->getMethod() == "POST" && $_GET["cmd"] == "render") {
	$filter_data = $DIC->uiService()->filter()->getData($filter);
//	}
//	else {
		/** @var \ILIAS\UI\Implementation\Component\Input\Field\Input $i */
//		foreach ($filter->getInputs() as $k => $i)
//		{
//			$filter_data[$k] = $i->getValue();
//		}
//	}


	//Step 4: Render the filter
	return $renderer->render($filter).$_GET["cmdFilter"].": ".print_r($filter_data, true);
}
