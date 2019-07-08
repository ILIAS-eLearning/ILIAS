<?php

namespace ILIAS\UI\Component\Table\Data\Data\Fetcher;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Data\TableData;
use ILIAS\UI\Component\Table\Data\Factory\Factory;
use ILIAS\UI\Component\Table\Data\Filter\TableFilter;

/**
 * Interface TableDataFetcher
 *
 * @package ILIAS\UI\Component\Table\Data\Data\Fetcher
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface TableDataFetcher {

	/**
	 * TableDataFetcher constructor
	 *
	 * @param Container $dic
	 */
	public function __construct(Container $dic);


	/**
	 * @param TableFilter $filter
	 * @param Factory     $factory
	 *
	 * @return TableData
	 */
	public function fetchData(TableFilter $filter, Factory $factory): TableData;
}
