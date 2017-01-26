<?php
function show_modal_on_button_click_with_async_loaded_content()
{
//	global $DIC;
//
//	$ilCtrl = $DIC['ilCtrl'];
//	$factory = $DIC->ui()->factory();
//	$renderer = $DIC->ui()->renderer();
//	$message = 'Are you sure you want to delete the following items?';
//	$form_action = $ilCtrl->getFormActionByClass('ilsystemstyledocumentationgui');
//
//	// Check if this is the ajax request to deliver the new modal
//	if (isset($_GET['ajaxRenderModal'])) {
//		// Create some random Items
//		$items = [
//			$factory->modal()->interruptiveItem(10, substr(md5(rand()), 0, 7)),
//			$factory->modal()->interruptiveItem(20, substr(md5(rand()), 0, 7)),
//		];
//		$modal = $factory->modal()->interruptive('My Title', $message, $form_action)
//			->withAffectedItems($items);
//		echo $renderer->render($modal);
//		exit();
//	}
//
//	$modal = $factory->modal()->interruptive('My Title', $message, $form_action);
//	$button = $factory->button()->standard('Show Modal Async', '');
//	$ajax_url = $_SERVER['REQUEST_URI'] . '&ajaxRenderModal=1';
//	$connection = $factory->connector()->onClick($button, $modal->getShowAsyncAction($ajax_url));
//
//	return implode('', $renderer->render([$button, $modal], [$connection]));
}