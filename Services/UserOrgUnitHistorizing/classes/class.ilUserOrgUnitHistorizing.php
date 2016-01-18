<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/HistorizingStorage/classes/class.ilHistorizingStorage.php';

/**
 * Class ilUserHistorizing
 */
class ilUserOrgUnitHistorizing extends ilHistorizingStorage {

	/**
	 * Returns the defined name of the table to be used for historizing.
	 *
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'generic_history'
	 *
	 *
	 * @return string Name of the table which contains the historized records.
	 */
	protected function getHistorizedTableName() {
		return 'hist_userorgu';
	}

	/**
	 * Returns the column name which holds the current records version.
	 *
	 * The column is required to be able to hold an integer: Integer,4, not null, default 1
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'version'
	 *
	 *
	 * @return string Name of the column which is used to track the records version.
	 */
	protected function getVersionColumnName() {
		return 'hist_version';
	}

	/**
	 * Returns the column name which holds the current records historic state.
	 *
	 * The column is required to be able to hold an integer representation of a boolean: Integer, 1, not null, default 0
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'is_historic'
	 *
	 *
	 * @return string Name of the column which holds the current records historic state.
	 */
	protected function getHistoricStateColumnName() {
		return 'hist_historic';
	}

	/**
	 * Returns the column name which holds the current records creator id.
	 *
	 * The column is required to be able to hold an integer reference to the creator of the record.
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'creator_fi'
	 *
	 *
	 * @return string Name of the column which holds the current records creator.
	 */
	protected function getCreatorColumnName() {
		return 'creator_user_id';
	}

	/**
	 * Returns the column name which holds the current records creation timestamp is integer.
	 *
	 * The column is required to be able to hold an integer unix-timestamp of the records creation.
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'created_ts'
	 *
	 *
	 * @return string Name of the column which holds the current records creator.
	 */
	protected function getCreatedColumnName() {
		return 'created_ts';
	}

	/**
	 * Defines the content columns for the historized records.
	 *
	 * The array holds a definition so the parent class can check for correct parameters and place proper escaping.
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example
	 *      array(
	 *          'participation_state' => 'string',
	 *          'course_ref' => 'integer',
	 *          'creator' => 'string',
	 *          'creation' => 'integer',
	 *          'changed' => 'integer'
	 *      )
	 *
	 *
	 * @return Array Array with field definitions in the format "fieldname" => "datatype".
	 */
	protected function getContentColumnsDefinition() {
		$definition =  array(
			't_in'				=> 'integer',
			'action'	 		=> 'integer',
			'orgu_title' 		=> 'text',
			'rol_title' 		=> 'text',
			'org_unit_above1' 	=> 'text',
			'org_unit_above2' 	=> 'text'		
		);

		return $definition;
	}

	/**
	 * Returns the column name which holds the current records unique record id.
	 *
	 * The column is required to be able to hold an integer records db-id. This field needs a sequence and
	 * gets populated by the sequence.
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'row_id'
	 *
	 *
	 * @return string Name of the column which holds the current records database id.
	 */
	protected function getRecordIdColumn() {
		return 'row_id';
	}

	/**
	 * Returns the column name which holds the current records case db-id.
	 *
	 * The column is required to be able to hold an integer. This integer gets populated during the initial setup of
	 * a case with the first versions record id. The first version of every case has case_id = row_id, subsequent
	 * rows differ from that.
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'case_id'
	 *
	 *
	 * @return array Name of the column which holds the current records case id.
	 */
	protected function getCaseIdColumns() {
		return array(
			'usr_id'	 =>	'integer',
			'orgu_id'    => 'integer',
			'rol_id'	 => 'integer'
		);
	}

	protected static function createRecord($case_id, $data, $version, $record_creator, $creation_timestamp) {
		if($data['action'] == -1 && $version == 1) {
			return;
		}
		if($data['action'] == 0 && $version == 1) {
			return;
		}
		return parent::createRecord($case_id, $data, $version, $record_creator, $creation_timestamp);
	}

	public static function containsChanges(&$a_current_data, &$a_new_data) {
		if((string)$a_current_data['action'] === (string)$a_new_data['action'] && (string)$a_current_data['action'] !== "0") {
			return false;
		}
		if($a_current_data['action'] === null && $a_new_data['action'] == -1) {
			return false;
		}
		if(($a_current_data['action'] === null || (string)$a_current_data['action'] === "-1")
			&& $a_new_data['action'] == 0) {
			return false;
		}
		return parent::containsChanges($a_current_data, $a_new_data);
	}


}