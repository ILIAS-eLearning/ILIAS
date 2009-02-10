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

include_once 'Services/Payment/classes/class.ilShopTopic.php';
include_once 'payment/classes/class.ilGeneralSettings.php';

/**
* Class ilShopTopics
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
* 
* @ingroup ServicesPayment
*  
*/
class ilShopTopics
{
	private static $instance;
		
	private $db = null;
	private $sorting_type = self::TOPICS_SORT_BY_TITLE;
	private $sorting_direction = 'ASC';
	private $enable_custom_sorting = false;
	private $id_filter = 0;
	private $topics = array();	
	
	const TOPICS_SORT_BY_TITLE = 1;
	const TOPICS_SORT_BY_CREATEDATE = 2;
	const TOPICS_SORT_MANUALLY = 3;
	
	const DEFAULT_SORTING_TYPE = self::TOPICS_SORT_BY_TITLE;
	const DEFAULT_SORTING_DIRECTION = 'ASC';
	
	private function __construct()
	{
		global $ilDB;

		$this->db = $ilDB;		
	}
	
	public static function _getInstance()
	{
		if(!isset(self::$instance))
		{
			self::$instance = new ilShopTopics();
		}
		
		return self::$instance;
	}
	
	public function read()
	{
		global $ilUser;
		
		$oSettings = new ilGeneralSettings();
		
		$this->topics = array();
		
		if(!in_array($this->getSortingType(), array(self::TOPICS_SORT_BY_TITLE, self::TOPICS_SORT_BY_CREATEDATE, self::TOPICS_SORT_MANUALLY)))
		{
			$this->setSortingType(self::DEFAULT_SORTING_TYPE);
		}
		if(!in_array(strtoupper($this->getSortingDirection()), array('ASC', 'DESC')))
		{
			$this->setSortingDirection(self::DEFAULT_SORTING_DIRECTION);
		}
		
		if(!$this->isCustomSortingEnabled())
		{
/*			$query = "SELECT *
				      FROM payment_topics WHERE 1 ";
			
			if((int)$this->id_filter > 0)
			{
				$query .= " AND pt_topic_pk = ".$this->db->quote($this->id_filter)." ";
			}
				      
			switch($this->getSortingType())
			{
				case 3:
					$query .= " ORDER BY pt_topic_sort ";
					break;
					
				case 2:
					$query .= " ORDER BY pt_topic_created ";
					break;
					
				case 1:				
				default:
					$query .= " ORDER BY pt_topic_title ";
					break;
			}
			$query .= ' '.strtoupper($this->getSortingDirection()).' ';			
			$query .= " , pt_topic_title ";
			$query .= ' '.strtoupper($this->getSortingDirection()).' ';
*/
			
			$data_types = array();
			$data_values = array();
			
			$query = 'SELECT * FROM payment_topics WHERE 1';
			if((int)$this->getIdFilter() > 0)
			{
				$query .= ' AND pt_topic_pk = ?';
				array_push($data_types, 'integer');
				array_push($data_values, $this->getIdFilter()); 
			}
			
			switch($this->getSortingType())
			{
				case 3:
					$query .= ' ORDER BY pt_topic_sort ';
					break;
					
				case 2:
					$query .= ' ORDER BY pt_topic_created ';
					break;
					
				case 1:				
				default:
					$query .= ' ORDER BY pt_topic_title ';
					break;
			}
			$query .= ' '.strtoupper($this->getSortingDirection()).' ';			
			$query .= " , pt_topic_title ";
			$query .= ' '.strtoupper($this->getSortingDirection()).' ';
			
			
		}
		else
		{
/*			$query = 'SELECT * FROM payment_topics ';		
			switch($this->getSortingType())
			{
				case 3:
					$query .= " LEFT JOIN payment_topics_user_sorting ON 
							       ptus_pt_topic_fk = pt_topic_pk AND
								   ptus_usr_id = ".$this->db->quote($ilUser->getId())." ";
					break;
			}
			$query .= " WHERE 1 ";
			
			if((int)$this->id_filter > 0)
			{
				$query .= " AND pt_topic_pk = ".$this->db->quote($this->id_filter)." ";
			}
			
			switch($this->getSortingType())
			{
				case 3:
					$query .= " ORDER BY ptus_sorting ";
					break;
					
				case 2:
					$query .= " ORDER BY pt_topic_created ";
					break;
					
				case 1:				
				default:
					$query .= " ORDER BY pt_topic_title ";
					break;
			}				      
			$query .= ' '.strtoupper($this->getSortingDirection()).' ';
			$query .= " , pt_topic_sort ";
			$query .= ' '.strtoupper($this->getSortingDirection()).' ';
*/
			$data_types = array();
			$data_values = array();

			$query = 'SELECT * FROM payment_topics ';		
			switch($this->getSortingType())
			{
				case 3:
					$query .= ' LEFT JOIN payment_topics_user_sorting ON 
							       ptus_pt_topic_fk = pt_topic_pk AND
								   ptus_usr_id = ?';
					array_push($data_types, 'integer');
					array_push($data_values, $ilUser->getId());
					
					break;
			}
			$query .= ' WHERE 1 ';
			
			if((int)$this->id_filter > 0)
			{
				$query .= ' AND pt_topic_pk = ?';
				array_push($data_types, 'integer');
				array_push($data_values, $this->getIdFilter());
			}
			
			switch($this->getSortingType())
			{
				case 3:
					$query .= ' ORDER BY ptus_sorting ';
					break;
					
				case 2:
					$query .= ' ORDER BY pt_topic_created ';
					break;
					
				case 1:				
				default:
					$query .= ' ORDER BY pt_topic_title ';
					break;
			}				      
			$query .= ' '.strtoupper($this->getSortingDirection()).' ';
			$query .= " , pt_topic_sort ";
			$query .= ' '.strtoupper($this->getSortingDirection()).' ';
		}

		if(count($data_types) > 0 && count($data_values > 0))
		{
			$statement = $this->db->prepare($query, $data_types);
			$res = $this->db->execute($statement, $data_values);
		}
		else
		{
			$res = $this->db->execute($this->db->prepare($query));
		}
		
		$counter = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$oTopic = new ilShopTopic();
			$oTopic->setId($row->pt_topic_pk);
			$oTopic->setTitle($row->pt_topic_title);
			$oTopic->setSorting($row->pt_topic_sort);
			$oTopic->setCustomSorting((int)$row->ptus_sorting);			
			$oTopic->setCreateDate($row->pt_topic_created);
			$oTopic->setChangeDate($row->pt_topic_changed);			
			$this->topics[$row->pt_topic_pk] = $oTopic;
			
			++$counter;
		}	
		
		return $this;
	}
	
	public function getTopics()
	{
		return is_array($this->topics) ? $this->topics : array();
	}
	
	public function setIdFilter($a_id_filter)
	{
		$this->id_filter = $a_id_filter;
	}
	public function getIdFilter()
	{
		return $this->id_filter;
	}
	public function setSortingType($a_sorting_type)
	{
		$this->sorting_type = $a_sorting_type;
	}
	public function getSortingType()
	{
		return $this->sorting_type;
	}
	public function setSortingDirection($a_sorting_direction)
	{
		$this->sorting_direction = $a_sorting_direction;
	}
	public function getSortingDirection()
	{
		return $this->sorting_direction;
	}
	public function enableCustomSorting($a_enable_custom_sorting)
	{
		$this->enable_custom_sorting = (bool)$a_enable_custom_sorting;
	}
	public function isCustomSortingEnabled()
	{
		return (bool)$this->enable_custom_sorting;
	}
}
?>