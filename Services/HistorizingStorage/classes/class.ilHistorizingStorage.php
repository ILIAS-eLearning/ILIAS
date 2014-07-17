<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract base class for historizing storage tables.
 *
 * In order to use this class in your service:
 *
 * - Derive from this class and implement the abstract methods defined here.
 * - Create a table using dbupdate that contains all columns according to the requirements
 *   and your use-case.
 *
 * To use the class, please refer to the documentation and samples provided here:
 * 	 @see createNewHistorizedCase
 *   @see updateHistorizedData
 *   @see caseExists
 *
 * Querying the table is simple:
 *  Select the data you need - be it part of the case-id or the payload and put a where clause equalling to one
 *  on the column specified in @see getHistoricStateColumnName
 *
 * After careful consideration, though a more sophisticated object graph would be possible and could have helped to
 * increase the readability of this service, it was decided to use simpler data structures to enhance the general
 * execution speed.
 *
 * @author	Maximilian Becker <mbecker@databay.de>
 * @version	$Id$
 */
abstract class ilHistorizingStorage
{

	#region Abstract definitions

	/**
	 * Returns the defined name of the table to be used for historizing.
	 *
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'generic_history'
	 *
	 * @abstract
	 *
	 * @return string Name of the table which contains the historized records.
	 */
	protected abstract function getHistorizedTableName();

	/**
	 * Returns the column name which holds the current records version.
	 *
	 * The column is required to be able to hold an integer: Integer,4, not null, default 1
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'version'
	 *
	 * @abstract
	 *
	 * @return string Name of the column which is used to track the records version.
	 */
	protected abstract function getVersionColumnName();

	/**
	 * Returns the column name which holds the current records historic state.
	 *
	 * The column is required to be able to hold an integer representation of a boolean: Integer, 1, not null, default 0
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'is_historic'
	 *
	 * @abstract
	 *
	 * @return string Name of the column which holds the current records historic state.
	 */
	protected abstract function getHistoricStateColumnName();

	/**
	 * Returns the column name which holds the current records creator id.
	 *
	 * The column is required to be able to hold an integer reference to the creator of the record.
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'creator_fi'
	 *
	 * @abstract
	 *
	 * @return string Name of the column which holds the current records creator.
	 */
	protected abstract function getCreatorColumnName();

	/**
	 * Returns the column name which holds the current records creation timestamp is integer.
	 *
	 * The column is required to be able to hold an integer unix-timestamp of the records creation.
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'created_ts'
	 *
	 * @abstract
	 *
	 * @return string Name of the column which holds the current records creator.
	 */
	protected abstract function getCreatedColumnName();

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
	 * @abstract
	 *
	 * @return Array Array with field definitions in the format "fieldname" => "datatype".
	 */
	protected abstract function getContentColumnsDefinition();

	/**
	 * Returns the column name which holds the current records unique record id.
	 *
	 * The column is required to be able to hold an integer records db-id. This field needs a sequence and
	 * gets populated by the sequence.
	 * Please note that database abstraction layer constraints are not checked.
	 *
	 * @example 'row_id'
	 *
	 * @abstract
	 *
	 * @return string Name of the column which holds the current records database id.
	 */
	protected abstract function getRecordIdColumn();

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
	 * @abstract
	 *
	 * @return array Name of the column which holds the current records case id.
	 */
	protected abstract function getCaseIdColumns();

	#endregion

	#region Prepared statement handling

	/** @var $prepared_statements array Array containing for each statement 'id' => 'statement' */
	private static $prepared_statements = array();

