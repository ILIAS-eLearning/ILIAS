<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Data\Fetcher;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Data\Data as DataInterface;
use ILIAS\UI\Component\Table\Data\Data\Fetcher\DataFetcher;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Data;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Row\GetterRowData;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Row\PropertyRowData;

/**
 * Class AbstractDataFetcher
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Data\Fetcher
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractDataFetcher implements DataFetcher
{

    /**
     * @var Container
     */
    protected $dic;


    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }


    /**
     * @inheritDoc
     */
    public function getNoDataText() : string
    {
        return $this->dic->language()->txt(Table::LANG_MODULE . "_no_data");
    }


    /**
     * @inheritDoc
     */
    public function isFetchDataNeedsFilterFirstSet() : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function data(array $data, int $max_count) : DataInterface
    {
        return new Data($data, $max_count);
    }


    /**
     * @inheritDoc
     */
    public function propertyRowData(string $row_id, object $original_data) : RowData
    {
        return new PropertyRowData($row_id, $original_data);
    }


    /**
     * @inheritDoc
     */
    public function getterRowData(string $row_id, object $original_data) : RowData
    {
        return new GetterRowData($row_id, $original_data);
    }
}
