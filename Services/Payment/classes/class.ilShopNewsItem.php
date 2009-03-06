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
*
* @author Nadia Krzywon <nkrzywon@databay.de>
* @version $Id$
*/
class ilShopNewsItem 
{
	private $id;
	private $title;
	private $content;
	private $creation_date;
	private $update_date;
	private $user_id;
	private $visibility = 'users';
	private $db = null;

	/**
	* Constructor.
	*
	* @param	int	$a_id	
	*/
	public function __construct($a_id = 0)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		
		if((int)$a_id > 0)
		{
			$this->setId((int)$a_id);
			$this->read();
		}
	}

	/**
	* Set Id.
	*
	* @param	int	$a_id	
	*/
	public function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get Id.
	*
	* @return	int	
	*/
	public function getId()
	{
		return $this->id;
	}

	/**
	* Set Title.
	*
	* @param	string	$a_title	Title of news item.
	*/
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title of news item.
	*/
	public function getTitle()
	{
		return $this->title;
	}

	/**
	* Set Content.
	*
	* @param	string	$a_content	Content of news.
	*/
	public function setContent($a_content)
	{
		$this->content = $a_content;
	}

	/**
	* Get Content.
	*
	* @return	string	Content of news.
	*/
	public function getContent()
	{
		return $this->content;
	}


	/**
	* Set CreationDate.
	*
	* @param	string	$a_creation_date	Date of creation.
	*/
	public function setCreationDate($a_creation_date)
	{
		$this->creation_date = $a_creation_date;
	}

	/**
	* Get CreationDate.
	*
	* @return	string	Date of creation.
	*/
	public function getCreationDate()
	{
		return $this->creation_date;
	}

	/**
	* Set UpdateDate.
	*
	* @param	string	$a_update_date	Date of last update.
	*/
	public function setUpdateDate($a_update_date)
	{
		$this->update_date = $a_update_date;
	}

	/**
	* Get UpdateDate.
	*
	* @return	string	Date of last update.
	*/
	public function getUpdateDate()
	{
		return $this->update_date;
	}

	/**
	* Set Visibility.
	*
	* @param	string	$a_visibility	Access level of news.
	*/
	public function setVisibility($a_visibility = 'users')
	{
		$this->visibility = $a_visibility;
	}

	/**
	* Get Visibility.
	*
	* @return	string	Access level of news.
	*/
	public function getVisibility()
	{
		return $this->visibility;
	}

	/**
	* Set UserId.
	*
	* @param	int	$a_user_id	
	*/
	public function setUserId($a_user_id)
	{
		$this->user_id = $a_user_id;
	}

	/**
	* Get UserId.
	*
	* @return	int	
	*/
	public function getUserId()
	{
		return $this->user_id;
	}
	
	/**
	* Create new item.
	*
	*/
	public function create()
	{
		global $ilDB;
		
		$createdate = new ilDateTime(time(), IL_CAL_UNIX);
		
		$next_id = $ilDB->nextId('payment_news');

		$res = $ilDB->manipulateF('
			INSERT INTO payment_news 
				  (news_id, news_title, news_content, visibility, creation_date, update_date, user_id)
			VALUES (%s, %s, %s, %s, %s, %s, %s)',
			array('integer', 'text', 'text', 'text', 'timestamp', 'timestamp', 'integer'),
			array($next_id, $this->getTitle(), $this->getContent(), $this->getVisibility(),
				$createdate->get(IL_CAL_DATETIME), $createdate->get(IL_CAL_DATETIME),$this->getUserId())
			);


		


		if((int)($id = $ilDB->nextId('payment_news')))
		{
			$this->setID((int)$id);
			return true;
		}	
		
		return false;
	}

	/**
	* Read item from database.
	*
	*/
	private function read()
	{	
		$result = $this->db->queryf('SELECT * FROM payment_news WHERE news_id = %s',
		        				array('integer'),
		        				array($this->getId())
		        			);
		
		while($record = $this->db->fetchAssoc($result))
		{
			$this->setTitle($record['news_title']);
			$this->setCreationDate($record['creation_date']);
			$this->setVisibility($record['visibility']);
			$this->setContent($record['content']);
			$this->setUpdateDate($record['update_date']);
			$this->setUserId($record['user_id']);
			break;
		}
	}

	/**
	* Update item in database.
	*
	*/
	public function update()
	{		
		$updatedate = new ilDateTime(time(), IL_CAL_UNIX);	
		vd($updatedate->get(IL_CAL_DATETIME));
		if((int)$this->getId())
		{
			$query = 'UPDATE payment_news 
					  SET
					  news_title = %s,
					  news_content = %s,
					  visibility = %s,
					  update_date = %s,
					  user_id = %s
					  WHERE news_id = %s';
			$statement = $this->db->manipulateF($query, 
			        array('text', 'text', 'text', 'timestamp', 'integer', 'integer'),
			        array($this->getTitle(), $this->getContent(), $this->getVisibility(),
						  $updatedate->get(IL_CAL_DATETIME), $this->getUserId(), $this->getId())
			);        
						  
			return true;
		}
		
		return false;
	}

	/**
	* Delete item from database.
	*
	*/
	public function delete()
	{
		if((int)$this->getId())
		{
			$query = 'DELETE FROM payment_news WHERE news_id = %s';
			$statement = $this->db->manipulateF($query, array('integer'),array($this->getId()));
		
			return true;
		}
		
		return false;
	}
}
?>