	/**
	 * Retrieves a prepared statement based on an sql-string by retrieval or creation.
	 *
	 * @static
	 *
	 * @param 	string 	$a_sql_string 	String of the sql-statement which is needed.
	 * @param 	array 	$a_data_types 	Datatypes for the sql-statements. 'field_name' => 'datatype'
	 *
	 * @return mixed
	 */
	private static function getPreparedStatementBySqlString($a_sql_string, array $a_data_types)
	{
		/** @var $ilDB ilDB */
		global $ilDB;

		$id = md5($a_sql_string);
		if (array_key_exists($id, self::$prepared_statements))
		{
			return self::$prepared_statements[$id];
		}
		else
		{
			if(strpos(strtolower(trim($a_sql_string)), 'select') == 0)
			{
				$statement = $ilDB->prepare(
								  $a_sql_string,
								  $a_data_types
				);
			}
			else
			{
				$statement = $ilDB->prepareManip(
								  $a_sql_string,
								  $a_data_types
				);
			}

			self::$prepared_statements[$id] = $statement;
			return self::$prepared_statements[$id];
		}
	}

	#endregion

	#region Public API

	/**
	 * Creates a new historized record.
	 *
	 * This method inserts a new record representing a new case for which data is to be historized.
	 *
	 * @example
	 *  $a_case_id = array(
	 * 		'usr_id'  => 6,
	 * 		'crs_ref' => 123
	 *  );
	 *
	 *  $a_data = array(
	 *      'lastname'          => 'Mueller',
	 *      'firstname'         => 'Anne',
	 * 		'learning_progress' => 'not_attempted'
	 *  );
	 *  createRecord($a_case_id, $a_data);
	 *
	 * @example
	 *  global $ilUser;
	 *
	 *  $a_case_id = array(
	 * 		'usr_id'  => 6,
	 * 		'crs_ref' => 123
	 *  );
	 *
	 *  $a_data = array(
	 *      'lastname'          => 'Mueller',
	 *      'firstname' 	    => 'Anne',
	 * 		'learning_progress' => 'not attempted'
	 *  );
	 *  createRecord($a_case_id, $a_data, $ilUser->getId(), now()-1 );
	 *
	 * @static
	 *
	 * @param $a_case_id			Array 			Array holding the case-id of the new record. See example.
	 * @param $a_data 				Array 			Array holding the content payload of the new record. See example.
	 * @param $a_record_creator		Integer|Null 	User id of the creator, set to current user if null.
	 * @param $a_creation_timestamp	Integer|Null	Unix-timestamp of creation, set to now if null.
	 *
	 * @return void
	 */
	public static function createNewHistorizedCase($a_case_id, $a_data, $a_record_creator = null, $a_creation_timestamp = null)
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

