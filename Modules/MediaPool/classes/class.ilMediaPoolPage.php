<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
* Class ilMediaPoolPage
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
* @ingroup ModulesMediaPool
*/
class ilMediaPoolPage extends ilPageObject
{
	/**
	* Constructor
	* @access	public
	* @param	media_pool page id
	*/
	function __construct($a_id = 0, $a_old_nr = 0)
	{
		parent::__construct("mep", $a_id, $a_old_nr);
	}

	/**
	* Set Title.
	*
	* @param	string	$a_title	Title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set media pool id
	 *
	 * @param	int	media pool id
	 */
	function setMediaPoolId($a_val)
	{
		$this->media_pool_id = $a_val;
	}
	
	/**
	 * Get media pool id
	 *
	 * @return	int	media pool id
	 */
	function getMediaPoolId()
	{
		return $this->media_pool_id;
	}

	/**
	* Create new media pool page
	*/
	function create()
	{
		global $ilDB;

		$id = $ilDB->nextId("il_media_pool_page");
		$this->setId($id);
		$query = "INSERT INTO il_media_pool_page (".
			"id".
			", title".
			", media_pool_id".
			" ) VALUES (".
			$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote($this->getTitle(), "text")
			.",".$ilDB->quote($this->getMediaPoolId(), "integer")
			.")";
		$ilDB->manipulate($query);
		
		// create page object
		parent::create();
		
		$this->saveInternalLinks($this->getXMLContent());
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update($a_validate = true, $a_no_history = false)
	{
		global $ilDB;
		
		// update media_pool page data
		$query = "UPDATE il_media_pool_page SET ".
			" title = ".$ilDB->quote($this->getTitle(), "text").
			",media_pool_id = ".$ilDB->quote($this->getMediaPoolId(), "integer").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
		parent::update($a_validate, $a_no_history);

		return true;
	}
	
	/**
	* Read media_pool data
	*/
	function read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_media_pool_page WHERE id = ".
			$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setTitle($rec["title"]);
		$this->setMediaPoolId($rec["media_pool_id"]);
		
		// get co page
		parent::read();
	}


	/**
	* delete media_pool page and al related data	
	*
	* @access	public
	*/
	function delete()
	{
		global $ilDB;
		

		// delete internal links information to this page
//		include_once("./Services/COPage/classes/class.ilInternalLink.php");
//		ilInternalLink::_deleteAllLinksToTarget("mep", $this->getId());
				
		// delete record of table il_media_pool_data
		$query = "DELETE FROM il_media_pool_page".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");

		$ilDB->manipulate($query);
		
		// delete co page
		parent::delete();

		return true;
	}

	/**
	* delete media pool page and al related data	
	*
	* @access	public
	*/
	static function deleteAllPagesOfMediaPool($a_media_pool_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_media_pool_page".
			" WHERE media_pool_id = ".$ilDB->quote($a_media_pool_id, "integer");
		$set = $ilDB->query($query);
		
		while($rec = $ilDB->fetchAssoc($set))
		{
			$mp_page = new ilMediaPoolPage($rec["id"]);
			$mp_page->delete();
		}
	}
	
	/**
	* Checks whether a page with given title exists
	*/
	static function exists($a_media_pool_id, $a_title)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_media_pool_page".
			" WHERE media_pool_id = ".$ilDB->quote($a_media_pool_id, "integer").
			" AND title = ".$ilDB->quote($a_title, "text");
		$set = $ilDB->query($query);
		if($rec = $ilDB->fetchAssoc($set))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	* Lookup title
	*/
	static function lookupTitle($a_page_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_media_pool_page".
			" WHERE id = ".$ilDB->quote($a_page_id, "integer");
		$set = $ilDB->query($query);
		if($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["title"];
		}
		
		return false;
	}

	/**
	* Get all pages of media pool
	*
	* @access	public
	*/
	static function getAllPages($a_media_pool_id)
	{
		global $ilDB;
		
		$pages = parent::getAllPages("mep", $a_media_pool_id);
		
		$query = "SELECT * FROM il_media_pool_page".
			" WHERE media_pool_id = ".$ilDB->quote($a_media_pool_id, "integer").
			" ORDER BY title";
		$set = $ilDB->query($query);
		
		while($rec = $ilDB->fetchAssoc($set))
		{
			if (isset($pages[$rec["id"]]))
			{
				$pages[$rec["id"]]["title"] = $rec["title"];
			}
		}
		
		return $pages;
	}

	/**
	* Check whether page exists in media pool or not	
	*
	* @param	int		media pool id
	* @param	string	page name
	* @return	boolean	page exists true/false
	*/
	static function _mediaPoolPageExists($a_media_pool_id, $a_title)
	{
		global $ilDB;
		
		$query = "SELECT id FROM il_media_pool_page".
			" WHERE media_pool_id = ".$ilDB->quote($a_media_pool_id, "integer").
			" AND title = ".$ilDB->quote($a_title, "text");
		$set = $ilDB->query($query);
		
		$pages = array();
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return true;
		}
		
		return false;
	}
	
}
?>
