<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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

		$id = $ilDB->nextId("il_wiki_page");
		$this->setId($id);
		$query = "INSERT INTO il_wiki_page (".
			"id".
			", title".
			", wiki_id".
			" ) VALUES (".
			$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote($this->getTitle(), "text")
			.",".$ilDB->quote($this->getWikiId(), "integer")
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
		
		// update wiki page data
		$query = "UPDATE il_wiki_page SET ".
			" title = ".$ilDB->quote($this->getTitle(), "text").
			",wiki_id = ".$ilDB->quote($this->getWikiId(), "integer").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
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
			$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

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
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");

		$ilDB->manipulate($query);
		
		// delete co page
		parent::delete();
		
		// make links of other pages to this page a missing link
		foreach($linking_pages as $lp)
		{
			$ilDB->manipulateF("DELETE FROM il_wiki_missing_page ".
				" WHERE wiki_id = %s AND source_id = %s AND target_name = %s ",
				array("integer", "integer", "text"),
				array($this->getWikiId(), $lp["id"], $this->getTitle()));
			$ilDB->manipulateF("INSERT INTO il_wiki_missing_page ".
				"(wiki_id, source_id, target_name) VALUES ".
				"(%s,%s,%s)",
				array("integer", "integer", "text"),
				array($this->getWikiId(), $lp["id"], $this->getTitle()));
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
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer");
		$set = $ilDB->query($query);
		
		while($rec = $ilDB->fetchAssoc($set))
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
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND title = ".$ilDB->quote($a_title, "text");
		$set = $ilDB->query($query);
		if($rec = $ilDB->fetchAssoc($set))
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
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND title = ".$ilDB->quote($a_title, "text");
		$set = $ilDB->query($query);
		if($rec = $ilDB->fetchAssoc($set))
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
			" WHERE id = ".$ilDB->quote($a_page_id, "integer");
		$set = $ilDB->query($query);
		if($rec = $ilDB->fetchAssoc($set))
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
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
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
			" WHERE ".$ilDB->in("wp.id", $ids, false, "integer").
			" AND wp.id = p.page_id AND p.parent_type = ".$ilDB->quote("wpg", "text").
			" AND wp.wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" ORDER BY title";
		$set = $ilDB->query($query);
		
		$pages = array();
		while ($rec = $ilDB->fetchAssoc($set))
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
				" WHERE ".$ilDB->in("id", $ids, false, "integer").
				" AND wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
				" ORDER BY title";
			$set = $ilDB->query($query);
			$rec = $ilDB->fetchAssoc($set);
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
		
		$query = "SELECT id FROM il_wiki_page".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND title = ".$ilDB->quote($a_title, "text");
		$set = $ilDB->query($query);
		
		$pages = array();
		if ($rec = $ilDB->fetchAssoc($set))
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
		$set = $ilDB->queryF("SELECT * FROM il_wiki_missing_page WHERE ".
			" wiki_id = %s AND target_name = %s",
			array("integer", "text"),
			array($this->getWikiId(), $this->getTitle()));
		while ($anmiss = $ilDB->fetchAssoc($set))	// insert internal links instead 
		{
			ilInternalLink::_saveLink("wpg:pg", $anmiss["source_id"], "wpg",
				$this->getId(), 0);
		}
		
		// now remove the missing page entries
		$ilDB->manipulateF("DELETE FROM il_wiki_missing_page WHERE ".
			" wiki_id = %s AND target_name = %s",
			array("integer", "text"),
			array($this->getWikiId(), $this->getTitle()));
		
		
		// *** STEP 3: This Page -> Other Pages ***
		
		// remove the exising "missing page" links for THIS page (they will be re-inserted below)
		$ilDB->manipulateF("DELETE FROM il_wiki_missing_page WHERE ".
			" wiki_id = %s AND source_id = %s",
			array("integer", "integer"),
			array($this->getWikiId(), $this->getId()));
		
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
				$ilDB->manipulateF("DELETE FROM il_wiki_missing_page WHERE".
					" wiki_id = %s AND source_id = %s AND target_name = %s",
					array("integer", "integer", "text"),
					array($this->getWikiId(), $this->getId(), $wlink));
				$ilDB->manipulateF("INSERT INTO il_wiki_missing_page (wiki_id, source_id, target_name)".
					" VALUES (%s,%s,%s)",
					array("integer", "integer", "text"),
					array($this->getWikiId(), $this->getId(), $wlink));
			}
		}
	}

	/**
	* Checks whether a page with given title exists
	*/
	static function _getPageIdForWikiTitle($a_wiki_id, $a_title)
	{
		global $ilDB;
		
		$query = "SELECT id FROM il_wiki_page".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND title = ".$ilDB->quote($a_title, "text");
		$set = $ilDB->query($query);
		if($rec = $ilDB->fetchAssoc($set))
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
		
		$query = "SELECT wp.*, po.view_cnt as cnt FROM il_wiki_page wp, page_object po".
			" WHERE wp.wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND wp.id = po.page_id ".
			" AND po.parent_type = ".$ilDB->quote("wpg", "text")." ".
			" ORDER BY po.view_cnt";
		$set = $ilDB->query($query);
		
		$pages = array();
		while($rec = $ilDB->fetchAssoc($set))
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
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer");
		$s = $ilDB->query($query);
		$r = $ilDB->fetchAssoc($s);
		
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
		$ilDB->setLimit(1, $rand);
		$query = "SELECT title FROM il_wiki_page".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer");
		$s = $ilDB->query($query);
		$r = $ilDB->fetchAssoc($s);
		
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