		self::validateRecordData($a_data);
		self::historizeRecord($a_case_id); // Historizing eventual existing case-data for equal-identified case
		// according to Q#3951. Please everyone watch out for side effects of this.
		self::createRecord($a_case_id, $a_data, 1, $a_record_creator, $a_creation_timestamp);
		return;
	}

	/**
	 * Validates values according to column-definition empty field rules.
	 *
	 * @param array $a_record Reference to the associative record array.
	 */
	protected static function validateRecordData(array &$a_record)
	{
		foreach(static::getContentColumnsDefinition() as $id => $type)
		{
			if( !isset( $a_record[$id] ) || $a_record[$id] === null || $a_record[$id] === "" )
			{
				$value = null;
				switch($type)
				{
					case "date":
					case "text":
						$value = "-empty-";
						break;

					case "integer":
					case "float":
						$value = -1;
						break;

					default:
						trigger_error("unknown column definition type ".$type, E_USER_ERROR);
						break;
				}

				$a_record[$id] = $value;
			}
		}
	}

	/**
	 * Updates a historized case with the given data to be written.
	 *
	 * Please note: There is no checking, if the new data passed to the method differ from the current record,
	 * this is up to you!
	 *
	 * @example
	 *  $a_case_id = array(
	 *        'usr_id' => 6,
	 *        'crs_ref' => 123
	 *  );
	 *
	 *  $a_data = array(
	 *      'learning_progress'  => 'completed'
	 *  );
	 *  updateHistorizedTable($a_case_id, $a_data);
	 *  --
	 *  This will update the case to a new learning progress, preserving the data of the rest of the record. Only the delta
	 *  is applied.
	 *
	 * @example
	 *  Additionaly, this method allows changes to groups of cases, by passing a partial case-id, here's how:
	 *  $a_case_id = array(
	 *        'usr_id' => 6
	 *  );
	 *
	 *  $a_data = array(
	 *      'lastname'  => 'Mayer'
	 *  );
	 *  updateHistorizedTable($a_case_id, $a_data, $ilUser->getId(), now()-1, true);
	 *  --
	 *  This will identify all cases which are identified by the partial case-id - here the user-id -  and then apply
	 *  the delta - here the changed lastname - to all cases on file. The last parameter, which allows for mass-updates
	 *    is a security precaution.
	 *
	 * @static
	 *
	 * @param      $a_case_id                     Array            Array holding the case-id of the new record. See example.
	 * @param      $a_data                        Array            Array holding the delta to be applied. See example.
	 * @param null $a_record_creator              Integer|Null    User id of the creator, set to current user if null.
	 * @param null $a_creation_timestamp          Integer|Null    Unix-timestamp of creation, set to now if null.
	 * @param bool $mass_modification_allowed     Boolean|False    In order to make mass-updates, set this true.
	 *
	 * @throws Exception|ilException
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
			throw new Exception('Illegal call: Case-Id does not point to a unique record.');
		}

		if ( count($cases) == 0 && $mass_modification_allowed == false)
		{
			self::validateRecordData($a_data);
			return self::createRecord($a_case_id, $a_data, '1', $a_record_creator, $a_creation_timestamp);
		}

		foreach ($cases as $case)
		{
			$current_data = static::getCurrentRecordByCase($case);
			$new_data = array_merge($current_data, $a_data);
			if ($new_data == $current_data)
			{
				continue;
			}
			$new_data[static::getVersionColumnName()] = $current_data[static::getVersionColumnName()]+1;
			self::validateRecordData($new_data);
			try {
				/** @var $ilDB ilDB */
				global $ilDB;
				self::historizeRecord($case);
				$ilDB->setUnfatal(true);
				self::createRecord($case, $new_data, $new_data[static::getVersionColumnName()],$a_record_creator, $a_creation_timestamp);
			}
			catch (ilException $ex)
			{
				self::createRecord($case, $current_data, $current_data[static::getVersionColumnName()], $a_record_creator, $a_creation_timestamp);
				throw $ex;
			}
			$ilDB->setUnfatal(false);
		}
	}

	/**
	 * Checks, if a given case exists
	 *
	 * In cases, where there are multiple possibilities to start a historizing case or the creation and updating of
	 * historized cases takes place, this method helps to identify the necessary method to call in order to have
	 * the historizing safe.
	 *
	 * @example
	 * $a_case_id = array('cost_center' => 4711, 'year' => 2015);
	 * if ( $htable->caseExists($a_case_id) )
	 * {
	 * 		$htable->updateHistorizedData($a_case_id, array('budget' => 5000);
	 * } else {
	 * 		$htable->createNewHistorizedCase($a_case_id, array('budget' => 5000);
	 * }
	 * This example is trivial as the payload data is exactly one item and all items of the record are already known.
	 * In other cases, when the creation needs data, which would have to be retrieved from other places, e.g. by using
	 * expensive queries or the like, this may save you some cycles.
	 *
	 * @static
	 *
	 * @param $a_case_id Array Array holding the case-id of the new record. See example.
	 * @return boolean True, if a case exists.
	 */
	public static function caseExists($a_case_id)
	{
		try
		{
			self::getCurrentRecordByCase($a_case_id);
			$case_exists = true;
		}
		catch (ilException $ex)
		{
			$case_exists = false;
		}

		return $case_exists;
	}

	/**
	 * Edits a value across historized data.
	 *
	 * @param $a_id_column		string	Column name of the column, which is used to identify records that need a change.
	 * @param $a_id_value		string	Value of the id_column, which designates that the record needs a change.
	 * @param $a_content_column	string	Column name of the column, whiches content needs to be changed.
	 * @param $a_content_value	string	New value of the content column.
	 *
	 * @return void
	 */
	public static function editHistorizedData($a_id_column, $a_id_value, $a_content_column, $a_content_value)
	{
		$id_columns = static::getCaseIdColumns();
		$content_columns = static::getContentColumnsDefinition();
		$column_definitions = array_merge($id_columns, $content_columns);

		$content_column_data_type = $column_definitions[$a_content_column];
		$id_column_data_type = $column_definitions[$a_id_column];

		/** @var $ilDB ilDB */
		global $ilDB;

		// validate against definition - get rid of undefined columns
		$column_ids = array_keys(static::getContentColumnsDefinition());
		if(in_array($a_id_column, $column_ids) &&
			in_array($a_content_column, $column_ids))
		{
			$query = 'UPDATE ' . static::getHistorizedTableName() . ' SET ' . $a_content_column . ' = ' .
				$ilDB->quote($a_content_value, $content_column_data_type)
				. ' WHERE ' . $a_id_column . ' = ' . $ilDB->quote($a_id_value, $id_column_data_type);
			$ilDB->query($query);
		}
		return;
	}

	#endregion

	#region Record creation

	/**
	 * Creates a historized record.
	 *
	 * For detailed information related to this method, @see createNewHistorizedCase
	 * @static
	 *
	 * @param $case_id				Array 			Array holding the case-id of the new record.
	 * @param $data 				Array 			Array holding the content payload of the new record.
	 * @param $version				Integer			Version of the record to be written.
	 * @param $record_creator		Integer 		User id of the creator, set to current user if null.
	 * @param $creation_timestamp	Integer			Unix-timestamp of creation, set to now if null.
	 *
	 * @return void
	 */
	private static function createRecord($case_id, $data, $version, $record_creator, $creation_timestamp)
	{
		/** @var $ilDB ilDB */
		global $ilDB;
		$query 			= self::getInsertionQuery();
		$data_types  	= self::getInsertionDatatypes();
		$statement 		= self::getPreparedStatementBySqlString($query, $data_types);
		$values 		= self::getInsertionValues($case_id, $data, (int) $version, $record_creator, $creation_timestamp);

		$ilDB->execute($statement, $values);
		return;
	}

	/**
	 * Creates the SQL for the insert-queries.
	 *
	 * @static
	 * @return string SQL for the insert-queries.
	 */
	private static function getInsertionQuery()
	{
		$query = 'INSERT INTO ' . static::getHistorizedTableName();
		$query .= ' ( ' . static::getRecordIdColumn();

		foreach (static::getCaseIdColumns() as $case_id_element => $case_id_datatype)
		{
			$query .= ', ' . $case_id_element;
		}

		$query .= ', ' . static::getVersionColumnName() . ', ' . static::getHistoricStateColumnName();
		$query .= ', ' . static::getCreatorColumnName() . ', ' . static::getCreatedColumnName();

		foreach (static::getContentColumnsDefinition() as $content_element => $content_datatype)
		{
			$query .= ', ' . $content_element;
		}
		$query .= ' ) VALUES ( ';
		$query .= '?';

		foreach (static::getCaseIdColumns() as $case_id_element => $case_id_datatype)
		{
			$query .= ', ?';
		}

		$query .= ', ?, ?, ?, ?';

		foreach (static::getContentColumnsDefinition() as $content_element => $content_datatype)
		{
			$query .= ', ?';
		}

		$query .= ' )';
		return $query;
	}

	/**
	 * Creates an array with datatypes for use with the preparation of sql prepared statements.
	 *
	 * @static
	 * @return array Array of datatypes. ( array('type','type','type') )
	 */
	private static function getInsertionDatatypes()
	{
		$datatypes = array();
		$datatypes[] = 'integer';

		foreach (static::getCaseIdColumns() as $case_id_element => $case_id_datatype)
		{
			$datatypes[] = $case_id_datatype;
		}

		$datatypes[] = 'integer';
		$datatypes[] = 'integer';
		$datatypes[] = 'integer';
		$datatypes[] = 'integer';

		foreach (static::getContentColumnsDefinition() as $content_element => $content_datatype)
		{
			$datatypes[] = $content_datatype;
		}

		return $datatypes;
	}

	/**
	 * Recomposes the given data in correct ordering to match the generated prepared statement.
	 *
	 * @static
	 *
	 * @param $a_case_id			Array 			Array holding the case-id of the new record.
	 * @param $a_data 				Array 			Array holding the content payload of the new record.
	 * @param $version				Integer			Version of the record to be written.
	 * @param $record_creator		Integer 		User id of the creator, set to current user if null.
	 * @param $creation_timestamp	Integer			Unix-timestamp of creation, set to now if null.
	 *
	 * @return array Array of values. ( array('value','value','value') )
	 */
	private static function getInsertionValues($a_case_id, $a_data, $version, $record_creator, $creation_timestamp)
	{
		$values = array();

		/** @var $ilDB ilDB */
		global $ilDB;
		$values[] = $ilDB->nextId(static::getHistorizedTableName());

		foreach (static::getCaseIdColumns() as $case_id_element => $case_id_datatype)
		{
			$values[] = $a_case_id[$case_id_element];
		}

		$values[] = $version;
		$values[] = 0; // Inserted records are always not historic!
		$values[] = $record_creator;
		$values[] = $creation_timestamp;

		foreach (static::getContentColumnsDefinition() as $content_element => $content_datatype)
		{
			$values[] = $a_data[$content_element];
		}

		return $values;
	}

	#endregion

	#region Record historizing

	/**
	 * Method to historize existing records of the case with the given case-id.
	 *
	 * Please note: There is a temporal coupling in the update process. Once this method is called, there is no
	 * quick way to get the current record for a case. This can only be achieved by getting all records for a case
	 * and get the highest version number.
	 * During the update process, the current record is fetched, records are historized and afterwards, the data-delta
	 * is applied and saved as a new current record.
	 *
	 * @static
	 *
	 * @param $a_case_id Array Array holding the case-id. ( array('field' => 'value', 'field' => 'value') )
	 */
	private static function historizeRecord($a_case_id)
	{
		$query 		= self::getHistorizingQuery();
		$datatypes 	= self::getHistorizingDatatypes();
		$values 	= self::getHistorizingValues($a_case_id);
		$statement 	= self::getPreparedStatementBySqlString($query, $datatypes);

		/** @var $ilDB ilDB */
		global $ilDB;
		$ilDB->execute($statement, $values);
	}

	/**
	 * Creates the SQL for the historizing-queries.
	 *
	 * @static
	 *
	 * @return string SQL for the historizing-queries.
	 */
	private static function getHistorizingQuery()
	{
		$query  = 'UPDATE ' . static::getHistorizedTableName() . ' ';
		$query .= 'SET ' . static::getHistoricStateColumnName() . ' =  1 ';
		$query .= 'WHERE';
		$i = 0;
		foreach ( static::getCaseIdColumns() as $field_name => $field_datatype )
		{
			$i++;
			$query .= ' '.$field_name . ' = ?';
			if (count(static::getCaseIdColumns()) != $i)
			{
				$query .= ' AND';
			}
		}
		return $query;
	}

	/**
	 * Creates an array with datatypes for use with the preparation of sql prepared statements.
	 *
	 * @static
	 *
	 * @return array Array of datatypes. ( array('type','type','type') )
	 */
	private static function getHistorizingDatatypes()
	{
		$datatypes = array();
		$i = 0;
		foreach ( static::getCaseIdColumns() as $field_name => $field_datatype )
		{
			$i++;
			$datatypes[] = $field_datatype;
		}
		return $datatypes;
	}

	/**
	 * Recomposes the given case-id in correct ordering to match the generated prepared statement.
	 *
	 * @static
	 *
	 * @param Array $a_case_id Array holding the case-id of the new record.
	 *
	 * @return array Array of values. ( array('value','value','value') )
	 */
	private static function getHistorizingValues($a_case_id)
	{
		$values = array();
		$i = 0;
		foreach ( static::getCaseIdColumns() as $field_name => $field_datatype )
		{
			$i++;
			$values[] = $a_case_id[$field_name];
		}
		return $values;
	}

	#endregion

	#region CasesByPartialCase

	/**
	 * Retrieves the cases, that share the - eventually partly - given case-id.
	 *
	 * @static
	 *
	 * @param Array $a_case_id Array holding an eventually partly given case-id.
	 *
	 * @return array Array of completed case-ids.
	 */
	protected static function getCaseIdsByPartialCase($a_case_id)
	{
		/** @var $ilDB ilDB */
		global $ilDB;
		$query 		= self::getCasesByPartialCaseQuery($a_case_id);
		$datatypes	= self::getCasesByPartialCaseDatatypes($a_case_id);
		$values		= self::getCasesByPartialCaseValues($a_case_id);
		$statement  = self::getPreparedStatementBySqlString($query, $datatypes);

		$result = $ilDB->execute($statement, $values);
		$cases = array();
		/** @noinspection PhpAssignmentInConditionInspection */
		while($row = $ilDB->fetchAssoc($result))
		{
			$case_id = array();
			foreach(static::getCaseIdColumns() as $column_name => $column_datatype)
			{
				$case_id[$column_name] = $row[$column_name];
			}
			$cases[] = $case_id;
		}
		return $cases;
	}

	/**
	 * Creates and returns the query to retrieve cases with the given - eventually partly - case-id.
	 *
	 * @static
	 *
	 * @param $a_case_id Array Array holding an eventually partly given case-id.
	 *
	 * @return string Sql query to retrieve cases with the given - eventually partly - case-id.
	 */
	private static function getCasesByPartialCaseQuery($a_case_id)
	{
		$query  = 'SELECT';
		$i = 0;
		foreach ( static::getCaseIdColumns() as $column_name => $column_datatype)
		{
			$i++;
			$query .= ' ' . $column_name;
			if (count(static::getCaseIdColumns()) != $i)
			{
				$query .= ',';
			}
		}

		$query .= ' FROM ' . static::getHistorizedTableName();
		$query .= ' WHERE';
		$i = 0;
		foreach ( $a_case_id as $field_name => $field_value )
		{
			$i++;
			$query .= ' '.$field_name . ' = ?';
			if (count($a_case_id) != $i)
			{
				$query .= ' AND';
			}
		}

		$query .= ' AND ' . static::getHistoricStateColumnName() . ' = 0';
		return $query;
	}

	/**
	 * Returns an array with datatypes for use with the preparation of sql prepared statements.
	 *
	 * @static
	 *
	 * @param $a_case_id Array Array holding an eventually partly given case-id.
	 *
	 * @return array Array of datatypes. ( array('type','type','type') )
	 */
	private static function getCasesByPartialCaseDatatypes($a_case_id)
	{
		$datatypes = array();
		$case_id_definition = static::getCaseIdColumns();
		foreach ( $a_case_id as $field_name => $field_value )
		{
			$datatypes[] = $case_id_definition[$field_name];
		}
		return $datatypes;
	}

	/**
	 * Returns the values ordered to match the prepared statement to retrieve cases by case-id.
	 *
	 * @static
	 *
	 * @param $a_case_id Array Array holding an eventually partly given case-id.
	 *
	 * @return array Array of values ( array('value','value','value' )
	 */
	private static function getCasesByPartialCaseValues($a_case_id)
	{
		$values = array();
		foreach ( $a_case_id as $field_name => $field_value )
		{
			$values[] = $a_case_id[$field_name];
		}
		return $values;
	}

	#endregion

	#region CurrentRecordByCase

	/**
	 * Returns an array holding the full current record for a given case.
	 *
	 * Please note: The full record contains all columns of the record, not only the data-payload.
	 *
	 * @static
	 *
	 * @param $a_case_id Array Array holding an eventually partly given case-id.
	 *
	 * @throws ilException Thrown if no case is found.
	 *
	 * @return array Array holding the full record. ( array('field' => 'value', 'field' => 'value') )
	 */
	protected static function getCurrentRecordByCase($a_case_id)
	{
		/** @var $ilDB ilDB */
		global $ilDB;
		$query 		= self::getCurrentRecordByCaseQuery();
		$datatypes 	= self::getCurrentRecordByCaseDatatypes();
		$values 	= self::getCurrentRecordByCaseValues($a_case_id);
		$statement	= self::getPreparedStatementBySqlString($query, $datatypes);
		$result = $ilDB->execute($statement, $values);
		if ($ilDB->numRows($result) == 0)
		{
			require_once './Services/Exceptions/classes/class.ilException.php';
			throw new ilException('No case.');
		}
		$row = $ilDB->fetchAssoc($result);
		return $row;
	}

	/**
	 * Creates and returns the query to retrieve records of cases with a given case-id.
	 *
	 * @static
	 *
	 * @return string Sql query to retrieve current records of cases with a given case-id.
	 */
	private static function getCurrentRecordByCaseQuery()
	{
		$query  = 'SELECT';
		$i = 0;
		foreach ( static::getCaseIdColumns() as $column_name => $column_datatype)
		{
			$i++;
			$query .= ' ' . $column_name;
			if (count(static::getCaseIdColumns()) != $i)
			{
				$query .= ',';
			}
		}

		$query .= ',' . static::getHistoricStateColumnName() . ',' . static::getVersionColumnName();

		$query .= ',';
		$i = 0;
		foreach ( static::getContentColumnsDefinition() as $column_name => $column_datatype)
		{
			$i++;
			$query .= ' ' . $column_name;
			if (count(static::getContentColumnsDefinition()) != $i)
			{
				$query .= ',';
			}
		}

		$query .= ' FROM ' . static::getHistorizedTableName();
		$query .= ' WHERE';
		$i = 0;
		foreach ( static::getCaseIdColumns() as $field_name => $field_datatype )
		{
			$i++;
			$query .= ' '.$field_name . ' = ?';
			if ( count( static::getCaseIdColumns() ) != $i )
			{
				$query .= ' AND';
			}
		}

		$query .= ' AND ' . static::getHistoricStateColumnName() . ' = 0';
		return $query;
	}

	/**
	 * Returns an array with datatypes for use with the preparation of sql prepared statements.
	 *
	 * @static
	 *
	 * @return array Array of datatypes. ( array('type','type','type') )
	 */
	private static function getCurrentRecordByCaseDatatypes()
	{
		$datatypes = array();
		foreach ( static::getCaseIdColumns() as $field_name => $field_datatype )
		{
			$datatypes[] = $field_datatype;
		}
		return $datatypes;
	}

	/**
	 * Returns the values ordered to match the prepared statement to retrieve current records by case-id.
	 *
	 * @static
	 *
	 * @param $a_case_id Array Array holding a case-id.
	 *
	 * @return array Array of values ( array('value','value','value' )
	 */
	private static function getCurrentRecordByCaseValues($a_case_id)
	{
		$values = array();
		foreach ( $a_case_id as $field_name => $field_value )
		{
			$values[] = $a_case_id[$field_name];
		}
		return $values;
	}

	#endregion

}