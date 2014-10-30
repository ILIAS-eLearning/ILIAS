<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Modules/DataCollection/classes/class.ilDataCollectionRecordField.php');
require_once('class.ilDclStack.php');
require_once('class.ilDclExpressionParser.php');
require_once('class.ilDclTokenizer.php');

/**
 * Class ilDataCollectionField
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilDataCollectionFormulaField extends ilDataCollectionRecordField {

	/**
	 * @var string
	 */
	protected $expression = '';
	/**
	 * @var array
	 */
	protected $field_properties = array();
	/**
	 * @var string
	 */
	protected $parsed_value = '';


	/**
	 * @param ilDataCollectionRecord $record
	 * @param ilDataCollectionField  $field
	 */
	public function __construct(ilDataCollectionRecord $record, ilDataCollectionField $field) {
		parent::__construct($record, $field);
		$this->field_properties = $field->getProperties();
		$this->expression = $this->field_properties[ilDataCollectionField::PROPERTYID_FORMULA_EXPRESSION];
	}


	/**
	 * Do nothing, value is runtime only and not stored in DB
	 */
	protected function loadValue() {
		return NULL;
	}


	/**
	 * @param $value
	 */
	public function setValue($value) {
		unset($value);
	}


	/**
	 * Do nothing, value is runtime only and not stored in DB
	 */
	public function doUpdate() {
		return NULL;
	}


	/**
	 * Do nothing, value is runtime only and not stored in DB
	 */
	public function doRead() {
		return NULL;
	}


	/**
	 * Do nothing, value is runtime only and not stored in DB
	 */
	public function delete() {
		return NULL;
	}


	/**
	 *
	 * @return mixed|string
	 */
	public function getFormInput() {
		return $this->parse();
	}


	/**
	 * @return string
	 */
	public function getHTML() {
		return $this->parse();
	}


	/**
	 * @return string
	 */
	public function getExportValue() {
		return $this->parse();
	}


	/**
	 * @return string
	 */
	public function getValue() {
		return $this->parse();
	}


	/**
	 * Parse expression
	 *
	 * @return string
	 */
	protected function parse() {
		if (!$this->parsed_value AND $this->expression) {
			$parser = new ilDclExpressionParser($this->expression, $this->record, $this->field);
			try {
				$this->parsed_value = $parser->parse();
			} catch (ilException $e) {
				return $this->lng->txt('dcl_error_parsing_expression') . ' (' . $e->getMessage() . ')';
			}
		}

		return $this->parsed_value;
	}
}

?>