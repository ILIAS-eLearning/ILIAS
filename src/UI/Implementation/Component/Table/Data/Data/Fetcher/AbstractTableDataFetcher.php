<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Data\Fetcher;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Data\Fetcher\TableDataFetcher;
use ILIAS\UI\Component\Table\Data\DataTable;

/**
 * Class AbstractTableDataFetcher
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Data\Fetcher
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractTableDataFetcher implements TableDataFetcher {

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
	 * @inheritDoc
	 */
	public function getNoDataText(): string {
		return $this->dic->language()->txt(DataTable::LANG_MODULE . "_no_data");
	}
}
