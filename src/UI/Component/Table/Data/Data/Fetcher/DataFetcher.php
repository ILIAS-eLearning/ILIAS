<?php

namespace ILIAS\UI\Component\Table\Data\Data\Fetcher;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\UserTableSettings\Settings;

/**
 * Interface DataFetcher
 *
 * @package ILIAS\UI\Component\Table\Data\Data\Fetcher
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface DataFetcher
{

    /**
     * DataFetcher constructor
     *
     * @param Container $dic
     */
    public function __construct(Container $dic);


    /**
     * @param Settings $user_table_settings
     *
     * @return Data
     */
    public function fetchData(Settings $user_table_settings) : Data;


    /**
     * @return string
     */
    public function getNoDataText() : string;


    /**
     * @return bool
     */
    public function isFetchDataNeedsFilterFirstSet() : bool;


    /**
     * @param RowData[] $data
     * @param int       $max_count
     *
     * @return Data
     */
    public function data(array $data, int $max_count) : Data;


    /**
     * @param string $row_id
     * @param object $original_data
     *
     * @return RowData
     */
    public function propertyRowData(string $row_id, object $original_data) : RowData;


    /**
     * @param string $row_id
     * @param object $original_data
     *
     * @return RowData
     */
    public function getterRowData(string $row_id, object $original_data) : RowData;
}
