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
		$this->title = ilWikiUtil::makeDbTitle($a_title);
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
		
		// get other pages that link to this page
		$linking_pages = ilWikiPage::getLinksToPage($this->getWikiId(),
			$this->getId());

		// delete internal links information to this page
		include_once("./Services/COPage/classes/class.ilInternalLink.php");
		ilInternalLink::_deleteAllLinksToTarget("wpg", $this->getId());
		
		// delete comments and notes of this page
		// (we keep them first)
		
		// delete record of table il_wiki_data
		$query = "DELETE FROM il_wiki_page".
			" WHERE id = ".$ilDB->quote($this->getId());

		$ilDB->query($query);
		
		// delete co page
		parent::delete();
		
		// make links of other pages to this page a missing link
		foreach($linking_pages as $lp)
		{
			$st = $ilDB->prepareManip("REPLACE INTO il_wiki_missing_page ".
				"(wiki_id, source_id, target_name) VALUES ".
				"(?,?,?)", array("integer", "integer", "text"));
			$ilDB->execute($st, array($this->getWikiId(), $lp["id"],
				$this->getTitle()));
		}

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
		
		$a_title = ilWikiUtil::makeDbTitle($a_title);
		
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
		
		$a_title = ilWikiUtil::makeDbTitle($a_title);
		
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
			if ($source["type"] == "wpg:pg")
			{
				$ids[] = $source["id"];
			}
		}
		// get wiki page record
		$query = "SELECT * FROM il_wiki_page wp, page_object p".
			" WHERE wp.id IN (".implode(",",ilUtil::quoteArray($ids)).")".
			" AND wp.id = p.page_id AND p.parent_type = 'wpg'".
			" AND wp.wiki_id = ".$ilDB->quote($a_wiki_id).
			" ORDER BY title";
		$set = $ilDB->query($query);
		
		$pages = array();
		while ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$pages[] = array_merge($rec, array("user" => $rec["last_change_user"],
				"date" => $rec["last_change"]));
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
				if ($source["type"] == "wpg:pg")
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
		
		$a_title = ilWikiUtil::makeDbTitle($a_title);
		
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
	* Get all contributors of wiki
	*
	* @access	public
	*/
	static function getPageContributors($a_page_id)
	{
		global $ilDB;
		
		$contributors = parent::getPageContributors("wpg", $a_page_id);
		
		return $contributors;
	}


	/**
	* save internal links of page
	*
	* @param	string		xml page code
	*/
	function saveInternalLinks($a_xml)
	{
		global $ilDB;
		
		
		// *** STEP 1: Standard Processing ***
		
		parent::saveInternalLinks($a_xml);
		
		
		// *** STEP 2: Other Pages -> This Page ***
		
		// Check, whether ANOTHER page links to this page as a "missing" page
		// (this is the case, when this page is created newly)
		$stmt = $ilDB->prepare("SELECT * FROM il_wiki_missing_page WHERE ".
			" wiki_id = ? AND target_name = ?", array("integer", "text"));
		$set = $ilDB->execute($stmt, array($this->getWikiId(), $this->getTitle()));
		while ($anmiss = $ilDB->fetchAssoc($set))	// insert internal links instead 
		{
			ilInternalLink::_saveLink("wpg:pg", $anmiss["source_id"], "wpg",
				$this->getId(), 0);
		}
		
		// now remove the missing page entries
		$stmt = $ilDB->prepareManip("DELETE FROM il_wiki_missing_page WHERE ".
			" wiki_id = ? AND target_name = ?", array("integer", "text"));
		$ilDB->execute($stmt, array($this->getWikiId(), $this->getTitle()));
		
		
		// *** STEP 3: This Page -> Other Pages ***
		
		// remove the exising "missing page" links for THIS page (they will be re-inserted below)
		$stmt = $ilDB->prepareManip("DELETE FROM il_wiki_missing_page WHERE ".
			" wiki_id = ? AND source_id = ?", array("integer", "integer"));
		$ilDB->execute($stmt, array($this->getWikiId(), $this->getId()));
		
		// collect the wiki links of the page
		include_once("./Modules/Wiki/classes/class.ilWikiUtil.php");
		$int_wiki_links = ilWikiUtil::collectInternalLinks($a_xml, $this->getWikiId(), true);

		foreach($int_wiki_links as $wlink)
		{
			$page_id = ilWikiPage::_getPageIdForWikiTitle($this->getWikiId(), $wlink);
			
			if ($page_id > 0)		// save internal link for existing page
			{
				ilInternalLink::_saveLink("wpg:pg", $this->getId(), "wpg",
					$page_id, 0);
			}
			else		// save missing link for non-existing page
			{
				$stmt = $ilDB->prepareManip("REPLACE INTO il_wiki_missing_page (wiki_id, source_id, target_name)".
					" VALUES (?,?,?)", array("integer", "integer", "text"));
				$ilDB->execute($stmt, array($this->getWikiId(), $this->getId(), $wlink));
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
