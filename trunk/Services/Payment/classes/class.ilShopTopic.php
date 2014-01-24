<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilShopTopic
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id:$
* 
* @ingroup ServicesPayment 
* 
*/
class ilShopTopic
{
	private $id = 0;
	private $title = '';
	private $sorting = 0;
	private $createdate = 0;
	private $changedate = 0;
	private $custom_sorting = 0;
	
	private $db = null;
	
	public function __construct($a_id = 0)
	{
		global $ilDB;

		$this->db = $ilDB;
		
		if($a_id)
		{
			$this->id = $a_id;
			
			$this->read();
		}
	}
	
	private function read()
	{
		global $ilUser;
		
		if($this->id)
		{
			$result = $this->db->queryf('
				SELECT * FROM payment_topics 
				LEFT JOIN payment_topic_usr_sort ON ptus_pt_topic_fk = pt_topic_pk
				AND ptus_usr_id = %s
				WHERE pt_topic_pk = %s',
		        array('integer', 'integer'),
		        array($ilUser->getId(), $this->id));
		        
			while($row = $this->db->fetchObject($result))
			{
				$this->setTitle($row->pt_topic_title);
				$this->setSorting($row->pt_topic_sort);
				$this->setCreateDate($row->pt_topic_created);
				$this->setChangeDate($row->pt_topic_changed);
				$this->setCustomSorting($row->ptus_sorting);
				
				return true;		
			}			
		}
		
		return false;
	}
	
	public function save()
	{
		if($this->id)
		{
			$this->changedate = time();
			
			$statement = $this->db->manipulateF('
				UPDATE payment_topics
				SET pt_topic_title = %s,
					pt_topic_sort = %s,
					pt_topic_changed = %s
				WHERE pt_topic_pk = %s',
				array('text', 'integer', 'integer', 'integer'),
				array(	
					$this->getTitle(),
					$this->getSorting(),
					$this->getChangeDate(),
					$this->getId()
				));
			

			
			return true;
		}
		else
		{
			$this->createdate = time();
			
			$next_id = $this->db->nextId('payment_topics');
			$statement = $this->db->manipulateF('
				INSERT INTO payment_topics 
				( 	pt_topic_pk,
					pt_topic_title,
					pt_topic_sort,
					pt_topic_created
				) VALUES (%s, %s, %s, %s)',
				array('integer','text', 'integer', 'integer'),
				array($next_id, $this->getTitle(), $this->getSorting(), $this->getCreateDate()));

			$this->id = $next_id;			
			if($this->id) return true;
		}
		
		return	false;
	}
	
	function delete()
	{
		if($this->id)
		{
			
			$result = $this->db->manipulateF('
				DELETE FROM payment_topics		
 				WHERE pt_topic_pk = %s',
				array('integer'),
				array($this->getId())
			);
			
			$result = $this->db->manipulateF('
				DELETE FROM payment_topic_usr_sort		
				WHERE ptus_pt_topic_fk = %s',
				array('integer'),
				array($this->getId())
			);
			
			$result = $this->db->manipulateF('
				UPDATE payment_objects
				SET pt_topic_fk = %s
			  	WHERE pt_topic_fk = %s',
				array('integer', 'integer'),
				array(0, $this->getId())
			);
			
			return true;
		}
		
		return false;
	}
	
	public static function _lookupTitle($a_id)
	{
		global $ilDB;
				
		$result = $ilDB->queryf("SELECT pt_topic_title FROM payment_topics WHERE pt_topic_pk = %s",
		        	 array('integer'), array($a_id));

       	 while($row = $ilDB->fetchObject($result))
		{
			return $row->pt_topic_title;
		}
		
		return false;
	}
	
	public function saveCustomSorting()
	{
		global $ilUser;
		
		if($this->id)
		{		
			$res = $this->db->queryf('
				SELECT * FROM payment_topic_usr_sort
				WHERE ptus_pt_topic_fk = %s
				AND ptus_usr_id = %s',
				array('integer', 'integer'),
				array($this->getId(), $ilUser->getId())
			);
			
			if($this->db->numRows($res) > 0)
			{
				$statement = $this->db->manipulateF('
					UPDATE payment_topic_usr_sort
					SET ptus_sorting = %s
					WHERE ptus_usr_id = %s
					AND	ptus_pt_topic_fk = %s',
						array('integer', 'integer', 'integer'),
						array( $this->getCustomSorting(),$ilUser->getId(), $this->getId())
				);
			}
			else
			{
				$statement = $this->db->manipulateF('
					INSERT INTO payment_topic_usr_sort
					( 	ptus_pt_topic_fk,
						ptus_usr_id,
						ptus_sorting
					) VALUES (%s,%s,%s)',
						array('integer', 'integer', 'integer'),
						array($this->getId(), $ilUser->getId(), $this->getCustomSorting())
				);	
			}
			
			return true;
		}
		
		return false;
	}	
	
	public function setId($a_id)
	{
		$this->id = $a_id;
	}
	public function getId()
	{
		return $this->id;
	}
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	public function getTitle()
	{
		return $this->title;
	}
	public function setCreateDate($a_createdate)
	{
		$this->createdate = $a_createdate;
	}
	public function getCreateDate()
	{
		return $this->createdate;
	}
	public function setChangeDate($a_changedate)
	{
		$this->changedate = $a_changedate;
	}
	public function getChangeDate()
	{
		return $this->changedate;
	}
	public function setSorting($a_sorting)
	{
		$this->sorting = $a_sorting;
	}
	public function getSorting()
	{
		return $this->sorting;
	}
	public function setCustomSorting($a_custom_sorting)
	{
		$this->custom_sorting = $a_custom_sorting;
	}
	public function getCustomSorting()
	{
		return $this->custom_sorting;
	}
}
?>
