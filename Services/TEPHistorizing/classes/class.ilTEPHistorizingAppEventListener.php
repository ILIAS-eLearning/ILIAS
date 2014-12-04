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

require_once("Services/TEP/classes/class.ilTEPEntry.php");
require_once("Services/TEP/classes/class.ilCalDerivedEntry.php");
require_once("Services/TEP/classes/class.ilTEPOperationDays.php");

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
		global $ilDB;
		
		/** @var ilTEPEntry $tep_entry */
		$tep_entry = $a_parameter['entry'];
		self::initEventHandler();

		$case_id = self::getCaseId($a_event, $tep_entry);
		$state_data = self::getStateData($a_event, $tep_entry);
		$record_creator = self::getRecordCreator($a_event, $tep_entry);
		$ts = self::getCreationTimestamp($a_event, $tep_entry);

		// Users with derived entries
		$uids = ilCalDerivedEntry::getUserIdsByMasterEntryIds(array($tep_entry->getEntryId()));
		if (array_key_exists($tep_entry->getEntryId(), $uids)) {
			$uids = $uids[$tep_entry->getEntryId()];
		}
		else {
			$uids = array();
		}
		
		$op_days_obj = new ilTEPOperationDays("tep_entry", $tep_entry->getEntryId(), $tep_entry->getStart(), $tep_entry->getEnd());
		$op_days = $op_days_obj->getDaysForUsers(array_merge(array($tep_entry->getOwnerId()), array_keys($uids)));

		$state_data["individual_days"] = count($op_days[$tep_entry->getOwnerId()]);

		// Historize Base-Entry
		self::$ilTEPHistorizing->updateHistorizedData(
								$case_id,
								$state_data,
								$record_creator,
								$ts,
								false // Not a mass-action
		);
		
		// historize derived entries
		foreach($uids as $uid => $drvd_id) {
			$case_id["user_id"] = $uid;
			$case_id["cal_derived_entry_id"] = $drvd_id;
			$state_data["individual_days"] = $op_days[$uid];
			self::$ilTEPHistorizing->updateHistorizedData(
									$case_id,
									$state_data,
									$record_creator,
									$ts,
									false // Not a mass-action
			);
		}

		// Mark all removed derived entries as deleted.
		$query = "SELECT cal_derived_entry_id, user_id FROM hist_tep "
				." WHERE ".(count($uids) > 0 ? $ilDB->in("cal_derived_entry_id", $uids, true, "integer")
											 : " 1 = 1")
				."   AND cal_entry_id = ".$ilDB->quote($tep_entry->getEntryId(), "integer")
				."   AND cal_derived_entry_id <> -1" // this is to not delete the master entry histo
				."   AND deleted = 0"
				."   AND hist_historic = 0"
				;

		$res = $ilDB->query($query);
		while($rec = $ilDB->fetchAssoc($res)) {
			$case_id["user_id"] = $rec["user_id"];
			$case_id["cal_derived_entry_id"] = $rec["cal_derived_entry_id"];
			self::$ilTEPHistorizing->updateHistorizedData(
										$case_id,
										array("deleted" => 1),
										$record_creator,
										$ts,
										false // Not a mass-action
									);
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
			'cal_derived_entry_id'	=> -1
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
			'category'				=> $parameter->getTypeTitle(),
			'individual_days'		=> -1,
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
}