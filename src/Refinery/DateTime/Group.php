<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\DateTime;

use ILIAS\Data\Factory;

/**
 * @author  Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Group
{
	/**
	 * @var Factory
	 */
	private $dataFactory;

	/**
	 * @param \ILIAS\Data\Factory $dataFactory
	 */
	public function __construct(\ILIAS\Data\Factory $dataFactory)
	{
		$this->dataFactory = $dataFactory;
	}


	public function changeTimezone(string $timezone) {
		return new ChangeTimezone($timezone, $this->dataFactory);
	}

}
