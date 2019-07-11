<?php

namespace ILIAS\UI\Component\Table\Data\Data\Fetcher;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Factory\Factory;
use ILIAS\UI\Component\Table\Data\Filter\Filter;

/**
 * Interface DataFetcher
 *
 * @package ILIAS\UI\Component\Table\Data\Data\Fetcher
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface DataFetcher {

	/**
	 * DataFetcher constructor
	 *
	 * @param Container $dic
	 */
	public function __construct(Container $dic);


	/**
	 * @param Filter  $filter
	 * @param Factory $factory
	 *
	 * @return Data
	 */
	public function fetchData(Filter $filter, Factory $factory): Data;


	/**
	 * @return string
	 */
	public function getNoDataText(): string;
}
