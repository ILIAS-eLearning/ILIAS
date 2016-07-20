<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/HistorizingStorage/classes/class.ilHistorizingStorage.php';

/**
 * Class ilUserHistorizing
 */
class ilUserHistorizing extends ilHistorizingStorage
{

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
	protected function getHistorizedTableName()
	{
		return 'hist_user';
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
	protected function getVersionColumnName()
	{
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
	protected function getHistoricStateColumnName()
	{
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
	protected function getCreatorColumnName()
	{
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
	protected function getCreatedColumnName()
	{
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
	protected function getContentColumnsDefinition()
	{
		$definition =  array(
			'firstname'					=> 'text',
			'lastname'					=> 'text',
			'gender'					=> 'text',
			'birthday'					=> 'date',
			'org_unit'					=> 'text',
			'position_key'				=> 'text',
			'entry_date'				=> 'date',
			'exit_date'					=> 'date',
			'bwv_id'					=> 'text',
			'okz'						=> 'text',
			'begin_of_certification'	=> 'date',
			'deleted'					=> 'integer',

			'street'	 				=> 'text',
			'zipcode'					=> 'text',
			'city'						=> 'text',
			'phone_nr'					=> 'text',
			'mobile_phone_nr'			=> 'text',
			
			'email'						=> 'text',
			'wbd_agent_status'			=> 'text',
			'wbd_type'					=> 'text',
			'wbd_email'					=> 'text',
			
			'job_number'				=> 'text',
			'adp_number'				=> 'text',
			'position_key'				=> 'text',
			'org_unit_above1'			=> 'text',
			'org_unit_above2'			=> 'text',
			'is_vfs'					=> 'integer',
			'is_active'					=> 'integer',
			'exit_date_wbd'				=> 'date',
			'next_wbd_action'			=> 'text',
			'login'						=> 'text',
			'report_points_from'		=> 'text',


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
	protected function getRecordIdColumn()
	{
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
	protected function getCaseIdColumns()
	{
		return array(
			'user_id'	 =>	'integer'
		);
	}
}
