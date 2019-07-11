<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Filter\Storage;

use ILIAS\UI\Component\Table\Data\Filter\Filter;
use ILIAS\UI\Component\Table\Data\Filter\Sort\FilterSortField;
use ilTablePropertiesStorage;

/**
 * Class DefaultFilterStorage
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Filter\Storage
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class DefaultFilterStorage extends AbstractFilterStorage {

	/**
	 * @var ilTablePropertiesStorage
	 */
	protected $properties_storage;


	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();

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
	public function read(string $table_id, int $user_id): Filter {
		$filter = $this->filter($table_id, $user_id);

		foreach (self::VARS as $property) {
			$value = json_decode($this->properties_storage->getProperty($filter->getTableId(), $filter->getUserId(), $property), true);

			if (!empty($value)) {
				switch ($property) {
					case self::VAR_SORT_FIELDS:
						$filter = $filter->withSortFields(array_map(function (array $sort_field): FilterSortField {
							return $this->sortField($sort_field[self::VAR_SORT_FIELD], $sort_field[self::VAR_SORT_FIELD_DIRECTION]);
						}, $value));
						break;

					default:
						if (method_exists($filter, $method = "with" . $this->strToCamelCase($property))) {
							$filter = $filter->{$method}($value);
						}
				}
			}
		}

		return $filter;
	}


	/**
	 * @inheritDoc
	 */
	public function store(Filter $filter): void {
		foreach (self::VARS as $property) {
			$value = "";
			if (method_exists($filter, $method = "get" . $this->strToCamelCase($property))) {
				$value = $filter->{$method}();
			} else {
				if (method_exists($filter, $method = "is" . $this->strToCamelCase($property))) {
					$value = $filter->{$method}();
				}
			}

			$this->properties_storage->storeProperty($filter->getTableId(), $filter->getUserId(), $property, json_encode($value));
		}
	}
}
