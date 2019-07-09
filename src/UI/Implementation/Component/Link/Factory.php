<?php

declare(strict_types=1);

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Component\Link as L;
use ILIAS\UI\Component\Symbol\Symbol;

class Factory implements L\Factory
{
	/**
	 * @inheritdoc
	 */
	public function standard(string $label, string $action): L\Standard
	{
		return new Standard($label, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function bulky(Symbol $symbol, string $label, string $action): L\Bulky
	{
		return new Bulky($symbol, $label, $action);
	}

}
