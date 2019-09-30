<?php

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Data\Settings\Storage;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Settings\Settings as SettingsInterface;
use ILIAS\UI\Component\Table\Data\Settings\Sort\SortField as SortFieldInterface;
use ILIAS\UI\Implementation\Component\Table\Data\Settings\Settings;
use ILIAS\UI\Implementation\Component\Table\Data\Settings\Sort\SortField;
use ilTablePropertiesStorage;

/**
 * Class DefaultSettingsStorage
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Settings\Storage
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class DefaultSettingsStorage extends AbstractSettingsStorage
{

    /**
     * @var ilTablePropertiesStorage
     */
    protected $properties_storage;


    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);

        $this->properties_storage = new ilTablePropertiesStorage();
        $this->properties_storage->properties = array_reduce(self::VARS, function (array $properties, string $property) : array {
            $properties[$property] = ["storage" => "db"];

            return $properties;
        }, []);
    }


    /**
     * @inheritDoc
     */
    public function read(string $table_id, int $user_id) : SettingsInterface
    {
        $settings = new Settings($this->dic->ui()->factory()->viewControl()->pagination());

        foreach (self::VARS as $property) {
            $value = json_decode($this->properties_storage->getProperty($table_id, $user_id, $property) ?? "", true);

            if (!empty($value)) {
                switch ($property) {
                    case self::VAR_SORT_FIELDS:
                        $settings = $settings->withSortFields(array_map(function (array $sort_field) : SortFieldInterface {
                            return new SortField($sort_field[self::VAR_SORT_FIELD], $sort_field[self::VAR_SORT_FIELD_DIRECTION]);
                        }, $value));
                        break;

                    default:
                        if (method_exists($settings, $method = "with" . $this->strToCamelCase($property))) {
                            $settings = $settings->{$method}($value);
                        }
                }
            }
        }

        return $settings;
    }


    /**
     * @inheritDoc
     */
    public function store(SettingsInterface $settings, string $table_id, int $user_id) : void
    {
        foreach (self::VARS as $property) {
            $value = "";
            if (method_exists($settings, $method = "get" . $this->strToCamelCase($property))) {
                $value = $settings->{$method}();
            } else {
                if (method_exists($settings, $method = "is" . $this->strToCamelCase($property))) {
                    $value = $settings->{$method}();
                }
            }

            $this->properties_storage->storeProperty($table_id, $user_id, $property, json_encode($value));
        }
    }
}
