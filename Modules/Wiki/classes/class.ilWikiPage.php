<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
* Class ilWikiPage
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
* @ingroup ModulesWiki
*/
class ilWikiPage extends ilPageObject
{
	/**
	* Constructor
	* @access	public
	* @param	wiki page id
	*/
	function __construct($a_id = 0, $a_old_nr = 0)
	{
		parent::__construct("wpg", $a_id, $a_old_nr);
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
	* Set Wiki Object Id.
	*
	* @param	int	$a_wikiid	Wiki Object Id
	*/
	function setWikiId($a_wikiid)
	{
		$this->setParentId($a_wikiid);
	}

	/**
	* Get Wiki Object Id.
	*
	* @return	int	Wiki Object Id
	*/
	function getWikiId()
	{
		return $this->getParentId();
	}

	/**
	* Create new wiki page
	*/
	function create()
	{
		global $ilDB;

		$query = "INSERT INTO il_wiki_page (".
			"title".
			", wiki_id".
			" ) VALUES (".
			$ilDB->quote($this->getTitle())
			.",".$ilDB->quote($this->getWikiId())
			.")";
		$ilDB->query($query);
		
		$id = $ilDB->getLastInsertId();
		$this->setId($id);
		
		// create page object
		parent::create();
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
		
		// update wiki page data
		$query = "UPDATE il_wiki_page SET ".
			" title = ".$ilDB->quote($this->getTitle()).
			",wiki_id = ".$ilDB->quote($this->getWikiId()).
			" WHERE id = ".$ilDB->quote($this->getId());
		$ilDB->query($query);
		parent::update($a_validate, $a_no_history);

		return true;
	}
	
	/**
	* Read wiki data
	*/
	function read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_wiki_page WHERE id = ".
			$ilDB->quote($this->getId());
		$set = $ilDB->query($query);
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);

		$this->setTitle($rec["title"]);
		$this->setWikiId($rec["wiki_id"]);
		
