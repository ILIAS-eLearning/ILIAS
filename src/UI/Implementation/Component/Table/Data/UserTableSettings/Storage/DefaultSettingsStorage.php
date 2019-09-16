<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\UserTableSettings\Storage;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\UserTableSettings\Settings;
use ILIAS\UI\Component\Table\Data\UserTableSettings\Sort\SortField;
use ilTablePropertiesStorage;

/**
 * Class DefaultSettingsStorage
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\UserTableSettings\Storage
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class DefaultSettingsStorage extends AbstractSettingsStorage {

	/**
	 * @var ilTablePropertiesStorage
	 */
	protected $properties_storage;


	/**
	 * @inheritDoc
	 */
	public function __construct(Container $dic) {
		parent::__construct($dic);

		// TODO: Not use ilTablePropertiesStorage and reimplement it - Currently just a "fast solution" to save the table filter
		$this->properties_storage = new ilTablePropertiesStorage();
		$this->properties_storage->properties = array_reduce(self::VARS, function (array $properties, string $property): array {
			$properties[$property] = [ "storage" => "db" ];

			return $properties;
		}, []);
	}


	/**
	 * @inheritDoc
	 */
	public function read(string $table_id, int $user_id): Settings {
		$user_table_settings = $this->userTableSettings();

		foreach (self::VARS as $property) {
			$value = json_decode($this->properties_storage->getProperty($table_id, $user_id, $property), true);

			if (!empty($value)) {
				switch ($property) {
					case self::VAR_SORT_FIELDS:
						$user_table_settings = $user_table_settings->withSortFields(array_map(function (array $sort_field): SortField {
							return $this->sortField($sort_field[self::VAR_SORT_FIELD], $sort_field[self::VAR_SORT_FIELD_DIRECTION]);
						}, $value));
						break;

					default:
						if (method_exists($user_table_settings, $method = "with" . $this->strToCamelCase($property))) {
							$user_table_settings = $user_table_settings->{$method}($value);
						}
				}
			}
		}

		return $user_table_settings;
	}


	/**
	 * @inheritDoc
	 */
	public function store(Settings $user_table_settings, string $table_id, int $user_id): void {
		foreach (self::VARS as $property) {
			$value = "";
			if (method_exists($user_table_settings, $method = "get" . $this->strToCamelCase($property))) {
				$value = $user_table_settings->{$method}();
			} else {
				if (method_exists($user_table_settings, $method = "is" . $this->strToCamelCase($property))) {
					$value = $user_table_settings->{$method}();
				}
			}

			$this->properties_storage->storeProperty($table_id, $user_id, $property, json_encode($value));
		}
	}
}
