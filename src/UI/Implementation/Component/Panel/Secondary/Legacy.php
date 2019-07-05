<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Secondary;

use ILIAS\UI\Component as C;

/**
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Legacy extends Secondary implements C\Panel\Secondary\Legacy {

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var \ILIAS\UI\Component\Legacy\Legacy
	 */
	protected $legacy;

	public function __construct(string $title, C\Legacy\Legacy $legacy)
	{
		$this->checkStringArg("title",$title);

		$this->title = $title;
		$this->legacy = $legacy;
	}

	/**
	 * @inheritdoc
	 */
	public function getLegacyComponent() : C\Legacy\Legacy
	{
		return $this->legacy;
	}
}