<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TEP entries application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 */
class ilTEPEntries
{	
	protected $user_ids; // [array]
	protected $catgory_ids; // [array]
	protected $start; // [ilDate]
	protected $end; // [ilDate]
	protected $filter; // [array]
	
	/**
	 * Constructor
	 * 
	 * @param ilDate $a_start
	 * @param ilDate $a_end
	 * @param array $a_user_ids
	 * @param array $a_filter
	 * @return self
	 */
	public function __construct(ilDate $a_start, ilDate $a_end, array $a_user_ids, array $a_filter = null)
	{
		$this->setPeriod($a_start, $a_end);
		$this->setUserIds($a_user_ids);
		$this->setFilter($a_filter);		
	}
	
	
	//
	// properties
	//
	
	/**
	 * Set start and end date
	 * 
	 * @param ilDate $a_start
	 * @param ilDate $a_end
	 */
	protected function setPeriod(ilDate $a_start, ilDate $a_end)
	{
		if(ilDate::_after($a_start, $a_end))
		{
			$this->start = $a_end;
			$this->end = $a_start;
		}
		else
		{
			$this->start = $a_start;
			$this->end = $a_end;
		}		
	}
	
	/**
	 * Get start date
	 * 
	 * @return ilDate
	 */
	protected function getStart()
	{
		return $this->start;
	}
	
	/**
	 * Get start date
	 * 
	 * @return ilDate
	 */
	protected function getEnd()
	{
		return $this->end;
	}
	
	/**
	 * Set user ids
	 * 
	 * @param array $a_ids
	 */
	protected function setUserIds(array $a_ids)
	{
		$this->user_ids = array_unique($a_ids);
		$this->setCategoriesFromUserIds($this->user_ids);
	}
	
	/**
	 * Get user ids
	 * 
	 * @return array 
	 */
	protected function getUserIds()
	{
		return $this->user_ids;
	}
	
	/**
	 * Set category ids
	 * 
	 * @param array $a_user_ids
	 */
	protected function setCategoriesFromUserIds(array $a_user_ids)
	{
		global $ilDB;
		
		require_once "Services/TEP/classes/class.ilTEP.php";	
		
		// global course calendar
		$res = array(ilTEP::getCourseCalendarId());

		// personal calendars
		foreach($a_user_ids as $user_id)
		{
			$user_cat = ilTEP::getPersonalCalendarId($user_id, false);
			if($user_cat)
			{
				$res[$user_id] = $user_cat;
			}
		}
		
		// #156 - add calendar categories of master entries
		$query = "SELECT ca.cat_id, up.usr_id".
			" FROM cal_derived_entry cd".
			" JOIN cal_cat_assignments ca ON (ca.cal_id = cd.master_cal_entry)".
			" JOIN usr_pref up ON (up.keyword = ".$ilDB->quote("tep_cat_id", "text").
			" AND up.value = ca.cat_id)".
			" WHERE ".$ilDB->in("cd.cat_id", array_values($res), "", "integer");		
		$set = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($set))
		{
			if(!in_array($row["cat_id"], $res))
			{
				$res[$row["usr_id"]] = $row["cat_id"];
			}
		}
		
