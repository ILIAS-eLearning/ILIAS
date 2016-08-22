<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/HistorizingStorage/classes/class.ilHistorizingStorage.php';

/**
 * Class ilUserCourseStatusHistorizing
 * 
 * This class extends the ilHistorizingStorage class for the user-course-status historizing.
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 */
class ilUserCourseStatusHistorizing extends ilHistorizingStorage
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
		return 'hist_usercoursestatus';
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
			'credit_points'			=> 'integer',
			'bill_id'				=> 'text',
			'booking_status'		=> 'text',
			'participation_status'	=> 'text',
			'okz'					=> 'text',
			'certificate_hash'		=> 'text',
			'certificate_version'	=> 'integer',
			'certificate_filename'	=> 'text',
			'begin_date'			=> 'date',
			'end_date'				=> 'date',
			'overnights'			=> 'integer',
			'function'				=> 'text'
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
			'usr_id'	 =>	'integer',
			'crs_id'	 =>	'integer'
		);
	}

	/**
	 * Updates a historized case with the given data to be written.
	 *
	 * @static
	 *
	 * @param      $a_case_id                     Array            Array holding the case-id of the new record. See example.
	 * @param      $a_data                        Array            Array holding the delta to be applied. See example.
	 * @param null $a_record_creator              Integer|Null    User id of the creator, set to current user if null.
	 * @param null $a_creation_timestamp          Integer|Null    Unix-timestamp of creation, set to now if null.
	 * @param bool $mass_modification_allowed     Boolean|False    In order to make mass-updates, set this true.
	 *
	 * @throws Exception|ilHistorizingException
	 */
	public static function updateHistorizedData(
		$a_case_id,
		$a_data,
		$a_record_creator = null,
		$a_creation_timestamp = null,
		$mass_modification_allowed = false
	)
	{
		// Since #989 we do updates with partial case ids to have correct
		// begin- and endtimes. Then $a_case_id only contains a crs_id and
		// no usr_id.
		if (count($a_case_id) == 2) {
			try {
				$current = parent::getCurrentRecordByCase($a_case_id);
			}
			catch (ilHistorizingException $e) {
				$current = array();
			}

			$a_data = self::maybeStoreCertificateAndUpdateNewEntry($a_data, $current, $a_case_id);
		}

		parent::updateHistorizedData( $a_case_id,
									  $a_data,
									  $a_record_creator,
									  $a_creation_timestamp,
									  $mass_modification_allowed
		);
	}

	protected static function maybeStoreCertificateAndUpdateNewEntry($new, $current, $case_id) {
		if(isset($new['certificate'])) {
			$certificate_hash = md5($new['certificate']);
			if($certificate_hash === $current['certificate_hash']) {
				$new['certificate'] = null;
				return $new;
			}
			$new['certificate_hash'] = $certificate_hash;
			$new['certificate_version'] = (int)$current['certificate_version']+1;
			$new['certificate_filename'] = self::createCertificateFilename($case_id, $new['certificate_version']);
		} else {
			return $new;
		}
		require_once 'Services/UserCourseStatusHistorizing/classes/class.ilCertificateStorage.php';
		$storage = new ilCertificateStorage();
		$storage->storeCertificate($new['certificate'], $new['certificate_filename']);
		$new['certificate'] = null;
		return $new;
	}

	protected static function createCertificateFilename($case_id, $version) {
		return $case_id['usr_id'].'_'.$case_id['crs_id'].'_'.$version.'.pdf';
	}
}