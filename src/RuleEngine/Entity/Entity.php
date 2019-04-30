<?php

namespace ILIAS\RuleEngine\Entity;

class Entity {

	/**
	 * @var string
	 */
	protected $primary_key;
	/**
	 * @var Field[]
	 */
	protected $fields = [];
	/**
	 * @var string
	 */
	protected $table_name;


	/**
	 * @param Field $field
	 */
	public function addField(Field $field) {
		$this->fields[$field->getName()] = $field;
	}


	/**
	 * @param $name
	 *
	 * @return string
	 */
	public function getTypeFor($name): string {
		if ($this->hasField($name) === true) {
			return $this->fields[$name]->getType();
		}
		//TODO exception
	}


	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasField($name) {
		return array_key_exists($name, $this->fields);
	}


	/**
	 * @return array
	 */
	public function getFieldNames() {
		return array_keys($this->fields);
	}

	public function getQuery() {
		return "SELECT ".implode(",",$this->getFieldNames())." from ".$this->getTableName();
	}


	/**
	 * @return string
	 */
	public function getPrimaryKey(): string {
		return $this->primary_key;
	}


	/**
	 * @param string $primary_key
	 */
	public function setPrimaryKey(string $primary_key): void {
		$this->primary_key = $primary_key;
	}


	/**
	 * @return Field[]
	 */
	public function getFields(): array {
		return $this->fields;
	}


	/**
	 * @param Field[] $fields
	 */
	public function setFields(array $fields): void {
		$this->fields = $fields;
	}


	/**
	 * @return string
	 */
	public function getTableName(): string {
		return $this->table_name;
	}


	/**
	 * @param string $table_name
	 */
	public function setTableName(string $table_name): void {
		$this->table_name = $table_name;
	}
}
