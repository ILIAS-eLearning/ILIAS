<?php

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Data;

use ILIAS\UI\Component\Input\Field\FilterInput;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Fetcher\DataFetcher;
use ILIAS\UI\Component\Table\Data\Format\BrowserFormat;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Settings\Storage\SettingsStorage;
use ILIAS\UI\Component\Table\Data\Table as TableInterface;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Table\Data\Format\DefaultBrowserFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Settings\Storage\DefaultSettingsStorage;

/**
 * Class Table
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Table implements TableInterface
{

    use ComponentHelper;
    /**
     * @var string
     */
    protected $table_id = "";
    /**
     * @var string
     */
    protected $action_url = "";
    /**
     * @var string
     */
    protected $title = "";
    /**
     * @var Column[]
     */
    protected $columns = [];
    /**
     * @var DataFetcher
     */
    protected $data_fetcher;
    /**
     * @var FilterInput[]
     */
    protected $filter_fields = [];
    /**
     * @var BrowserFormat
     */
    protected $browser_format = null;
    /**
     * @var Format[]
     */
    protected $formats = [];
    /**
     * @var string[]
     */
    protected $multiple_actions = [];
    /**
     * @var SettingsStorage
     */
    protected $settings_storage;


    /**
     * Table constructor
     *
     * @param string      $table_id
     * @param string      $action_url
     * @param string      $title
     * @param Column[]    $columns
     * @param DataFetcher $data_fetcher
     */
    public function __construct(string $table_id, string $action_url, string $title, array $columns, DataFetcher $data_fetcher)
    {
        $this->table_id = $table_id;

        $this->action_url = $action_url;

        $this->title = $title;

        $this->checkArgListElements("columns", $columns, [Column::class]);
        $this->columns = $columns;

        $this->data_fetcher = $data_fetcher;
    }


    /**
     * @inheritDoc
     */
    public function getTableId() : string
    {
        return $this->table_id;
    }


    /**
     * @inheritDoc
     */
    public function withTableId(string $table_id) : TableInterface
    {
        $clone = clone $this;

        $clone->table_id = $table_id;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getActionUrl() : string
    {
        return $this->action_url;
    }


    /**
     * @inheritDoc
     */
    public function withActionUrl(string $action_url) : TableInterface
    {
        $clone = clone $this;

        $clone->action_url = $action_url;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @inheritDoc
     */
    public function withTitle(string $title) : TableInterface
    {
        $clone = clone $this;

        $clone->title = $title;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getColumns() : array
    {
        return $this->columns;
    }


    /**
     * @inheritDoc
     */
    public function withColumns(array $columns) : TableInterface
    {
        $this->checkArgListElements("columns", $columns, [Column::class]);

        $clone = clone $this;

        $clone->columns = $columns;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getDataFetcher() : DataFetcher
    {
        return $this->data_fetcher;
    }


    /**
     * @inheritDoc
     */
    public function withFetchData(DataFetcher $data_fetcher) : TableInterface
    {
        $clone = clone $this;

        $clone->data_fetcher = $data_fetcher;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getFilterFields() : array
    {
        return $this->filter_fields;
    }


    /**
     * @inheritDoc
     */
    public function withFilterFields(array $filter_fields) : TableInterface
    {
        $this->checkArgListElements("filter_fields", $filter_fields, [FilterInput::class]);

        $clone = clone $this;

        $clone->filter_fields = $filter_fields;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getBrowserFormat() : BrowserFormat
    {
        if ($this->browser_format === null) {
            global $DIC; // TODO: !!!

            $this->browser_format = new DefaultBrowserFormat($DIC);
        }

        return $this->browser_format;
    }


    /**
     * @inheritDoc
     */
    public function withBrowserFormat(BrowserFormat $browser_format) : TableInterface
    {
        $clone = clone $this;

        $clone->browser_format = $browser_format;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getFormats() : array
    {
        return $this->formats;
    }


    /**
     * @inheritDoc
     */
    public function withFormats(array $formats) : TableInterface
    {
        $this->checkArgListElements("formats", $formats, [Format::class]);

        $clone = clone $this;

        $clone->formats = $formats;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getMultipleActions() : array
    {
        return $this->multiple_actions;
    }


    /**
     * @inheritDoc
     */
    public function withMultipleActions(array $multiple_actions) : TableInterface
    {
        $clone = clone $this;

        $clone->multiple_actions = $multiple_actions;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getSettingsStorage() : SettingsStorage
    {
        if ($this->settings_storage === null) {
            global $DIC; // TODO: !!!

            $this->settings_storage = new DefaultSettingsStorage($DIC);
        }

        return $this->settings_storage;
    }


    /**
     * @inheritDoc
     */
    public function withSettingsStorage(SettingsStorage $settings_storage) : TableInterface
    {
        $clone = clone $this;

        $clone->settings_storage = $settings_storage;

        return $clone;
    }
}
