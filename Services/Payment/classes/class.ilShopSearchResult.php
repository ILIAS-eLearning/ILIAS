<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Search/classes/class.ilSearchResult.php';

/**
* searchResult stores all result of a search query.
* Offers methods like mergeResults. To merge result sets of different queries.
* 
* 
* @author Michael Jansen <mjansen@databay.de>
* @version $Id:$
* 
* @ingroup ServicesPayment
*/
class ilShopSearchResult extends ilSearchResult
{
	const SHOW_SPECIAL_CONTENT = 1;
	const SHOW_CONTAINER_CONTENT = 2;
	const SHOW_TOPICS_CONTENT = 3;
	
	private static $_instance;
	
	protected $result_page_number = 0;
	protected $topics = array();
	protected $presentation_results = array();

	/**
	 * @var int  SHOP_CONTENT | SHOP_ADVANCED_SEARCH
	 */
	protected $search_type = SHOP_CONTENT;
	
	public $filter_mode = null;

	/**
	 * @param $a_search_type
	 * @return ilShopSearchResult
	 */
	public function _getInstance($a_search_type)
	{
		if(!isset(self::$_instance))
		{
			self::$_instance = new ilShopSearchResult($a_search_type);
		}

		return self::$_instance;
	}

	/**
	 * @param int $a_search_type  SHOP_CONTENT | SHOP_ADVANCED_SEARCH
	 */
	private function __construct($a_search_type)
	{
		global $ilUser;
		
		$this->search_type = $a_search_type;
		parent::__construct($ilUser->getId());	
	}
	
	public function setSearchType($_search_type)
	{
		$this->search_type = $_search_type;
		
		return $this;
	}
	
	public function getSearchType()
	{
		return $this->search_type;
	}

	/**
	 * @return int
	 */
	public function getFilterMode()
	{
		return $this->filter_mode;
	}

	/**
	 * @param int $filter_mode 
	 */
	public function setFilterMode($filter_mode)
	{
		$this->filter_mode = $filter_mode;
	}
	
	protected function assignEntries($a_entries)
	{
		$ordered_entries = array();

		$num_entries = count($a_entries);
		foreach($this->topics as $oTopic)
		{
			foreach($a_entries as $aEntry)
			{
				if($oTopic->getId() == $aEntry['topic_id'])
				{
					$ordered_entries[$aEntry['ref_id']] = $aEntry;
				}				
			}
		}
		if(count($ordered_entries) < $num_entries)
		{
			foreach($a_entries as $aEntry)
			{
				if(0 == $aEntry['topic_id'])
				{
					$ordered_entries[$aEntry['ref_id']] = $aEntry;
				}				
			}
		}

		return is_array($ordered_entries) ? $ordered_entries : array();
	}
	
	/**
	 * Filter search result.
	 * Do RBAC checks.
	 * 
	 * Allows paging of results for referenced objects
	 *
	 * @param int a_root_node node id
	 * @param bool check_and and boolean search
	 * @return bool success status
	 * 
	 */
	public function filter($a_root_node, $check_and)
	{
		global $tree;

		$this->__initSearchSettingsObject();
			
		// get ref_ids and check access

		$tmp_entries = array();
		foreach($this->getEntries() as $ref_id => $entry)
		{
			// boolean and failed continue
			if($check_and && in_array(0, $entry['found']))
			{
				continue;
			}
			// Types like role, rolt, user do not need rbac checks
			$type = ilObject::_lookupType($entry['obj_id']);
			if($type == 'rolt' or $type == 'usr' or $type == 'role')
			{
				continue;
			}			
			
			// Check access
			if($this->ilAccess->checkAccessOfUser($this->getUserId(),
					  $this->getRequiredPermission(),
					  '',
					  $ref_id))
				{					
					if($a_root_node == ROOT_FOLDER_ID || $tree->isGrandChild($a_root_node, $ref_id))
					{
						if($this->callListeners($ref_id, $entry))
						{
							$entry['ref_id'] = $ref_id;
							$entry['type'] = $type;							
							$entry['topic_id'] = ilPaymentObject::_lookupTopicId($ref_id);
							$tmp_entries[$ref_id] = $entry;							
						}
					}
				}
		}

		$this->results = array();		
		
		$tmp_entries = $this->assignEntries($tmp_entries);		
		
		foreach($tmp_entries as $entry)
		{			
			if($this->callListeners($entry['ref_id'], $entry))
			{				
				$this->addResult($entry['ref_id'], $entry['obj_id'], $entry['type']);
				$this->search_cache->appendToChecked($entry['ref_id'], $entry['obj_id']);
				$this->__updateResultChilds($entry['ref_id'], $entry['child']);
			}
		}
		$this->search_cache->setResults($this->results);
		return false;
	}
	
