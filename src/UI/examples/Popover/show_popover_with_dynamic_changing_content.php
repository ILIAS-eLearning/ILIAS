<?php
function show_popover_with_dynamic_changing_content()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$url = $_SERVER['REQUEST_URI'];

	// This is an ajax request to render the overview page
	if (isset($_GET['page']) && $_GET['page'] == 'overview') {
		$signalId = $_GET['replaceSignal'];
		$replaceSignal = new \ILIAS\UI\Implementation\Component\Popover\ReplaceContentSignal($signalId);
		$button1 = $factory->button()->standard('Go to page 1', '#')
			->withOnClick($replaceSignal->withAsyncRenderUrl($url . '&page=1&replaceSignal=' . $signalId));
		$button2 = $factory->button()->standard('Go to page 2', '#')
			->withOnClick($replaceSignal->withAsyncRenderUrl($url . '&page=2&replaceSignal=' . $signalId));
		$button3 = $factory->button()->standard('Go to page 3', '#')
			->withOnClick($replaceSignal->withAsyncRenderUrl($url . '&page=3&replaceSignal=' . $signalId));
		$list = $factory->listing()->unordered([$button1, $button2, $button3]);
		echo $renderer->renderAsync($list);
		exit();
	}

	// This is an ajax request to render a page
	if (isset($_GET['page'])) {
		$page = (int) $_GET['page'];
		$signalId = $_GET['replaceSignal'];
		$replaceSignal = new \ILIAS\UI\Implementation\Component\Popover\ReplaceContentSignal($signalId);
		$button = $factory->button()->standard('Back to Overview', '#')
			->withOnClick($replaceSignal->withAsyncRenderUrl($url . '&page=overview&replaceSignal=' . $signalId));
		$intro = $factory->legacy("<p>You are viewing page {$page}</p>");
		echo $renderer->renderAsync([$intro, $button]);
		exit();
	}

	$popover = $factory->popover($factory->legacy(''))->withTitle('Pages');
	$asyncUrl = $url . '&page=overview&replaceSignal=' . $popover->getReplaceContentSignal()->getId();
	$popover = $popover->withAsyncContentUrl($asyncUrl);
	$button = $factory->button()->standard('Show Popover', '#')
		->withOnClick($popover->getShowSignal());
	return $renderer->render([$popover, $button]);
}