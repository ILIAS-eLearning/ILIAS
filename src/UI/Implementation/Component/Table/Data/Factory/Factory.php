<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Factory;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Column\Column as ColumnInterface;
use ILIAS\UI\Component\Table\Data\Data\Fetcher\DataFetcher;
use ILIAS\UI\Component\Table\Data\Factory\Factory as FactoryInterface;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Table as TableInterface;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Column;
use ILIAS\UI\Implementation\Component\Table\Data\Format\CSVFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Format\ExcelFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Format\HTMLFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Format\PDFFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Table;

/**
 * Class Factory
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Factory
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Factory implements FactoryInterface
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
    public function table(string $id, string $action_url, string $title, array $columns, DataFetcher $data_fetcher) : TableInterface
    {
        return new Table($id, $action_url, $title, $columns, $data_fetcher);
    }


    /**
     * @inheritDoc
     */
    public function column(string $key, string $title) : ColumnInterface
    {
        return new Column($key, $title);
    }


    /**
     * @inheritDoc
     */
    public function formatCSV() : Format
    {
        return new CSVFormat($this->dic);
    }


    /**
     * @inheritDoc
     */
    public function formatExcel() : Format
    {
        return new ExcelFormat($this->dic);
    }


    /**
     * @inheritDoc
     */
    public function formatPDF() : Format
    {
        return new PDFFormat($this->dic);
    }


    /**
     * @inheritDoc
     */
    public function formatHTML() : Format
    {
        return new HTMLFormat($this->dic);
    }
}
