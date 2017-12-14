<?php

use ILIAS\UI\Component\Input\Field\TagInput;

function with_async_data() {
	/**
	 * @var $DIC \ILIAS\DI\Container
	 */
	global $DIC;
	$ui = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$http = $DIC->http();

	// This provides the filered results
	if ($http->request()->getQueryParams()['taginput_dataprovider1']) {
		$cities = json_decode(file_get_contents("src/UI/examples/Input/Field/TagInput/cities.json"));
		$query = $http->request()->getQueryParams()[TagInput::QUERY_WILDCARD];
		$matching = array_filter($cities, function ($data) use ($query, $DIC) {
			return (strpos($data->name, $query) !== false);
		});
		echo json_encode($matching);
		exit;
	}

	//Step 1: Define the text input field and attach some default value
	$multi_select_input = $ui->input()
	                         ->field()
	                         ->tagInput("Basic Multi-Select Input", "Just some basic input")
	                         ->withOptionsProviderURL($_SERVER['REQUEST_URI'] . '&taginput_dataprovider1=true');

	//Step 2: Define the form and attach the section.
	$form = $ui->input()->container()->form()->standard("#", [$multi_select_input]);

	//Step 4: Render the form with the text input field
	return $renderer->render($form);
}