	public function __initSearchSettingsObject()
	{
		include_once 'Services/Payment/classes/class.ilPaymentSettings.php';
		$maxhits = ilPaymentSettings::_getInstance()->get('max_hits');
		$this->setMaxHits(($maxhits > 0 ? $maxhits : 20));
	}
	
	public function getResultsForPresentation()
	{
		global $lng;
		$results = array();
		
		$offset_counter = 0;
		$counter = 0;

		$objects_with_topics = array();
		$objects_with_no_topcis = array();

		foreach($this->getResults() as $result)
		{
			if($this->getMaxHits() * ($this->getResultPageNumber() - 1) > $offset_counter)
			{
				++$offset_counter;
				continue;
			}
			
			$results[] = $result;
			++$counter;			
			
			if($counter >= $this->getMaxHits())
			{
				break;
			}
		}

		foreach($this->getTopics() as $oTopic)
		{		
			foreach($results as $result)
			{
				$topic_id = ilPaymentObject::_lookupTopicId($result['ref_id']);
				
				if(!(int)$topic_id && !array_key_exists($result['ref_id'], $objects_with_no_topcis))
				{					
					$objects_with_no_topcis[$result['ref_id']] = $result;
					continue;
				}
				
				if((int)$topic_id != $oTopic->getId())
				{
					continue;					
				}
				
				if(!array_key_exists($result['ref_id'], $objects_with_topics))
				{
					$objects_with_topics[$result['ref_id']] = $result;
				}
				
				switch($result['type'])
				{
					// learning material
					case "sahs":
					case "lm":
					case "dbk":
					case "htlm":
						$type = "lres";
						break;
	
					default:
						$type = $result['type'];
						break;
				}
				$title = ilObject::_lookupTitle($result['obj_id']);
				$description = ilObject::_lookupDescription($result['obj_id']);
	
					$subtype = '';
					if($result['type'] == 'exc')
					{
						$check_sub = ilPaymentObject::_checkExcSubtype($result['ref_id']);
						$subtype = ' ('.$lng->txt($check_sub[0]).')';
					}

				$presentation_results[$oTopic->getId()][$type][] = array('ref_id' => $result['ref_id'],
														  'title' => $title.' '.$subtype,
													  'description' => $description,
													  'type' => $result['type'],
													  'obj_id' => $result['obj_id'],
													  'topic_id' => $topic_id,
													  'child' => $result['child']);
				$this->addPresentationResult($presentation_results[$oTopic->getId()][$type][count($presentation_results[$oTopic->getId()][$type]) - 1]);
			}
		}
		foreach($results as $result)
		{
			if(!array_key_exists($result['ref_id'], $objects_with_topics) &&
			   !array_key_exists($result['ref_id'], $objects_with_no_topcis))
			{
				$objects_with_no_topcis[$result['ref_id']] = $result;
			}
		}
		foreach($objects_with_no_topcis as $result)
		{
			switch($result['type'])
			{
				// learning material
				case "sahs":
				case "lm":
				case "dbk":
				case "htlm":
					$type = "lres";
					break;

				default:
					$type = $result['type'];
					break;
			}
			$title = ilObject::_lookupTitle($result['obj_id']);
			$description = ilObject::_lookupDescription($result['obj_id']);

			$presentation_results[0][$type][] = array('ref_id' => $result['ref_id'],
												  'title' => $title,
												  'description' => $description,
												  'type' => $result['type'],
												  'obj_id' => $result['obj_id'],
												  'child' => $result['child']);
			$this->addPresentationResult($presentation_results[0][$type][count($presentation_results[0][$type]) - 1]);
		}
		
		return $presentation_results ? $presentation_results : array();
	}
	
