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


/**
* Class ilShopTopic
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
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
			$query = $this->db->prepare("SELECT * FROM payment_topics 
										 LEFT JOIN payment_topics_user_sorting ON ptus_pt_topic_fk = pt_topic_pk
										     AND ptus_usr_id = ?
										 WHERE pt_topic_pk = ?",
		        	 array('integer', 'integer'));
			$result = $this->db->execute($query, array($ilUser->getId(), $this->id));
			while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->title = $row->pt_topic_title;
				$this->sorting = $row->pt_topic_sort;
				$this->createdate = $row->pt_topic_created;
				$this->changedate = $row->pt_topic_changed;
				$this->custom_sorting = $row->ptus_sorting;
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
			
			$query = "UPDATE payment_topics
					  SET 	
					  pt_topic_title = ".$this->db->quote($this->title).",
					  pt_topic_sort = ".$this->db->quote($this->sorting).",										
					  pt_topic_changed = ".$this->db->quote($this->changedate)."
					  WHERE 1
					  AND pt_topic_pk = ".$this->db->quote($this->id)." ";
			
			$this->db->query($query);
			
			return true;
		}
		else
		{
			$this->createdate = time();
			
			$query = "INSERT INTO payment_topics
					  SET 
					  pt_topic_title = ".$this->db->quote($this->title).",
					  pt_topic_sort = ".$this->db->quote($this->sorting).",
					  pt_topic_created = ".$this->db->quote($this->createdate)." ";
			
			$this->db->query($query);
			
			$this->id = $this->db->getLastInsertId();			
			if($this->id) return true;
		}
		
		return	false;
	}
	
	function delete()
	{
		if($this->id)
		{
			$query = "DELETE FROM payment_topics
					  WHERE 1
					  AND pt_topic_pk = ".$this->db->quote($this->id)." ";			
			$this->db->query($query);
			
			$query = "DELETE FROM payment_topics_user_sorting
					  WHERE 1
					  AND ptus_pt_topic_fk = ".$this->db->quote($this->id)." ";			
			$this->db->query($query);
			
			$query = "UPDATE payment_objects
					  SET pt_topic_fk = ?
					  WHERE 1
					  AND pt_topic_fk = ? ";
			$statement = $this->db->prepareManip($query, array('integer', 'integer'));
			$result = $this->db->execute($statement, array(0, $this->id));
			
			return true;
		}
		
		return false;
	}
	
	public static function _lookupTitle($a_id)
	{
		global $ilDB;
				
		$query = $ilDB->prepare("SELECT pt_topic_title FROM payment_topics WHERE pt_topic_pk = ?",
		        	 array('integer'));
		$result = $ilDB->execute($query, array($a_id));
		while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
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
			$query = "REPLACE INTO payment_topics_user_sorting
					  SET 
					  ptus_pt_topic_fk = ".$this->db->quote($this->id).",
					  ptus_usr_id = ".$this->db->quote($ilUser->getId()).",
					  ptus_sorting = ".$this->db->quote($this->custom_sorting)." ";
			$this->db->query($query);  

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
