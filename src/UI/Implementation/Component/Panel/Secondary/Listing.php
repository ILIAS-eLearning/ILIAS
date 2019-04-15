<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Secondary;

use ILIAS\UI\Component as C;

/**
 * Class Panel
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Listing extends Secondary implements C\Panel\Secondary\Listing {

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var \ILIAS\UI\Component\Item\Group[]
	 */
	protected $item_groups = array();

	public function __construct(string $title, array $item_groups)
	{
		$this->checkStringArg("title",$title);

		$this->title = $title;
		$this->item_groups = $item_groups;
	}

	/**
	 * @inheritdoc
	 */
	public function getItemGroups(): array {
		return $this->item_groups;
	}
}