	public function getTopics()
	{
		return is_array($this->topics) ? $this->topics : array();
	}
	public function setTopics($a_topics = array())
	{
		$this->topics = $a_topics;
	}
	
	public function setResultPageNumber($a_result_page_number)
	{
		$this->result_page_number = (int)$a_result_page_number > 0 ? $a_result_page_number : 1;
	}
	public function getResultPageNumber()
	{
		return $this->result_page_number;
	}
	
	public function addPresentationResult($a_presentation_result = array())
	{
		$this->presentation_results[] = $a_presentation_result;
	}
	public function getPresentationResults()
	{
		return is_array($this->presentation_results) ? $this->presentation_results : array();
	}
	
	protected function initUserSearchCache()
	{
	 	parent::initUserSearchCache();
	 	
	 	$this->search_cache->switchSearchType($this->search_type);
	}
	
	function addEntry($a_ref_id, $a_type, $found, $a_child_id = 0)
	{
		global $ilObjDataCache;		
		
		$a_obj_id = $ilObjDataCache->lookupObjId($a_ref_id);
		
		// Create new entry if it not exists
		if(!$this->entries[$a_ref_id])
		{
			$this->entries[$a_ref_id]['ref_id'] = $a_ref_id;
			$this->entries[$a_ref_id]['obj_id'] = $a_obj_id;
			$this->entries[$a_ref_id]['type'] = $a_type;
			$this->entries[$a_ref_id]['found'] = $found;

			if($a_child_id and $a_child_id != $a_ref_id)
			{
				$this->entries[$a_ref_id]['child'][$a_child_id] = $a_child_id;
			}
		}
		else
		{
			// replace or add child ('pg','st') id
			if($a_child_id and $a_child_id != $a_obj_id)
			{
				$this->entries[$a_ref_id]['child'][$a_child_id] = $a_child_id;
			}

			// UPDATE FOUND
			$counter = 0;
			foreach($found as $position)
			{
				if($position)
				{
					$this->entries[$a_ref_id]['found'][$counter] = $position;
				}
				$counter++;
			}
		}
		return true;
	}

	function __updateEntryChilds($a_ref_id,$a_childs)
	{
		if($this->entries[$a_ref_id] and is_array($a_childs))
		{
			foreach($a_childs as $child_id)
			{
				if($child_id)
				{
					$this->entries[$a_ref_id]['child'][$child_id] = $child_id;
				}
			}
			return true;
		}
		return false;
	}
	
	function mergeEntries(&$result_obj)
	{
		foreach($result_obj->getEntries() as $entry)
		{
			$this->addEntry($entry['ref_id'],$entry['type'],$entry['found']);
			$this->__updateEntryChilds($entry['ref_id'],$entry['child']);
		}
		return true;
	}
	
	function diffEntriesFromResult(&$result_obj)
	{
		$new_entries = $this->getEntries();
		$this->entries = array();

		// Get all checked objects
		foreach($this->search_cache->getCheckedItems() as $ref_id => $obj_id)
		{
			if(isset($new_entries[$ref_id]))
			{
				$this->addEntry($new_entries[$ref_id]['ref_id'],
								$new_entries[$ref_id]['type'],
								$new_entries[$ref_id]['found']);
				$this->__updateEntryChilds($new_entries[$ref_id]['ref_id'],
									 $new_entries[$ref_id]['child']);
			}
		}
	}
	
	function getUniqueResults()
	{
		return $this->results;
	}
	
} 
?>