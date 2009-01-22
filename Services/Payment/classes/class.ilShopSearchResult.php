<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once 'Services/Search/classes/class.ilSearchResult.php';

/**
* searchResult stores all result of a search query.
* Offers methods like mergeResults. To merge result sets of different queries.
* 
* 
* @author Michael Jansen <mjansen@databay.de>
* @version Id$
* 
* @ingroup ServicesPayment
*/
class ilShopSearchResult extends ilSearchResult
{	
	protected $result_page_number = 0;
	protected $topics = array();
	protected $presentation_results = array();
	protected $search_type = '';
	
	public function ilShopSearchResult($a_search_type)
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
	 * @access public
	 * @param int root node id
	 * @param bool check and boolean search
	 * @return bool success status
	 * 
	 */
	public function filter($a_root_node, $check_and)
	{
		global $tree;

		$this->__initSearchSettingsObject();
			
		// get ref_ids and check access
		$counter = 0;
		$offset_counter = 0;

		$tmp_entries = array();

		foreach($this->getEntries() as $entry)
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
			
			// Check referenced objects
			foreach(ilObject::_getAllReferences($entry['obj_id']) as $ref_id)
			{
				$type = ilObject::_lookupType($ref_id, true);
				if($this->ilAccess->checkAccessOfUser($this->getUserId(),
													  $this->getRequiredPermission(),
													  '',
													  $ref_id,
													  $type,
													  $entry['obj_id']))
				{					
					if($a_root_node == ROOT_FOLDER_ID or $tree->isGrandChild($a_root_node,$ref_id))
					{
						if($this->callListeners($ref_id, $entry))
						{
							$entry['ref_id'] = $ref_id;
							$entry['type'] = $type;							
							$entry['topic_id'] = ilPaymentObject::_lookupTopicId($ref_id);
							$tmp_entries[$ref_id] = $entry;							
						}
					}
					continue;
				}
			}
		}

		$this->results = array();		
		
		$tmp_entries = $this->assignEntries($tmp_entries);		
		
		$counter = 0;		
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
		include_once 'payment/classes/class.ilGeneralSettings.php';
		$maxhits = ilGeneralSettings::_getInstance()->get('max_hits'); 
		$this->setMaxHits(($maxhits > 0 ? $maxhits : 20));
	}
	
	public function getResultsForPresentation()
	{
		$results = array();
		
		$offset_counter = 0;
		$counter = 0;

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
		
		$objects_with_topics = array();
		$objects_with_no_topcis = array();
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
	
				$presentation_results[$oTopic->getId()][$type][] = array('ref_id' => $result['ref_id'],
													  'title' => $title,
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
} 
?>