<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");
include_once("./Modules/Wiki/classes/class.ilWikiUtil.php");
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
	protected $blocked = false;
	protected $rating = false; // [boo,]
	protected $hide_adv_md = false; // [bool]

	/**
	 * Get parent type
	 *
	 * @return string parent type
	 */
	function getParentType()
	{
		return "wpg";
	}	

	/**
	 * After constructor
	 *
	 * @param
	 * @return
	 */
	function afterConstructor()
	{
		$this->getPageConfig()->configureByObjectId($this->getParentId());
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
	* Set Wiki Ref Id.
	*
	* @param	int	$a_wiki_ref_id	Wiki Ref Id
	*/
	function setWikiRefId($a_wiki_ref_id)
	{
		$this->parent_ref_id = $a_wiki_ref_id;
	}

	/**
	* Get Wiki Ref Id.
	*
	* @return	int	Wiki Ref Id
	*/
	function getWikiRefId()
	{
		return $this->parent_ref_id;
	}

	/**
	 * Set blocked
	 *
	 * @param	boolean	$a_val	blocked
	 */
	public function setBlocked($a_val)
	{
		$this->blocked = $a_val;
	}

	/**
	 * Get blocked
	 *
	 * @return	boolean	blocked
	 */
	public function getBlocked()
	{
		return $this->blocked;
	}
	
	/**
	 * Set rating
	 *
	 * @param	boolean	$a_val	
	 */
	public function setRating($a_val)
	{
		$this->rating = (bool)$a_val;
	}

	/**
	 * Get rating
	 *
	 * @return	boolean	
	 */
	public function getRating()
	{
		return $this->rating;
	}
	
	/**
	 * Toggle adv md visibility
	 *
	 * @param	boolean	$a_val	
	 */
	public function hideAdvancedMetadata($a_val)
	{
		$this->hide_adv_md = (bool)$a_val;
	}

	/**
	 * Get adv md visibility status 
	 *
	 * @return	boolean	
	 */
	public function isAdvancedMetadataHidden()
	{
		return $this->hide_adv_md;
	}

	/**
	 * Create page from xml
	 */
	function createFromXML()
	{
		global $ilDB;

		// ilWikiDataset creates wiki pages without copage objects
		// (see create function in this class, parameter $a_prevent_page_creation)
		// The ilCOPageImporter will call createFromXML without running through the read
		// method -> we will miss the important wiki id, thus we read it now
		// see also bug #12224
		$set = $ilDB->query("SELECT id FROM il_wiki_page ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			$this->read(true);
		}

		parent::createFromXML();
	}

	/**
	* Create new wiki page
	*/
	function create($a_prevent_page_creation = false)
	{
		global $ilDB;

		$id = $ilDB->nextId("il_wiki_page");
		$this->setId($id);
		$query = "INSERT INTO il_wiki_page (".
			"id".
			", title".
			", wiki_id".
			", blocked".
			", rating".
			", hide_adv_md".
			" ) VALUES (".
			$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote($this->getTitle(), "text")
			.",".$ilDB->quote((int) $this->getWikiId(), "integer")
			.",".$ilDB->quote((int) $this->getBlocked(), "integer")
			.",".$ilDB->quote((int) $this->getRating(), "integer")
			.",".$ilDB->quote((int) $this->isAdvancedMetadataHidden(), "integer")
			.")";
		$ilDB->manipulate($query);

		// create page object
		if (!$a_prevent_page_creation)
		{
			parent::create();
			$this->saveInternalLinks($this->getDomDoc());
			
			include_once "./Modules/Wiki/classes/class.ilWikiStat.php";
			ilWikiStat::handleEvent(ilWikiStat::EVENT_PAGE_CREATED, $this);
			
			include_once "./Services/Notification/classes/class.ilNotification.php";
			ilWikiUtil::sendNotification("new", ilNotification::TYPE_WIKI, $this->getWikiRefId(), $this->getId());
		}

		$this->updateNews();
	}
	
	public function afterUpdate($a_domdoc, $a_xml)
	{				
		// internal == wiki links	
		include_once "Modules/Wiki/classes/class.ilWikiUtil.php";
		$int_links = sizeof(ilWikiUtil::collectInternalLinks($a_xml, $this->getWikiId(), true));
		
		$xpath = new DOMXPath($a_domdoc);
	
		// external = internal + external links
		$ext_links = sizeof($xpath->query('//IntLink'));		
		$ext_links += sizeof($xpath->query('//ExtLink'));		
		
		$footnotes = sizeof($xpath->query('//Footnote'));
		
		
		// words/characters (xml)
				
		$xml = strip_tags($a_xml);	
		
		include_once "Services/Utilities/classes/class.ilStr.php";
		$num_chars = ilStr::strLen($xml);
		$num_words = sizeof(explode(" ", $xml));
						
		$page_data = array(
			"int_links" => $int_links,
			"ext_links" => $ext_links,
			"footnotes" => $footnotes,
			"num_words" => $num_words,
			"num_chars" => $num_chars
		);
		
		include_once "./Modules/Wiki/classes/class.ilWikiStat.php";
		ilWikiStat::handleEvent(ilWikiStat::EVENT_PAGE_UPDATED, $this, null, $page_data);				
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
			",wiki_id = ".$ilDB->quote((int) $this->getWikiId(), "integer").
			",blocked = ".$ilDB->quote((int) $this->getBlocked(), "integer").
			",rating = ".$ilDB->quote((int) $this->getRating(), "integer").
			",hide_adv_md = ".$ilDB->quote((int) $this->isAdvancedMetadataHidden(), "integer").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
		$updated = parent::update($a_validate, $a_no_history);

		if ($updated === true)
		{		
			include_once "./Services/Notification/classes/class.ilNotification.php";
			ilWikiUtil::sendNotification("update", ilNotification::TYPE_WIKI_PAGE, $this->getWikiRefId(), $this->getId());	
			
			$this->updateNews(true);
		}
		else
		{
			return $updated;
		}

		return true;
	}
	
	/**
	* Read wiki data
	*/
	function read($a_omit_page_read = false)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_wiki_page WHERE id = ".
			$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setTitle($rec["title"]);
		$this->setWikiId($rec["wiki_id"]);
		$this->setBlocked($rec["blocked"]);
		$this->setRating($rec["rating"]);
		$this->hideAdvancedMetadata($rec["hide_adv_md"]);
		
		// get co page
		if (!$a_omit_page_read)
		{
			parent::read();
		}
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
		include_once("./Services/Link/classes/class.ilInternalLink.php");
		ilInternalLink::_deleteAllLinksToTarget("wpg", $this->getId());
		
		include_once "./Modules/Wiki/classes/class.ilWikiStat.php";
		ilWikiStat::handleEvent(ilWikiStat::EVENT_PAGE_DELETED, $this);	

		include_once "./Services/Notification/classes/class.ilNotification.php";
		ilWikiUtil::sendNotification("delete", ilNotification::TYPE_WIKI_PAGE, $this->getWikiRefId(), $this->getId());

		// remove all notifications
		include_once "./Services/Notification/classes/class.ilNotification.php";
		ilNotification::removeForObject(ilNotification::TYPE_WIKI_PAGE, $this->getId());
		
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
		
		$query = "SELECT id FROM il_wiki_page".
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
	 * Checks whether a page with given title exists
	 */
	static function getIdForPageTitle($a_wiki_id, $a_title)
	{
		global $ilDB;

		$a_title = ilWikiUtil::makeDbTitle($a_title);

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
	 * Lookup wiki id
	 */
	static function lookupWikiId($a_page_id)
	{
		global $ilDB;

		$query = "SELECT wiki_id FROM il_wiki_page".
			" WHERE id = ".$ilDB->quote($a_page_id, "integer");
		$set = $ilDB->query($query);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["wiki_id"];
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

		$pg = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			if (isset($pages[$rec["id"]]))
			{
				$pg[$rec["id"]] = $pages[$rec["id"]];
				$pg[$rec["id"]]["title"] = $rec["title"];
			}
		}

		return $pg;
	}

	/**
	* Get links to a page	
	*/
	static function getLinksToPage($a_wiki_id, $a_page_id)
	{
		global $ilDB;
		
		include_once("./Services/Link/classes/class.ilInternalLink.php");
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
		
		include_once("./Services/Link/classes/class.ilInternalLink.php");
		
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
			$query = "SELECT count(*) cnt FROM il_wiki_page".
				" WHERE ".$ilDB->in("id", $ids, false, "integer").
				" AND wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
				" GROUP BY wiki_id";
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
	function saveInternalLinks($a_domdoc)
	{
		global $ilDB;
		
		
		// *** STEP 1: Standard Processing ***
		
		parent::saveInternalLinks($a_domdoc);
		
		
		// *** STEP 2: Other Pages -> This Page ***
		
		// Check, whether ANOTHER page links to this page as a "missing" page
		// (this is the case, when this page is created newly)
		$set = $ilDB->queryF("SELECT * FROM il_wiki_missing_page WHERE ".
			" wiki_id = %s AND target_name = %s",
			array("integer", "text"),
			array($this->getWikiId(), ilWikiUtil::makeDbTitle($this->getTitle())));
		while ($anmiss = $ilDB->fetchAssoc($set))	// insert internal links instead 
		{
//echo "adding link";
			ilInternalLink::_saveLink("wpg:pg", $anmiss["source_id"], "wpg",
				$this->getId(), 0);
		}
//exit;
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
		$xml = $a_domdoc->saveXML();
		$int_wiki_links = ilWikiUtil::collectInternalLinks($xml, $this->getWikiId(), true);
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


	/**
	 * returns the wiki/object id to a given page id
	 * 
	 * @param $a_page_id
	 * @return int the object id
	 */
	public static function lookupObjIdByPage($a_page_id)
	{
		global $ilDB;
		
		$query = "SELECT wiki_id FROM il_wiki_page".
			" WHERE id = ".$ilDB->quote($a_page_id, "integer");
		$set = $ilDB->query($query);
		if($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["wiki_id"];
		}
		
		return false;		
	}

	/**
	 * Rename page
	 */
	function rename($a_new_name)
	{
		global $ilDB;
		
		// replace unallowed characters
		$a_new_name = str_replace(array("<", ">"), '', $a_new_name);

		// replace multiple whitespace characters by one single space
		$a_new_name = trim(preg_replace('!\s+!', ' ', $a_new_name));
				
		$page_title = ilWikiUtil::makeDbTitle($a_new_name);
		$pg_id = ilWikiPage::_getPageIdForWikiTitle($this->getWikiId(), $page_title);
		
		$xml_new_name = str_replace("&", "&amp;", $a_new_name);

		if ($pg_id == 0 || $pg_id == $this->getId())
		{
			include_once("./Services/Link/classes/class.ilInternalLink.php");
			$sources = ilInternalLink::_getSourcesOfTarget("wpg", $this->getId(), 0);

			foreach ($sources as $s)
			{
				if ($s["type"] == "wpg:pg" && ilPageObject::_exists("wpg", $s["id"]))
				{
					$wpage = new ilWikiPage($s["id"]);
					
					$col = ilWikiUtil::processInternalLinks($wpage->getXmlContent(), 0,
						IL_WIKI_MODE_EXT_COLLECT);
					$new_content = $wpage->getXmlContent();
					foreach ($col as $c)
					{

						// this complicated procedure is needed due to the fact
						// that depending on the collation e = é is true
						// in the (mysql) database
						// see bug http://www.ilias.de/mantis/view.php?id=11227
						$t1 = ilWikiUtil::makeDbTitle($c["nt"]->mTextform);
						$t2 = ilWikiUtil::makeDbTitle($this->getTitle());
						
						// this one replaces C2A0 (&nbsp;) by a usual space
						// otherwise the comparision will fail, since you
						// get these characters from tiny if more than one
						// space is repeated in a string. This may not be
						// 100% but we do not store $t1 anywhere and only
						// modify it for the comparison
						$t1 = preg_replace('/\xC2\xA0/', ' ', $t1);
						$t2 = preg_replace('/\xC2\xA0/', ' ', $t2);
						
						$set = $ilDB->query($q = "SELECT ".$ilDB->quote($t1, "text")." = ".$ilDB->quote($t2, "text")." isequal");
						$rec = $ilDB->fetchAssoc($set);
						
						if ($rec["isequal"])
						{
							$new_content = 
								str_replace("[[".$c["nt"]->mTextform."]]",
								"[[".$xml_new_name."]]", $new_content);
							if ($c["text"] != "")
							{
								$new_content = 
									str_replace("[[".$c["text"]."]]",
									"[[".$xml_new_name."]]", $new_content);
							}
							$add = ($c["text"] != "")
								? "|".$c["text"]
								: "";
							$new_content = 
								str_replace("[[".$c["nt"]->mTextform.$add."]]",
								"[[".$xml_new_name.$add."]]", $new_content);
						}
					}
					$wpage->setXmlContent($new_content);
//echo htmlentities($new_content);
					$wpage->update();
				}
			}

			include_once("./Modules/Wiki/classes/class.ilObjWiki.php");
			if (ilObjWiki::_lookupStartPage($this->getWikiId()) == $this->getTitle())
			{
				ilObjWiki::writeStartPage($this->getWikiId(), $a_new_name);
			}

			$this->setTitle($a_new_name);

			$this->update();
		}
		
		return $a_new_name;
	}


	/**
	 * Create
	 */
	function updateNews($a_update = false)
	{
		global $ilUser;

		$news_set = new ilSetting("news");
		$default_visibility = ($news_set->get("default_visibility") != "")
				? $news_set->get("default_visibility")
				: "users";

		include_once("./Services/News/classes/class.ilNewsItem.php");
		if (!$a_update)
		{
			$news_item = new ilNewsItem();
			$news_item->setContext(
				$this->getWikiId(), "wiki",
				$this->getId(), "wpg");
			$news_item->setPriority(NEWS_NOTICE);
			$news_item->setTitle($this->getTitle());
			$news_item->setContentTextIsLangVar(true);
			$news_item->setContent("wiki_news_page_created");
			$news_item->setUserId($ilUser->getId());
			$news_item->setVisibility($default_visibility);
			$news_item->create();
		}
		else
		{
			// get last news item of the day (if existing)
			$news_id = ilNewsItem::getLastNewsIdForContext(
				$this->getWikiId(), "wiki",
				$this->getId(), "wpg", true);

			if ($news_id > 0)
			{
				$news_item = new ilNewsItem($news_id);
				$news_item->setContent("wiki_news_page_changed");
				$news_item->setUserId($ilUser->getId());
				$news_item->setTitle($this->getTitle());
				$news_item->setContentTextIsLangVar(true);
				$news_item->update(true);
			}
			else
			{
				$news_item = new ilNewsItem();
				$news_item->setContext(
					$this->getWikiId(), "wiki",
					$this->getId(), "wpg");
				$news_item->setPriority(NEWS_NOTICE);
				$news_item->setTitle($this->getTitle());
				$news_item->setContentTextIsLangVar(true);
				$news_item->setContent("wiki_news_page_changed");
				$news_item->setUserId($ilUser->getId());
				$news_item->setVisibility($default_visibility);
				$news_item->create();
			}
		}
	}

	/**
	 * Get content for a wiki news item
	 */
	function getNewsContent()
	{
		return "12.1.1: Test User, Max";
	}

	/**
	 * Get goto href for internal wiki page link target 
	 *
	 * @param
	 * @return
	 */
	static function getGotoForWikiPageTarget($a_target, $a_offline = false)
	{
		if (!$a_offline)
		{
			$href = "./goto.php?target=wiki_wpage_".$a_target;
		}
		else
		{
			$href = ILIAS_HTTP_PATH."/goto.php?target=wiki_wpage_".$a_target;
		}
		return $href;
	}


	/**
	 * Get content templates
	 *
	 * @return array array of arrays with "id" => page id (int), "parent_type" => parent type (string), "title" => title (string)
	 */
	function getContentTemplates()
	{
		include_once("./Modules/Wiki/classes/class.ilWikiPageTemplate.php");
		$wt = new ilWikiPageTemplate($this->getWikiId());
		$templates = array();
		foreach ($wt->getAllInfo(ilWikiPageTemplate::TYPE_ADD_TO_PAGE) as $t)
		{
			$templates[] = array("id" => $t["wpage_id"], "parent_type" => "wpg", "title" => $t["title"]);
		}
		return $templates;
	}

	/**
	 * Get pages for search
	 *
	 * @param
	 * @return
	 */
	static function getPagesForSearch($a_wiki_id, $a_term)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT DISTINCT title FROM il_wiki_page".
			" WHERE wiki_id = ".$ilDB->quote($a_wiki_id, "integer").
			" AND ".$ilDB->like("title", "text", "%".$a_term."%").
			" ORDER by title");
		$res = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$res[] = $rec["title"];
		}

		return $res;
	}
	
	public static function lookupAdvancedMetadataHidden($a_page_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_wiki_page".
			" WHERE id = ".$ilDB->quote($a_page_id, "integer");
		$set = $ilDB->query($query);
		if($rec = $ilDB->fetchAssoc($set))
		{
			return (bool)$rec["hide_adv_md"];
		}
		
		return false;
	}
}
?>
