<?php
function drilldown() {

	global $DIC;
	$f = $DIC->ui()->factory();
	$fd = $f->drilldown();
	$renderer = $DIC->ui()->renderer();

	$ico = $f->icon()->standard('', '')->withSize('medium');

	$image = $f->image()->responsive("src/UI/examples/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
	$page = $f->modal()->lightboxImagePage($image, 'Mountains');
	$modal = $f->modal()->lightbox($page);

	$button = $f->button()->bulky($ico->withAbbreviation('lnk'), 'Modal', '#')
		->withOnClick($modal->getShowSignal());


	$dd = $f->drilldown()->drilldown('root', $ico->withAbbreviation('0'))
		->withAdditionalEntry(
			$fd->level('1', $ico->withAbbreviation('1'))
				->withAdditionalEntry($fd->level('1.1')
					->withAdditionalEntry($button)
					->withAdditionalEntry($button)
				)
				->withAdditionalEntry(
					$fd->level('1.2')
						->withAdditionalEntry(
							$fd->level('1.2.1')
								->withAdditionalEntry($button)
								->withAdditionalEntry($button)
								->withAdditionalEntry($button)
						)
						->withAdditionalEntry($fd->level('1.2.2'))
				)
				->withAdditionalEntry(
					$fd->level('1.3')
						->withAdditionalEntry($fd->level('1.3.1'))
						->withAdditionalEntry($fd->level('1.3.2'))
				)
		)
		->withAdditionalEntry(
			$fd->level('2', $ico->withAbbreviation('2'))
				->withAdditionalEntry($fd->level('2.1'))
		)
		->withAdditionalEntry(
			$fd->level('3', $ico->withAbbreviation('3'))
				->withAdditionalEntry($fd->level('3.1'))
		)
		;

	return $renderer->render([
		$dd,
		$modal
	]);
}