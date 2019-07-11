<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Filter\Storage;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Filter\Storage\FilterStorage;

/**
 * Class AbstractFilterStorage
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Filter\Storage
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractFilterStorage implements FilterStorage {

	/**
	 * @var Container
	 */
	protected $dic;


	/**
	 * @inheritDoc
	 */
	public function __construct(Container $dic) {
		$this->dic = $dic;
	}


	/**
	 * @param string $string
	 *
	 * @return string
	 */
	protected function strToCamelCase(string $string): string {
		return str_replace("_", "", ucwords($string, "_"));
	}
}
