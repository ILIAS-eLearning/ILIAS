<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Payment/classes/class.ilShopTopic.php';
//include_once 'Services/Payment/classes/class.ilPaymentSettings.php';

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
		
			$data_types = array();
			$data_values = array();
			
			$query = 'SELECT * FROM payment_topics WHERE 1 = 1 ';
			if((int)$this->getIdFilter() > 0)
			{
				$query .= ' AND pt_topic_pk = %s';
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

			$data_types = array();
			$data_values = array();

			$query = 'SELECT * FROM payment_topics ';		
			switch($this->getSortingType())
			{
				case 3:
					$query .= ' LEFT JOIN payment_topic_usr_sort ON 
							       ptus_pt_topic_fk = pt_topic_pk AND
								   ptus_usr_id = %s';
					array_push($data_types, 'integer');
					array_push($data_values, $ilUser->getId());
					
					break;
			}
			$query .= ' WHERE 1 = 1 ';
			
			if((int)$this->id_filter > 0)
			{
				$query .= ' AND pt_topic_pk = %s';
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
			$res = $this->db->queryf($query, $data_types, $data_values);
		}
		else
		{
			$res = $this->db->query($query);
		}
		
		$counter = 0;
		while($row = $this->db->fetchObject($res))
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
	
	public function getCountAssignedTopics()
	{
		global $ilDB;
		
		$res = $ilDB->query('SELECT pt_topic_fk, count(pt_topic_fk) cnt FROM payment_objects GROUP BY pt_topic_fk');
		
		$topics_count = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$topics_count[$row['pt_topic_fk']] = (int)$row['cnt'];
		}

		return $topics_count;
	}
}
?>