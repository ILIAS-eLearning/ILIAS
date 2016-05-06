<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/HistorizingStorage/classes/class.ilHistorizingStorage.php';

/**
 * Class ilCourseHistorizing
 */
class ilCourseHistorizing extends ilHistorizingStorage {

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
		return 'hist_course';
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
			'custom_id'				=> 'text',
			'title'					=> 'text',
			'template_title'		=> 'text',
			'type'					=> 'text',
			'topic_set'				=> 'integer',
			'begin_date'			=> 'date',
			'end_date'				=> 'date',
			'hours'					=> 'integer',
			'is_expert_course'		=> 'integer',
			'venue'					=> 'text',
			'provider'				=> 'text',
			'tutor'					=> 'text',
			'max_credit_points'		=> 'text',
			'fee'					=> 'float',
			'is_template'			=> 'text',
			'wbd_topic'				=> 'text',
			'edu_program'			=> 'text',
			'is_online'				=> 'integer',
			'dl_invitation'			=> 'integer',
			'dl_storno'				=> 'integer',
			'dl_booking'			=> 'integer',
			'dl_waitinglist'		=> 'integer',
			'dbv_hot_topic'			=> 'text',
			'virtual_classroom_type'=> 'text',
			'dct_type'				=> 'text',
			'template_obj_id'		=> 'integer',
			'is_cancelled'			=> 'text',
			'size_waitinglist'		=> 'integer',
			'waitinglist_active'	=> 'text',
			'max_participants'		=> 'integer',
			'min_participants'		=> 'integer',
			'accomodation'			=> 'text'
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
			'crs_id' => 'integer'
		);
	}

	/**
	 * Updates a historized case with the given data to be written.
	 * 
	 * Overwritten here in order to address the special handling of topic-sets.
	 *
	 * @see ilHistorizingStorage::updateHistorizedData
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
	) {
		// Deal with the topic-set first and update $a_data accordingly if necessary.
		/** @var ilDB $ilDB */
		global $ilDB;
		try {
			$current_record 		= parent::getCurrentRecordByCase( $a_case_id );
		} catch (Exception $ex)	{
			$current_record = array();
		}
		$current_topic_set_id 	= $current_record['topic_set'] != null ? $current_record['topic_set'] : 0;

		$query =
			'SELECT topic_title FROM hist_topicset2topic '.
			'		JOIN hist_topics ON hist_topicset2topic.topic_id = hist_topics.topic_id'.
			'		WHERE hist_topicset2topic.topic_set_id = ' . $ilDB->quote( $current_topic_set_id, 'integer' );

		$current_topics 	= array();
		$result 			= $ilDB->query( $query );

		/** @noinspection PhpAssignmentInConditionInspection */
		while ( $row = $ilDB->fetchAssoc( $result ) ) {
			$current_topics[] 		= $row['topic_title'];
		}
		asort($current_topics);
		$new_topics = $a_data['topic_set'];
		asort($new_topics);

		if(count($new_topics) === 0) {
			$a_data['topic_set'] = -1;
		} elseif ($current_topics == $new_topics) {
			$a_data['topic_set'] = $current_record['topic_set'];
		}
		else {
			$next_id = $ilDB->nextId( 'hist_topicset2topic' );
			$a_data['topic_set'] = $next_id;
			self::storeNewTopicSet($a_data['topic_set'], $new_topics);
		}
		parent::updateHistorizedData($a_case_id, $a_data, $a_record_creator, $a_creation_timestamp, $mass_modification_allowed);
	}

	/**
	 * Stores a new topic-set.
	 * 
	 * @param integer	$topic_set_id	Topic set id for the new topic set.
	 * @param string[] 	$new_topics		List of strings containing new topics.
	 * 
	 * @return void
	 */
	protected static function storeNewTopicSet( $topic_set_id, $new_topics ) {
		$existing_topics = self::getExistingTopicsFromDatabase();
		$topic_ids = self::getTopicIdsFromTopicList( $new_topics, $existing_topics );
		self::writeNewTopicSet( $topic_set_id, $topic_ids );
		return;
	}

	/**
	 * Reads all existing topics from the database.
	 * 
	 * @return string[]
	 */
	protected static function getExistingTopicsFromDatabase() {
		/** @var ilDB $ilDB */
		global $ilDB;

		$query = 'SELECT topic_id, topic_title FROM hist_topics';
		$result = $ilDB->query( $query );
		$existing_topics = array();

		/** @noinspection PhpAssignmentInConditionInspection */
		while ( $row = $ilDB->fetchAssoc( $result ) )
		{
			$existing_topics[$row['topic_id']] = $row['topic_title'];
		}
		return $existing_topics;
	}

	/**
	 * Inserts a new topic into the database, returning its ID.
	 * 
	 * @param string $new_topic	New topic name.
	 *
	 * @return integer
	 */
	protected static function writeTopicToDatabase( $new_topic ) {
		/** @var ilDB $ilDB */
		global $ilDB;

		$id = $ilDB->nextId( 'hist_topics' );
		$ilDB->insert(
			'hist_topics',
			array(
				'row_id'      => array( 'integer', $id ),
				'topic_id'    => array( 'integer', $id ),
				'topic_title' => array( 'text', $new_topic )
			)
		);
		return $id;
	}

	/**
	 * Writes a new topic set to the database.
	 *
	 * @param integer	$topic_set_id	New topic set id.
	 * @param integer[]	$topic_ids		List of topic-ids.
	 */
	protected static function writeNewTopicSet( $topic_set_id, $topic_ids) {
		/** @var ilDB $ilDB */
		global $ilDB;

		foreach ( $topic_ids as $topic_id )
		{
			$ilDB->insert(
				'hist_topicset2topic',
				array(
					'row_id'       => array( 'integer', $ilDB->nextId( 'hist_topicset2topic' ) ),
					'topic_set_id' => array( 'integer', $topic_set_id ),
					'topic_id'     => array( 'integer', $topic_id )
				)
			);
		}
	}

	/**
	 * Gets a list of topic ids from a list of topics, creating missing ones.
	 *
	 * @param string[]	$new_topics			List of new topics.
	 * @param array		$existing_topics	Id-topic-list of existing topics.
	 *
	 * @return integer[]
	 */
	protected static function getTopicIdsFromTopicList( $new_topics, $existing_topics) {
		$topic_ids = array();
		foreach ( $new_topics as  $new_topic ) {

			$exists = array_search( $new_topic, $existing_topics );
			if ( $exists ) {
				$topic_ids[] = $exists;
			}
			else {
				$id          = self::writeTopicToDatabase( $new_topic );
				$topic_ids[] = $id;
			}
		}
		return $topic_ids;
	}
}