		$this->catgory_ids = $res;
	}
	
	/**
	 * Get category ids 
	 * 
	 * @return array
	 */
	protected function getCategoryIds()
	{
		return $this->catgory_ids;
	}
	
	/**
	 * Set filter (items)
	 * 
	 * @param array $a_filter
	 */
	protected function setFilter(array $a_filter = null)
	{
		$this->filter = $a_filter;
	}
	
	/**
	 * Get filter item
	 * 
	 * @return mixed 
	 */
	protected function getFilterItem($a_item)
	{
		if(is_array($this->filter) && 
			array_key_exists($a_item, $this->filter))
		{
			return $this->filter[$a_item];
		}
	}
	
	
	//
	// find
	// 
	
	/**
	 * Get matching calendar entries
	 * 
	 * @return array
	 */
	protected function getRawEntries()
	{
		global $ilDB;
		
		$res = array();
		
		// get matching entries
		
		$cat_ids = $this->getCategoryIds();	
		$cat_map = array_flip($cat_ids);
		$start = $this->getStart()->get(IL_CAL_DATE);
		$end = $this->getEnd()->get(IL_CAL_DATE);
		
		
		$query = "SELECT ce.cal_id, ce.title, ce.subtitle, ce.description, ce.starta,".
			" ca.cat_id, ce.enda, ce.location, ce.entry_type, ce.context_id,".
			" ce.fullday".
		    " FROM cal_entries ce".
			" JOIN cal_cat_assignments ca ON (ca.cal_id = ce.cal_id".
			" AND ".$ilDB->in("ca.cat_id", array_values($cat_ids), "", "integer").")".			
			" LEFT JOIN crs_settings crss ON (crss.obj_id = ce.context_id)".
		    " WHERE ((ce.starta >= ".$ilDB->quote($start." 00:00:00", "timestamp").
		    " AND ce.enda <= ".$ilDB->quote($end." 23:59:59", "timestamp").")". 
			" OR (ce.starta <= ".$ilDB->quote($start." 00:00:00", "timestamp").
		    " AND ce.enda >= ".$ilDB->quote($start." 00:00:00", "timestamp").")".
			" OR (ce.starta <= ".$ilDB->quote($end." 23:59:59", "timestamp").
		    " AND ce.enda >= ".$ilDB->quote($end." 23:59:59", "timestamp")."))"; 

		
		// filters
		$filter_type = $this->getFilterItem("etype");
		if($filter_type)
		{
			$query .= " AND ce.entry_type = ".$ilDB->quote($filter_type, "text");
		}
		$filter_title = $this->getFilterItem("etitle");	
		if($filter_title)
		{
			$query .= " AND ".$ilDB->like("ce.title", "text", "%".$filter_title."%");
		}
		$filter_location = $this->getFilterItem("eloc");
		if($filter_location)
		{
			$query .= " AND ".$ilDB->like("ce.location", "text", "%".$filter_location."%");				
		}		

		$query .= " ORDER BY ce.starta, ce.enda";
		
		$set = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($set))
		{
			// #961 - remove time, mind timezone
			if($row["fullday"])
			{
				$row["start"] = new ilDate($row["starta"], IL_CAL_DATE);
				$row["end"] = new ilDate($row["enda"], IL_CAL_DATE);
			}
			else
			{
				$row["start"] = new ilDateTime($row["starta"], IL_CAL_DATETIME, "UTC");
				$row["end"] = new ilDateTime($row["enda"], IL_CAL_DATETIME, "UTC");
			}
			$row["start"] = $row["start"]->get(IL_CAL_DATE);			
			$row["end"] = $row["end"]->get(IL_CAL_DATE);
					
			$user_id = $cat_map[$row["cat_id"]];	
			$idx = $this->buildEntryId($row["cal_id"]);
			$res[$user_id][$idx] = $row;
		}
	
		return $res;
	}
	
	/**
	 * Build entry id from component ids
	 * 
	 * @param int $a_master_id
	 * @param int $a_derived_id
	 * @param int $a_op_id
	 * @return string
	 */
	protected function buildEntryId($a_master_id, $a_derived_id = null, $a_op_id = null)
	{
		return $a_master_id.".".$a_derived_id.".".$a_op_id;
	}
	
	/**
	 * Get components from entry id
	 * 
	 * @param string $a_entry_id
	 * @return array
	 */
	protected function getComponentsFromEntryId($a_entry_id)
	{
		return explode(".", $a_entry_id);		
	}
	
	/**
	 * Get master id from entry id
	 * 
	 * @param string $a_entry_id
	 * @return int
	 */
	protected function getMasterIdFromEntryId($a_entry_id)
	{
		$parts = $this->getComponentsFromEntryId($a_entry_id);
		return (int)$parts[0];
	}
	
	/**
	 * Get derived id from entry id
	 * 
	 * @param string $a_entry_id
	 * @return int
	 */
	protected function getDerivedIdFromEntryId($a_entry_id)
	{
		$parts = $this->getComponentsFromEntryId($a_entry_id);
		return (int)$parts[1];
	}
	
	/**
	 * Add course ref ids
	 * 
	 * @param array &$a_raw
	 */
	protected function addCourseRefIds(array &$a_raw)
	{
		global $ilDB, $tree;

		$course_ids = array();
		
		if(array_key_exists(0, $a_raw))
		{
			foreach($a_raw[0] as $entry_id => $item)
			{
				if($item["context_id"])
				{
					$course_ids[$item["context_id"]] = $entry_id;
				}
			}
			
			if(sizeof($course_ids))
			{
				$sql = "SELECT oref.obj_id,oref.ref_id,act.timing_type".
					" FROM object_reference oref".
					" LEFT JOIN crs_items act ON (act.obj_id = oref.ref_id)".
					" WHERE ".$ilDB->in("oref.obj_id", array_keys($course_ids), "", "integer");
				$set = $ilDB->query($sql);
				while($row = $ilDB->fetchAssoc($set))
				{						
					// #154 - offline/trashed courses should not be presented in TEP
					if($row["timing_type"] &&
						!$tree->isDeleted($row["ref_id"]))
					{
						$a_raw[0][$course_ids[$row["obj_id"]]]["course_ref_id"] = $row["ref_id"];		
						
						// force fullday - operation days do not make sense with datetimes, as we have no start-end for single days
						$a_raw[0][$course_ids[$row["obj_id"]]]["fullday"] = true;					
						$a_raw[0][$course_ids[$row["obj_id"]]]["starta"] = substr($a_raw[0][$course_ids[$row["obj_id"]]]["starta"], 0, 10);					
						$a_raw[0][$course_ids[$row["obj_id"]]]["end"] = substr($a_raw[0][$course_ids[$row["obj_id"]]]["end"], 0, 10);				
					}
					else
					{
						unset($a_raw[0][$course_ids[$row["obj_id"]]]);
					}
				}
			}			
		}
	}
	
	/**
	 * Add derived entries 
	 * 
	 * @param array &$a_raw
	 */
	protected function addDerivedEntries(array &$a_raw)
	{
		// we need all users (regardless of filters) to correctly handle courses with "missing operation days"
		
		$entry_map = array();
		foreach($a_raw as $user_id => $entries)
		{			
			foreach(array_keys($entries) as $entry_id)
			{
				$master_id = $this->getMasterIdFromEntryId($entry_id);
				$entry_map[$master_id] = $user_id;								
			}
		}
		
		require_once "Services/TEP/classes/class.ilCalDerivedEntry.php";				
		foreach(ilCalDerivedEntry::getUserIdsByMasterEntryIds(array_keys($entry_map)) as $master_id => $users)
		{
			foreach($users as $user_id => $drv_entry_id)
			{												
				$master_entry_id = $this->buildEntryId($master_id);
				$master_entry_user = $entry_map[$master_id];
								
				$entry_id = $this->buildEntryId($master_id, $drv_entry_id);						
				$a_raw[$user_id][$entry_id] = $a_raw[$master_entry_user][$master_entry_id];
				$a_raw[$user_id][$entry_id]["derived_id"] = $drv_entry_id;					
			}
		}	
	}
	
	/**
	 * Handle operation days (split events if necessary)
	 *  
	 * @param array &$a_raw
	 */
	protected function handleOperationDays(array &$a_raw)
	{		
		$op_objects = $op_map = array();
		
		// gather master entries and their derived users 
		foreach($a_raw as $user_id => $entries)
		{			
			if($user_id)
			{
				foreach($entries as $entry_id => $entry)
				{					
					// currently restricted to courses
					if($entry["course_ref_id"])
					{	
						$master_id = $this->getMasterIdFromEntryId($entry_id);					
						$op_objects[$master_id][$user_id] = $entry_id;
						
						if(!in_array($master_id, $op_map))
						{
							$op_map[] = $master_id;
						}
					}
				}							
			}
		}
		
		// process operation days for all valid master entries
		if(sizeof($op_map))
		{
			require_once "Services/TEP/classes/class.ilTEPEntry.php";					
			require_once "Services/TEP/classes/class.ilTEPOperationDays.php";					
			require_once "Services/TEP/classes/class.ilTEPPeriodInputGUI.php";
			
			foreach($op_map as $master_id)
			{														
				$master_entry_id = $this->buildEntryId($master_id);
				$master_entry = $a_raw[0][$master_entry_id];
				
				// #183
				$master_days = ilTEPPeriodInputGUI::convertPeriodToDays(
					$master_entry["start"]
					,$master_entry["end"]
				);				
								
				$op_days = new ilTEPOperationDays(
					ilTEPEntry::OPERATION_DAY_ID
					,$master_id
					,new ilDate($master_entry["start"], IL_CAL_DATE)
					,new ilDate($master_entry["end"], IL_CAL_DATE)
				);
				$op_user_days = $op_days->getDaysForUsers(array_keys($op_objects[$master_id]));
				
				// convert user operation days to chunks and split derived entries
				$master_op_days = array();
				foreach($op_user_days as $user_id => $days)
				{						
					foreach($days as $idx => $day)
					{					
						$days[$idx] = $day->get(IL_CAL_DATE);
						$master_op_days[] = $days[$idx];
					}										
					
					// days are missing?
					if(array_diff($master_days, $days))
					{																							
						$derived_entry_id = $op_objects[$master_id][$user_id];					
						$org_entry = $a_raw[$user_id][$derived_entry_id];						
						
						// remove original entry
						unset($a_raw[$user_id][$derived_entry_id]);
						
						$derived_id = $this->getDerivedIdFromEntryId($derived_entry_id);
						
						// create entries for each chunk
						$chunks = ilTEPPeriodInputGUI::convertDaysToChunks($days);							
						foreach($chunks as $idx => $chunk)
						{							
							// adapt entry period to chunk
							$chunk_entry = $org_entry;							
							$chunk_entry["fullday"] = true; // makes only sense for full days
							$chunk_entry["start"] = $chunk_entry["starta"] = $chunk[0];							
							$chunk_entry["end"] = $chunk_entry["enda"] = $chunk[1];				
							$chunk_entry["chunk_id"] = $idx;
						
							$chunk_id = $this->buildEntryId($master_id, $derived_id, $idx);
							
							$a_raw[$user_id][$chunk_id] = $chunk_entry;
						}
					}
				}	
				
				$master_op_days = array_unique($master_op_days);
				
				// if all days are accounted for, master entry must not be displayed
				if(!array_diff($master_days, $master_op_days))
				{
					unset($a_raw[0][$master_entry_id]);					
				}
			}
		}
	}
	
	/**
	 * Prepare raw data for presentation
	 *  
	 * @param array &$a_raw
	 */
	protected function prepareRawForPresentation(array &$a_raw)
	{
		global $ilAccess;
		
		require_once "Services/TEP/classes/class.ilTEPPeriodInputGUI.php";
		
		$res = array();
		
		$valid_cat_ids = array_values($this->getCategoryIds());				
		$valid_user_ids = $this->getUserIds();
		foreach($a_raw as $user_id => $entries)
		{						
			foreach($entries as $entry_id => $entry)
			{				
				// :TODO: if user has no write-access to course it should not be presented
				if(!$user_id)
				{
					if($entry["course_ref_id"] && 
						!$ilAccess->checkAccess("write", "", $entry["course_ref_id"]))
					{
						continue;
					}	
				}
				// don't show cancelled trainings w/o trainers
				if((int)$user_id === 0 && isset($entry['course_ref_id'])) {
					if(gevCourseUtils::getInstanceByObj(new ilObjCourse($entry['course_ref_id']))->getIsCancelled()) {
						continue;
					}
				}
				
				if(in_array($entry["cat_id"], $valid_cat_ids) &&
					(!$user_id || in_array($user_id, $valid_user_ids)))
				{					
					// add period to key to enable sorting (see below)
					$idx = $entry["start"]."_".$entry["end"]."_".$entry_id;
					// gev-patch start
					$entry["user_id"] = $user_id;
					// gev-patch end
					$res[$user_id][$idx] = $entry;
				}					
			}
		}
		
		// all items have to be sorted by start, end
		foreach($res as $user_id => $items)
		{
			ksort($items);
			$res[$user_id] = array_values($items);
		}
		
		return $res;
	}
	
	/**
	 * Get matching entries for presentation
	 * 
	 * @return array
	 */
	public function getEntriesForPresentation()
	{		
		$res = array();
		
		$raw = $this->getRawEntries();
		if(sizeof($raw))
		{							
			$this->addCourseRefIds($raw);
						
			$this->addDerivedEntries($raw);
			
			$this->handleOperationDays($raw);
															
			$res = $this->prepareRawForPresentation($raw);
		}		
		
		return $res;
	}
}