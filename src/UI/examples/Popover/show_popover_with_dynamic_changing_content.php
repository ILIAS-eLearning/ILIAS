<?php
function show_popover_with_dynamic_changing_content()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	if (isset($_GET['page']) && $_GET['page'] == 'overview') {
		$replaceSignal = new \ILIAS\UI\Implementation\Component\Popover\ReplaceContentSignal($_GET['replaceSignal']);
		$button1 = $factory->button()->standard('Go to page 1', '#')
			->withOnClick($replaceSignal->withAsyncRenderUrl($_SERVER['REQUEST_URI'] . '&page=1&replaceSignal=' . $_GET['replaceSignal']));
		$button2 = $factory->button()->standard('Go to page 2', '#')
			->withOnClick($replaceSignal->withAsyncRenderUrl($_SERVER['REQUEST_URI'] . '&page=2&replaceSignal=' . $_GET['replaceSignal']));
		$button3 = $factory->button()->standard('Go to page 3', '#')
			->withOnClick($replaceSignal->withAsyncRenderUrl($_SERVER['REQUEST_URI'] . '&page=3&replaceSignal=' . $_GET['replaceSignal']));
		$list = $factory->listing()->unordered([$button1, $button2, $button3]);
		echo $renderer->renderAsync($list);
		exit();
	}

	if (isset($_GET['page'])) {
		$page = (int) $_GET['page'];
		$replaceSignal = new \ILIAS\UI\Implementation\Component\Popover\ReplaceContentSignal($_GET['replaceSignal']);
		$replaceSignal = $replaceSignal->withAsyncRenderUrl($_SERVER['REQUEST_URI'] . '&page=overview&replaceSignal=' . $_GET['replaceSignal']);
		$button = $factory->button()->standard('Back to Overview', '#')
			->withOnClick($replaceSignal);
		$intro = $factory->legacy("<p>You are viewing page {$page}</p>");
		echo $renderer->renderAsync([$intro, $button]);
		exit();
	}

	$popover = $factory->popover($factory->legacy(''))
		->withTitle('Pages');
	$async_url = $_SERVER['REQUEST_URI'] . '&page=overview&replaceSignal=' . (string) $popover->getReplaceContentSignal();
	$popover = $popover->withAsyncContentUrl($async_url);
	$button = $factory->button()->standard('Show Popover', '#')
		->withOnClick($popover->getShowSignal());
	return $renderer->render([$popover, $button]);
}