<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
namespace ILIAS\Refinery\Container;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Container\AddLabels;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class Group
{
	/**
	 * @var Factory
	 */
	private $dataFactory;

	public function __construct(Factory $dataFactory)
	{
		$this->dataFactory = $dataFactory;
	}

	/**
	 * Adds to any array keys for each value
	 *
	 * @param array $labels
	 * @return mixed
	 */
	public function addLabels(array $labels)
	{
		return new AddLabels($labels, $this->dataFactory);
	}
}
