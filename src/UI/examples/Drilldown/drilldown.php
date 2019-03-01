<?php
function drilldown() {

	global $DIC;
	$f = $DIC->ui()->factory();
	$fd = $f->drilldown();
	$renderer = $DIC->ui()->renderer();

	$ico = $f->icon()->standard('', '');

	$dd = $f->drilldown()->drilldown('root', $ico->withAbbreviation('0'))
		->withAdditionalEntry(
			$fd->level('1', $ico->withAbbreviation('1'))
				->withAdditionalEntry($fd->level('1.1'))
				->withAdditionalEntry(
					$fd->level('1.2')
						->withAdditionalEntry($fd->level('1.2.1'))
						->withAdditionalEntry($fd->level('1.2.2'))
				)
				->withAdditionalEntry(
					$fd->level('1.3')
						->withAdditionalEntry($fd->level('1.3.1'))
						->withAdditionalEntry($fd->level('1.3.2'))
				)
		)
		->withAdditionalEntry($fd->level('2', $ico->withAbbreviation('2')))
		->withAdditionalEntry($fd->level('3', $ico->withAbbreviation('3')))
		;

	return $renderer->render($dd);
}