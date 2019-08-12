<?php
function footer()
{
	global $DIC;
	$f = $DIC->ui()->factory();
	$df = new \ILIAS\Data\Factory();
	$renderer = $DIC->ui()->renderer();

	$text = 'Additional info:';
	$links = [];
	$links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");
	$links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");

	$footer = $f->mainControls()->footer($links, $text)
		->withPermanentURL(
			$df->uri(
				$_SERVER['REQUEST_SCHEME'].
				'://'.
				$_SERVER['SERVER_NAME'].
				':'.
				$_SERVER['SERVER_PORT'].
				str_replace(
					'ilias.php',
					'goto.php?target=xxx12345',
					$_SERVER['SCRIPT_NAME']
				)
			)
		);

	return $renderer->render($footer);
}