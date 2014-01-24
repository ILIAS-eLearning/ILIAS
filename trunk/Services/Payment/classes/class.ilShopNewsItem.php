<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id$
*/
class ilShopNewsItem 
{
	private $id = 0;
	private $title = '';
	private $content = '';
	private $creation_date = null;
	private $update_date = null;
	private $user_id = 0;
	private $visibility = 'users';

	/**
	* Constructor.
	*
	* @param	int	$a_id	
	*/
	public function __construct($a_id = 0)
	{		
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
		if((int)$next_id)
		{
			$ilDB->insert('payment_news', array(
				'news_id'			=> array('integer', $next_id),
				'news_title'		=> array('text', $this->getTitle()),
				'news_content'		=> array('clob', $this->getContent()),
				'visibility'		=> array('text', $this->getVisibility()),
				'creation_date'		=> array('timestamp', $createdate->get(IL_CAL_DATETIME)),
				'update_date'		=> array('timestamp', $createdate->get(IL_CAL_DATETIME)),
				'user_id'			=> array('integer', $this->getUserId())		
			));
			$this->id = $next_id;
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
		global $ilDB;
			
		$result = $ilDB->queryf('SELECT * FROM payment_news WHERE news_id = %s',
		        				array('integer'),
		        				array($this->getId())
		        			);
		
		while($record = $ilDB->fetchAssoc($result))
		{
			$this->setTitle($record['news_title']);
			$this->setCreationDate($record['creation_date']);
			$this->setVisibility($record['visibility']);
			$this->setContent($record['news_content']);
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
		global $ilDB;
		
		$updatedate = new ilDateTime(time(), IL_CAL_UNIX);	

		if((int)$this->getId())
		{
			$ilDB->update('payment_news', 
				array(				
					'news_title'		=> array('text', $this->getTitle()),
					'news_content'		=> array('clob', $this->getContent()),
					'visibility'		=> array('text', $this->getVisibility()),
					'update_date'		=> array('timestamp', $updatedate->get(IL_CAL_DATETIME)),
					'user_id'			=> array('integer', $this->getUserId())		
				),
				array(
					'news_id'			=> array('integer', $this->getId())
				)				
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
		global $ilDB;
		
		if((int)$this->getId())
		{
			$query = 'DELETE FROM payment_news WHERE news_id = %s';
			$statement = $ilDB->manipulateF($query, array('integer'),array($this->getId()));
		
			return true;
		}
		
		return false;
	}
}
?>