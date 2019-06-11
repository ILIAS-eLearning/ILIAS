<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Link;

/**
 * Footer
 */
class Footer implements MainControls\Footer
{
	use ComponentHelper;

	public function __construct(array $links, string $text = '')
	{

		//assure links is of Link\Standard
		$this->links = $links;
		$this->text = $text;
	}

	public function getLinks(): array
	{
		return $this->links;
	}

	public function getText(): string
	{
		return $this->text;
	}

}
