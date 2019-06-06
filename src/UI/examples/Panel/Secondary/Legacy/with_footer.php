<?php

function with_footer()
{
	global $DIC;

	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$tags = ["PHP", "ILIAS", "Sofware", "SOLID", "Domain Driven"];

	$html = "";
	foreach ($tags as $tag) {
		$html .= $renderer->render($factory->button()->tag($tag, ""));
	}

	$legacy = $factory->legacy($html);
	$link = $factory->link()->standard("Edit Keywords", "");

	$panel = $factory->panel()->secondary()->legacy("panel title", $legacy)->withFooter($link);

	return $renderer->render($panel);
}