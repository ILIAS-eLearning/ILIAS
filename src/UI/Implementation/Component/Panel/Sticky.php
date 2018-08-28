<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Stick
 * @author Alex Killing <killing@leifos.de>
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Sticky implements C\Panel\Sticky {
	use ComponentHelper;

	/**
	 * @var \ILIAS\UI\Component\Panel\StickyView[]
	 */
	protected $views;

	/**
	 * @param \ILIAS\UI\Component\Panel\StickyView[] $views
	 */
	public function __construct(array $views) {
		$types = [C\Component\Panel\StickyView::class];
		$this->checkArgListElements("views", $views, $types);

		$this->views = $views;
	}
	/**
	 * @inheritdoc
	 */
	public function getViews() {
		return $this->views;
	}
}
?>