<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/HistorizingStorage/classes/class.ilHistorizingStorage.php';

/**
 * Class ilTEPHistorizing
 *
 * This class extends the ilHistorizingStorage class for the tep historizing.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 */
class ilTEPHistorizing extends ilHistorizingStorage
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
		return 'hist_tep';
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
			'context_id'			=> 'integer',
			'title'					=> 'text',
			'subtitle'				=> 'text',
			'description'			=> 'text',
			'location'				=> 'text',
			'fullday'				=> 'integer',
			'begin_date'			=> 'date',
			'end_date'				=> 'date',
			'individual_days'		=> 'integer',
			'category'				=> 'text',
			'deleted'				=> 'integer',
			// gev-patch start
			'orgu_title'			=> 'text',
			'orgu_id'				=> 'integer'
			// gev-patch end
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
			'user_id'				=>	'integer',
			'cal_entry_id'			=>	'integer',
			'cal_derived_entry_id'	=>	'integer'
		);
	}
	
	
	
	// gev-patch start
	/**
	 * Overwritten to write individual days to hist_tep_individ_days.
	 */
	public static function updateHistorizedData(
		$a_case_id,
		$a_data,
		$a_record_creator = null,
		$a_creation_timestamp = null,
		$mass_modification_allowed = false
	)
	{
		if (!$a_record_creator)
		{
			/** @var $ilUser ilObjUser */
			global $ilUser;
			$a_record_creator = $ilUser->getId();
		}

		if (!$a_creation_timestamp)
		{
			$a_creation_timestamp = time();
		}

		$cases = self::getCaseIdsByPartialCase($a_case_id);
		if (count($cases) > 1 && $mass_modification_allowed == false)
		{
			throw new Exception( 'Illegal call: Case-Id '.implode(", ", $a_case_id).' does not point to a unique record in '
								.static::getHistorizedTableName().'.');
		}

		if ( count($cases) == 0 && $mass_modification_allowed == false)
		{
			// gev-patch start
			if (array_key_exists("individual_days", $a_data)) {
				$a_data["individual_days"] 
						= static::updateIndividualDays(null, $a_data["individual_days"]);
			}
			// gev-patch end
			self::validateRecordData($a_data);
			return self::createRecord($a_case_id, $a_data, '1', $a_record_creator, $a_creation_timestamp);
		}

		foreach ($cases as $case)
		{
			$current_data = static::getCurrentRecordByCase($case);
			
			// gev-patch start
			if (array_key_exists("individual_days", $a_data)) {
				$a_data["individual_days"] 
					= static::updateIndividualDays($current_data["individual_days"], $a_data["individual_days"]);
			}
			
			if (!static::containsChanges($current_data, $a_data)) {
				continue;
			}
			$new_data = array_merge($current_data, $a_data);
			// gev-patch end

			$new_data[static::getVersionColumnName()] = $current_data[static::getVersionColumnName()]+1;
			self::validateRecordData($new_data);
			try {
				/** @var $ilDB ilDB */
				global $ilDB;
				self::historizeRecord($case);
				$ilDB->setUnfatal(true);
				self::createRecord($case, $new_data, $new_data[static::getVersionColumnName()],$a_record_creator, $a_creation_timestamp);
			}
			catch (ilHistorizingException $ex)
			{
				self::createRecord($case, $current_data, $current_data[static::getVersionColumnName()], $a_record_creator, $a_creation_timestamp);
				throw $ex;
			}
			$ilDB->setUnfatal(false);
		}
	}
	
	// This updates the table hist_tep_individ_days if the days stored with $a_days_id
	// differ from the days given in array $a_days. Returns the id of the entries in
	// hist_tep_individ_days in both cases.
	protected static function updateIndividualDays($a_days_id, $a_days) {
		global $ilDB;
		
		if ($a_days_id) {
			$res = $ilDB->query("SELECT day, start_time, end_time, weight FROM hist_tep_individ_days "
							   ." WHERE id = ".$ilDB->quote($a_days_id, "integer")
							   ." ORDER BY day ASC");
			$cur_days = array();
			while ($rec = $ilDB->fetchAssoc($res)) {
				$cur_days[] = $rec;
			}
			
			if ($cur_days == $a_days) {
				return $a_days_id;
			}
		}
		
		$next = $ilDB->nextID("hist_tep_individ_days");
		foreach ($a_days as $rec) {
			$res = $ilDB->manipulate("INSERT INTO hist_tep_individ_days "
									."            (id, day, start_time, end_time, weight)"
									." VALUES ( ".$ilDB->quote($next, "integer")
									."        , ".$ilDB->quote($rec["day"], "date")
									."        , ".$ilDB->quote($rec["start_time"], "text")
									."        , ".$ilDB->quote($rec["end_time"], "text")
									."        , ".$ilDB->quote($rec["weight"], "integer")
									."        )"
									);
		}
		
		return $next;
	}
	
	// gev-patch end
}
