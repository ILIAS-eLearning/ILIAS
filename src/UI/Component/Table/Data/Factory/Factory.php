<?php

namespace ILIAS\UI\Component\Table\Data\Factory;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Fetcher\DataFetcher;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Table;

/**
 * Interface Factory
 *
 * @package ILIAS\UI\Component\Table\Data\Factory
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface Factory {

	/**
	 * Factory constructor
	 *
	 * @param Container $dic
	 */
	public function __construct(Container $dic);


	/**
	 * @param string      $id
	 * @param string      $action_url
	 * @param string      $title
	 * @param Column[]    $columns
	 * @param DataFetcher $data_fetcher
	 *
	 * @return Table
	 */
	public function table(string $id, string $action_url, string $title, array $columns, DataFetcher $data_fetcher): Table;


	/**
	 * @param string $key
	 * @param string $title
	 *
	 * @return Column
	 */
	public function column(string $key, string $title): Column;


	/**
	 * @param string   $key
	 * @param string   $title
	 * @param string[] $actions
	 *
	 * @return Column
	 */
	public function actionColumn(string $key, string $title, array $actions): Column;


	/**
	 * @return Format
	 */
	public function formatCSV(): Format;


	/**
	 * @return Format
	 */
	public function formatExcel(): Format;


	/**
	 * @return Format
	 */
	public function formatPDF(): Format;


	/**
	 * @return Format
	 */
	public function formatHTML(): Format;
}
