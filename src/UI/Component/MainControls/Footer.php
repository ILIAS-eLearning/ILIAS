<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MainControls;

use ILIAS\UI\Component\Component;

/**
 * This describes the Footer.
 */
interface Footer extends Component
{
	/**
	 * @return \ILIAS\UI\Component\Link\Standard[]
	 */
	public function getLinks(): array;

	public function getText(): string;

	/**
	 * @return \ILIAS\Data\URI | null
	 */
	public function getPermanentURL();

	public function withPermanentURL(\ILIAS\Data\URI $url): Footer;

}
