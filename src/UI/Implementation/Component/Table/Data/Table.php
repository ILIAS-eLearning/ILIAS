<?php

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Data;

use ILIAS\UI\Component\Input\Field\FilterInput;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Fetcher\DataFetcher;
use ILIAS\UI\Component\Table\Data\Format\BrowserFormat;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Table as TableInterface;
use ILIAS\UI\Component\Table\Data\UserTableSettings\Storage\SettingsStorage;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Table\Data\Format\DefaultBrowserFormat;
use ILIAS\UI\Implementation\Component\Table\Data\UserTableSettings\Storage\DefaultSettingsStorage;

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
    protected $browser_format;
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
    protected $user_table_settings_storage;


    /**
     * @inheritDoc
     */
    public function __construct(string $table_id, string $action_url, string $title, array $columns, DataFetcher $data_fetcher)
    {
        $this->table_id = $table_id;

        $this->action_url = $action_url;

        $this->title = $title;

        $this->columns = $columns;

        $this->data_fetcher = $data_fetcher;

        global $DIC; // TODO: !!!
        $this->browser_format = new DefaultBrowserFormat($DIC);

        $this->user_table_settings_storage = new DefaultSettingsStorage($DIC);
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
        $clone = clone $this;

        $clone->filter_fields = $filter_fields;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getBrowserFormat() : BrowserFormat
    {
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
    public function getUserTableSettingsStorage() : SettingsStorage
    {
        return $this->user_table_settings_storage;
    }


    /**
     * @inheritDoc
     */
    public function withUserTableSettingsStorage(SettingsStorage $user_table_settings_storage) : TableInterface
    {
        $clone = clone $this;

        $clone->user_table_settings_storage = $user_table_settings_storage;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getActionRowId() : string
    {
        return strval(filter_input(INPUT_GET, DefaultBrowserFormat::actionParameter(TableInterface::ACTION_GET_VAR, $this->getTableId())));
    }


    /**
     * @inheritDoc
     */
    public function getMultipleActionRowIds() : array
    {
        return (filter_input(INPUT_POST, DefaultBrowserFormat::actionParameter(TableInterface::MULTIPLE_SELECT_POST_VAR, $this->getTableId()), FILTER_DEFAULT, FILTER_FORCE_ARRAY)
            ?? []);
    }
}
