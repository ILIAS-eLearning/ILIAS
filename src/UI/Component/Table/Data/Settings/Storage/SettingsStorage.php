<?php

declare(strict_types=1);

namespace ILIAS\UI\Component\Table\Data\Settings\Storage;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Settings\Settings;
use ILIAS\UI\Component\Table\Data\Table;

/**
 * Interface SettingsStorage
 *
 * @package ILIAS\UI\Component\Table\Data\Settings\Storage
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface SettingsStorage
{

    /**
     * @var string
     */
    const VAR_SORT_FIELDS = "sort_fields";
    /**
     * @var string
     */
    const VAR_SORT_FIELD = "sort_field";
    /**
     * @var string
     */
    const VAR_REMOVE_SORT_FIELD = "remove_sort_field";
    /**
     * @var string
     */
    const VAR_SORT_FIELD_DIRECTION = "sort_field_direction";
    /**
     * @var string
     */
    const VAR_ROWS_COUNT = "rows_count";
    /**
     * @var string
     */
    const VAR_CURRENT_PAGE = "current_page";
    /**
     * @var string
     */
    const VAR_FILTER_FIELD_VALUES = "filter_field_values";
    /**
     * @var string
     */
    const VAR_SELECTED_COLUMNS = "selected_columns";
    /**
     * @var string
     */
    const VAR_SELECT_COLUMN = "select_column";
    /**
     * @var string
     */
    const VAR_DESELECT_COLUMN = "deselect_column";
    /**
     * @var string
     */
    const VAR_EXPORT_FORMAT_ID = "export_format_id";
    /**
     * @var string[]
     */
    const VARS
        = [
            self::VAR_SORT_FIELDS,
            self::VAR_ROWS_COUNT,
            self::VAR_CURRENT_PAGE,
            self::VAR_FILTER_FIELD_VALUES,
            self::VAR_SELECTED_COLUMNS
        ];


    /**
     * SettingsStorage constructor
     *
     * @param Container $dic
     */
    public function __construct(Container $dic);


    /**
     * @param string $table_id
     * @param int    $user_id
     *
     * @return Settings
     */
    public function read(string $table_id, int $user_id) : Settings;


    /**
     * @param Settings $settings
     * @param Table    $component
     *
     * @return Settings
     */
    public function handleDefaultSettings(Settings $settings, Table $component) : Settings;


    /**
     * @param Settings $settings
     * @param string   $table_id
     * @param int      $user_id
     */
    public function store(Settings $settings, string $table_id, int $user_id) : void;
}