		// get co page
		parent::read();
	}


	/**
	* delete wiki page and al related data	
	*
	* @access	public
	*/
	function delete()
	{
		global $ilDB;
		
		// delete record of table il_wiki_data
		$query = "DELETE FROM il_wiki_page".
			" WHERE id = ".$ilDB->quote($this->getId());

		$ilDB->query($query);
		
		// delete co page
		parent::delete();
		
		return true;
	}

	/**
	* delete wiki page and al related data	
	*
	* @access	public
	*/
	static function deleteAllPagesOfWiki($a_wiki_id)
	{
		global $ilDB;
		
		// delete record of table il_wiki_data
		$query = "SELECT * FROM il_wiki_page".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id);
		$set = $ilDB->query($query);
		
		while($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$wiki_page = new ilWikiPage($rec["id"]);
			$wiki_page->delete();
		}
	}
	
	/**
	* Checks whether a page with given title exists
	*/
	static function exists($a_wiki_id, $a_title)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_wiki_page".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id).
			" AND title = ".$ilDB->quote($a_title);
		$set = $ilDB->query($query);
		if($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return true;
		}
		
		return false;
	}

	/**
	* Get wiki page object for id and title
	*/
	static function getPageIdForTitle($a_wiki_id, $a_title)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_wiki_page".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id).
			" AND title = ".$ilDB->quote($a_title);
		$set = $ilDB->query($query);
		if($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $rec["id"];
		}
		
		return false;
	}
	
	/**
	* Checks whether a page with given title exists
	*/
	static function lookupTitle($a_page_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_wiki_page".
			" WHERE id = ".$ilDB->quote($a_page_id);
		$set = $ilDB->query($query);
		if($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $rec["title"];
		}
		
		return false;
	}

	/**
	* Get all pages of wiki	
	*
	* @access	public
	*/
	static function getAllPages($a_wiki_id)
	{
		global $ilDB;
		
		$pages = parent::getAllPages("wpg", $a_wiki_id);
		
		$query = "SELECT * FROM il_wiki_page".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id).
			" ORDER BY title";
		$set = $ilDB->query($query);
		
		while($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if (isset($pages[$rec["id"]]))
			{
				$pages[$rec["id"]]["title"] = $rec["title"];
			}
		}
		
		return $pages;
	}

	/**
	* Get links to a page	
	*/
	static function getLinksToPage($a_wiki_id, $a_page_id)
	{
		global $ilDB;
		
		include_once("./Services/COPage/classes/class.ilInternalLink.php");
		$sources = ilInternalLink::_getSourcesOfTarget("wpg", $a_page_id, 0);
		
		$ids = array();
		foreach ($sources as $source)
		{ 
			if ($source["type"] == "wpg")
			{
				$ids[] = $source["id"];
			}
		}
		// delete record of table il_wiki_data
		$query = "SELECT * FROM il_wiki_page".
			" WHERE id IN (".implode(",",ilUtil::quoteArray($ids)).")".
			" AND wiki_id = ".$ilDB->quote($a_wiki_id).
			" ORDER BY title";
		$set = $ilDB->query($query);
		
		$pages = array();
		while ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$pages[] = $rec;
		}
		
		return $pages;
	}

	/**
	* Get orphaned pages of wiki	
	*
	* @access	public
	*/
	static function getOrphanedPages($a_wiki_id)
	{
		global $ilDB;
		
		$pages = ilWikiPage::getAllPages($a_wiki_id);
		
		include_once("./Services/COPage/classes/class.ilInternalLink.php");
		
		$orphaned = array();
		foreach ($pages as $k => $page)
		{
			$sources = ilInternalLink::_getSourcesOfTarget("wpg", $page["id"], 0);
			
			$ids = array();
			foreach ($sources as $source)
			{ 
				if ($source["type"] == "wpg")
				{
					$ids[] = $source["id"];
				}
			}
			// delete record of table il_wiki_data
			$query = "SELECT count(*) AS cnt FROM il_wiki_page".
				" WHERE id IN (".implode(",",ilUtil::quoteArray($ids)).")".
				" AND wiki_id = ".$ilDB->quote($a_wiki_id).
				" ORDER BY title";
			$set = $ilDB->query($query);
			$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
			if ($rec["cnt"] == 0 &&
				ilObjWiki::_lookupStartPage($a_wiki_id) != $page["title"])
			{
				$orphaned[] = $page;
			}
		}
		
		return $orphaned;
	}

	/**
	* Check whether page exists for wiki or not	
	*
	* @access	public
	*/
	static function _wikiPageExists($a_wiki_id, $a_title)
	{
		global $ilDB;
		
		// delete record of table il_wiki_data
		$query = "SELECT * FROM il_wiki_page".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id).
			" AND title = ".$ilDB->quote($a_title);
		$set = $ilDB->query($query);
		
		$pages = array();
		if ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	* Get all contributors of wiki
	*
	* @access	public
	*/
	static function getParentObjectContributors($a_wiki_id)
	{
		global $ilDB;
		
		$contributors = parent::getParentObjectContributors("wpg", $a_wiki_id);
		
		return $contributors;
	}

	/**
	* save internal links of page
	*
	* @param	string		xml page code
	*/
	function saveInternalLinks($a_xml)
	{
		parent::saveInternalLinks($a_xml);
		include_once("./Modules/Wiki/classes/class.ilWikiUtil.php");
		$int_wiki_links = ilWikiUtil::collectInternalLinks($a_xml, $this->getWikiId());
		foreach($int_wiki_links as $wlink)
		{
			$page_id = ilWikiPage::_getPageIdForWikiTitle($this->getWikiId(), $wlink);
			
			if ($page_id > 0)
			{
				ilInternalLink::_saveLink("wpg", $this->getId(), "wpg",
					$page_id, 0);
			}
		}
	}

	/**
	* Checks whether a page with given title exists
	*/
	static function _getPageIdForWikiTitle($a_wiki_id, $a_title)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_wiki_page".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id).
			" AND title = ".$ilDB->quote($a_title);
		$set = $ilDB->query($query);
		if($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $rec["id"];
		}
		
		return false;
	}

	/**
	* Get popular pages of wiki	
	*
	* @access	public
	*/
	static function getPopularPages($a_wiki_id)
	{
		global $ilDB;
		
		$query = "SELECT wp.*, po.view_cnt as cnt FROM il_wiki_page as wp, page_object as po".
			" WHERE wp.wiki_id = ".$ilDB->quote($a_wiki_id).
			" AND wp.id = po.page_id ".
			" AND po.parent_type = 'wpg' ".
			" ORDER BY po.view_cnt";
		$set = $ilDB->query($query);
		
		$pages = array();
		while($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$pages[] = $rec;
		}
		
		return $pages;
	}

	/**
	* Count pages of wiki
	*
	* @param	int		$a_wiki_id		Wiki ID
	*/
	static function countPages($a_wiki_id)
	{
		global $ilDB;
		
		// delete record of table il_wiki_data
		$query = "SELECT count(*) as cnt FROM il_wiki_page".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id);
		$s = $ilDB->query($query);
		$r = $s->fetchRow(DB_FETCHMODE_ASSOC);
		
		return $r["cnt"];
	}

	/**
	* Get a random page	
	*
	* @param	int		$a_wiki_id		Wiki ID
	*/
	static function getRandomPage($a_wiki_id)
	{
		global $ilDB;
		
		$cnt = ilWikiPage::countPages($a_wiki_id);
		
		if ($cnt < 1)
		{
			return "";
		}
		
		$rand = rand(1, $cnt);
		
		// delete record of table il_wiki_data
		$query = "SELECT title FROM il_wiki_page".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id).
			" LIMIT $rand, 1";
		$s = $ilDB->query($query);
		$r = $s->fetchRow(DB_FETCHMODE_ASSOC);
		
		return $r["title"];
	}

	/**
	* Get all pages of wiki	
	*
	* @access	public
	*/
	static function getNewPages($a_wiki_id)
	{
		global $ilDB;
		
		$pages = parent::getNewPages("wpg", $a_wiki_id);
		
		foreach($pages as $k => $page)
		{
			$pages[$k]["title"] = ilWikiPage::lookupTitle($page["id"]);
		}
		
		return $pages;
	}

} // END class.ilWikiPage
?>
