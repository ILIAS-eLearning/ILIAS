<?php

class catReportOrder {
	protected function __construct(catReportTable $a_table) {
		$this->mapping = array();
		$this->default_field = null;
		$this->default_direction = null;
		$this->order_field = null;
		$this->order_direction = null;
		$this->table = $a_table;
	}

	static public function create(catReportTable $a_table) {
		return new catReportOrder($a_table);
	}

	public function getOrderField() {
		if ($this->order_field === null) {
			$this->getOrderFromGETOrDefault();
		}
		return $this->order_field;
	}

	public function getOrderDirection() {
		if ($this->order_direction === null) {
			$this->getOrderFromGETOrDefault();
		}
		return $this->order_direction;
	}

	protected function getOrderFromGETorDefault() {
		if (array_key_exists("_table_nav", $_GET)) {
			$tmp = explode(":", $_GET["_table_nav"]);

			$this->order_field = $this->normalizeOrderField($tmp[0]);
			$this->order_direction = $this->normalizeOrderDirection($tmp[1]);
		}
		else {
			$this->order_field = $this->default_field;
			$this->order_direction = $this->default_direction;
		}
	}

	public function getSQL() {
		$field = $this->getOrderField();
		$direction = $this->getOrderDirection();

		if ($field === null) {
			return "";
		}

		if (!array_key_exists($field, $this->mapping)) {
			return " ORDER BY ".catFilter::quoteDBId($field)." $direction";
		}

		$sql = " ORDER BY ";
		$fields = array();
		foreach ($this->mapping[$field] as $field) {
			$fields[] = catFilter::quoteDBId($field)." $direction";
		}
		return " ORDER BY ".implode(", ", $fields);
	}

	public function mapping($a_from, $a_to) {
		if (array_key_exists($a_from, $this->mapping)) {
			throw new Exception("catReportOrder::mapping: Mapping for $a_from already set.");
		}
		if (!is_array($a_to)) {
			$a_to = array($a_to);
		}

		$this->mapping[$a_from] = $a_to;
		return $this;
	}

	public function defaultOrder($a_field, $a_direction) {
		if ($this->default_field !== null) {
			throw new Exception("catReportOrder::default: Default order already set.");
		}

		$this->default_field = $this->normalizeOrderField($a_field);
		$this->default_direction = $this->normalizeOrderDirection($a_direction);

		return $this;
	}

	protected function normalizeOrderField($a_field) {
		if (!array_key_exists($a_field, $this->table->columns)) {
			throw new Exception("catReportOrder::normalizeOrderField: "
				."$a_field is no column in the table.");
		}

		return $a_field;
	}

	protected function normalizeOrderDirection($a_direction) {
		$a_direction = strtoupper($a_direction);

		if (!in_array($a_direction, array("ASC", "DESC"))) {
			throw new Exception("catReportOrder::normalizeOrderDirection: ".
				"$a_direction is no valid order.");
		}

		return $a_direction;
	}
}