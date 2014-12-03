<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTEPHistorizingAppEventHandler
 *
 * This class receives and handles events for the tep historizing.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 */
class ilTEPHistorizingAppEventListener
{
	/** @var  ilTEPHistorizing $ilTEPHistorizing */
	protected static $ilTEPHistorizing;

	/**
	 * Handles raised events for ilUserHistorizing.
	 * 
	 * This method initializes the class, dispatches to helper methods and triggers historizing.
	 *
	 * @static
	 * 
	 * @param	string	$a_component	Component which has thrown the event to be handled.
	 * @param	string	$a_event		Name of the event
	 * @param 	mixed	$a_parameter	Parameters for the event
	 */
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		/** @var ilTEPEntry $tep_entry */
		$tep_entry = $a_parameter['entry'];
		self::initEventHandler();

		// Historize Base-Entry
		self::$ilTEPHistorizing->updateHistorizedData(
							   self::getCaseId($a_event, $tep_entry),
							   self::getStateData($a_event, $tep_entry),
							   self::getRecordCreator($a_event, $tep_entry),
							   self::getCreationTimestamp($a_event, $tep_entry),
							   false // Not a mass-action
		);

		if($a_event == 'delete')
		{
			self::$ilTEPHistorizing->updateHistorizedData(
								   array(
									   'user_id' => $derived_user, 
									   'cal_entry_id' => $tep_entry->getEntryId(),
									   // REVIEW: this is not the id of the derived entry, but the
									   // tep entry it self. Use $derived_entry_id as described
									   // above. 
									   'cal_derived_entry_id' => $tep_entry->getEntryId()),
								   self::getStateData($a_event, $tep_entry),
								   self::getRecordCreator($a_event, $tep_entry),
								   self::getCreationTimestamp($a_event, $tep_entry),
								   false // Not a mass-action
			);
			self::persistDerivedDeleted( $a_event, $tep_entry );
		} else {
			self::persistDerivedEntries( $a_event, $tep_entry );
		}

	}

	/**
	 * Initializes the static members of the class.
	 * 
	 * @static
	 */
	protected static function initEventHandler()
	{
		if (!self::$ilTEPHistorizing)
		{
			require_once './Services/TEPHistorizing/classes/class.ilTEPHistorizing.php';
			self::$ilTEPHistorizing = new ilTEPHistorizing();
		}
	}

	/**
	 * Returns the correct case ID for the record affected by the event raised.
	 *
	 * @static
	 * 
	 * @param 	string 		$event 		Name of the event
	 * @param 	ilTEPEntry 	$parameter 	Parameters for the event
	 * 
	 * @return 	array 	Array consisting of the case id. (@see ilUserHistorizing, ilHistorizingStorage)
	 */
	protected static function getCaseId($event, ilTEPEntry $parameter)
	{
		return array(
			'user_id'				=> $parameter->getOwnerId(),
			'cal_entry_id'			=> $parameter->getEntryId(),
			// REVIEW: since this is only use for the owner of the master entry
			// set the derived entry id to null.
			'cal_derived_entry_id'	=> $parameter->getDerivedUsers(),
		);
	}

	/**
	 * Returns the full state data for the record affected by the event raised.
	 *
	 * @static
	 * 
	 * @param 	string 		$event 		Name of the event
	 * @param 	ilTEPEntry 	$parameter 	Parameters for the event
	 * 
	 * @return 	array 	Array consisting of the cases data state. (@see ilUserHistorizing, ilHistorizingStorage)
	 */
	protected static function getStateData($event, ilTEPEntry $parameter)
	{
		$data_payload = array(
			'context_id'			=> $parameter->getContextId(),
			'title'					=> $parameter->getTitle(),
			'subtitle'				=> $parameter->getSubtitle(),
			'description'			=> $parameter->getDescription(),
			'location'				=> $parameter->getLocation(),
			'fullday'				=> $parameter->isFullday(),
			'begin_date'			=> $parameter->getStart(),
			'end_date'				=> $parameter->getEnd(),
			'type'					=> $parameter->getType(),
			'deleted'				=> ($event == 'delete' ? 1 : 0)
		);

		return $data_payload;
	}

	/**
	 * Returns the correct record creator for the new record to be created.
	 *
	 * @static
	 * 
	 * @param	string		$event		Name of the event
	 * @param 	ilTEPEntry	$parameter	Parameters for the event
	 * 
	 * @return 	string 	Record creator identifier. (@see ilUserHistorizing, ilHistorizingStorage)
	 */
	protected static function getRecordCreator($event, $parameter)
	{
		/** @var ilObjUser $ilUser */
		global $ilUser;
		return $ilUser->getId();
	}

	/**
	 * Returns the correct creation timestamp for the new record to be created.
	 *
	 * @static
	 * 
	 * @param	string		$event		Name of the event
	 * @param 	ilTEPEntry	$parameter	Parameters for the event
	 * 
	 * @return 	string 	UNIX-Timestamp. (@see ilUserHistorizing, ilHistorizingStorage)
	 */
	protected static function getCreationTimestamp($event, $parameter)
	{
		return time();
	}

	/**
	 * Persists derived entries on "non-deletion".
	 * 
	 * @param string 		$a_event
	 * @param ilTEPEntry 	$tep_entry
	 */
	private static function persistDerivedEntries($a_event, ilTEPEntry $tep_entry)
	{
		$derived_users = ilCalDerivedEntry::getUserIdsByMasterEntryIds( array( $tep_entry->getEntryId() ) );
		$derived_users = $derived_users[$tep_entry->getEntryId()];

		foreach ($derived_users as $derived_user => $derived_entry_id)
		{
			self::$ilTEPHistorizing->updateHistorizedData(
								   array(
									   'user_id'              => $derived_user,
									   'cal_entry_id'         => $tep_entry->getEntryId(),
									   'cal_derived_entry_id' => $derived_entry_id ),
								   self::getStateData( $a_event, $tep_entry ),
								   self::getRecordCreator( $a_event, $tep_entry ),
								   self::getCreationTimestamp( $a_event, $tep_entry ),
								   false // Not a mass-action
			);
		}
	}

	/**
	 * Persists deletion for derived entries.
	 * 
	 * @param string     $a_event (Which should always be "delete")
	 * @param ilTEPEntry $tep_entry
	 */
	private static function persistDerivedDeleted($a_event, ilTEPEntry $tep_entry)
	{
		global $ilDB;
		$query = 'SELECT user_id, cal_derived_entry_id FROM hist_tep WHERE cal_derived_entry_id != 1 AND cal_entry_id = ' 
			. $ilDB->quote($tep_entry->getEntryId(), 'integer');
		$result = $ilDB->query($query);

		/** @noinspection PhpAssignmentInConditionInspection */
		while( $row = $ilDB->fetchAssoc($result) )
		{
			self::$ilTEPHistorizing->updateHistorizedData(
								   array(
									   'user_id'              => $row['user_id'],
									   'cal_entry_id'         => $tep_entry->getEntryId(),
									   'cal_derived_entry_id' => $row['cal_derived_entry_id'] ),
								   self::getStateData( $a_event, $tep_entry ),
								   self::getRecordCreator( $a_event, $tep_entry ),
								   self::getCreationTimestamp( $a_event, $tep_entry ),
								   false // Not a mass-action
			);
		}
	}
}