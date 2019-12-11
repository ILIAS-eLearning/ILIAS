<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ModulesWiki Modules/Wiki
 */

include_once "./Services/Object/classes/class.ilObject.php";
include_once "./Modules/Wiki/classes/class.ilWikiUtil.php";
include_once "./Services/AdvancedMetaData/interfaces/interface.ilAdvancedMetaDataSubItems.php";

/**
 * Class ilObjWiki
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesWiki
 */
class ilObjWiki extends ilObject implements ilAdvancedMetaDataSubItems
{
    /**
     * @var ilObjUser
     */
    protected $user;

    protected $online = false;
    protected $public_notes = true;
    protected $empty_page_templ = true;
    protected $link_md_values = false;

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->type = "wiki";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
    * Set Online.
    *
    * @param	boolean	$a_online	Online
    */
    public function setOnline($a_online)
    {
        $this->online = $a_online;
    }

    /**
    * Get Online.
    *
    * @return	boolean	Online
    */
    public function getOnline()
    {
        return $this->online;
    }

    /**
    * Set Enable Rating For Object.
    *
    * @param	boolean	$a_rating	Enable Rating
    */
    public function setRatingOverall($a_rating)
    {
        $this->rating_overall = (bool) $a_rating;
    }

    /**
    * Get Enable Rating For Object.
    *
    * @return	boolean	Enable Rating
    */
    public function getRatingOverall()
    {
        return $this->rating_overall;
    }
    
    /**
    * Set Enable Rating.
    *
    * @param	boolean	$a_rating	Enable Rating
    */
    public function setRating($a_rating)
    {
        $this->rating = (bool) $a_rating;
    }

    /**
    * Get Enable Rating.
    *
    * @return	boolean	Enable Rating
    */
    public function getRating()
    {
        return $this->rating;
    }
    
    /**
    * Set Enable Rating Side Block.
    *
    * @param	boolean	$a_rating
    */
    public function setRatingAsBlock($a_rating)
    {
        $this->rating_block = (bool) $a_rating;
    }

    /**
    * Get Enable Rating Side Block.
    *
    * @return	boolean
    */
    public function getRatingAsBlock()
    {
        return $this->rating_block;
    }
    
    /**
    * Set Enable Rating For New Pages.
    *
    * @param	boolean	$a_rating
    */
    public function setRatingForNewPages($a_rating)
    {
        $this->rating_new_pages = (bool) $a_rating;
    }

    /**
    * Get Enable Rating For New Pages.
    *
    * @return	boolean
    */
    public function getRatingForNewPages()
    {
        return $this->rating_new_pages;
    }
        
    /**
    * Set Enable Rating Categories.
    *
    * @param	boolean	$a_rating
    */
    public function setRatingCategories($a_rating)
    {
        $this->rating_categories = (bool) $a_rating;
    }

    /**
    * Get Enable Rating Categories.
    *
    * @return	boolean
    */
    public function getRatingCategories()
    {
        return $this->rating_categories;
    }
    
    /**
     * Set public notes
     */
    public function setPublicNotes($a_val)
    {
        $this->public_notes = $a_val;
    }

    /**
     * Get public notes
     */
    public function getPublicNotes()
    {
        return $this->public_notes;
    }

    /**
     * Set important pages
     *
     * @param	boolean	$a_val	important pages
     */
    public function setImportantPages($a_val)
    {
        $this->imp_pages = $a_val;
    }

    /**
     * Get important pages
     *
     * @return	boolean	important pages
     */
    public function getImportantPages()
    {
        return $this->imp_pages;
    }

    /**
    * Set Start Page.
    *
    * @param	string	$a_startpage	Start Page
    */
    public function setStartPage($a_startpage)
    {
        $this->startpage = ilWikiUtil::makeDbTitle($a_startpage);
    }

    /**
    * Get Start Page.
    *
    * @return	string	Start Page
    */
    public function getStartPage()
    {
        return $this->startpage;
    }

    /**
    * Set ShortTitle.
    *
    * @param	string	$a_shorttitle	ShortTitle
    */
    public function setShortTitle($a_shorttitle)
    {
        $this->shorttitle = $a_shorttitle;
    }

    /**
    * Get ShortTitle.
    *
    * @return	string	ShortTitle
    */
    public function getShortTitle()
    {
        return $this->shorttitle;
    }

    /**
    * Set Introduction.
    *
    * @param	string	$a_introduction	Introduction
    */
    public function setIntroduction($a_introduction)
    {
        $this->introduction = $a_introduction;
    }

    /**
    * Get Introduction.
    *
    * @return	string	Introduction
    */
    public function getIntroduction()
    {
        return $this->introduction;
    }

    /**
    * get ID of assigned style sheet object
    */
    public function getStyleSheetId()
    {
        return $this->style_id;
    }

    /**
    * set ID of assigned style sheet object
    */
    public function setStyleSheetId($a_style_id)
    {
        $this->style_id = $a_style_id;
    }

    /**
     * Set page toc
     *
     * @param	boolean	$a_val	page toc
     */
    public function setPageToc($a_val)
    {
        $this->page_toc = $a_val;
    }

    /**
     * Get page toc
     *
     * @return	boolean	page toc
     */
    public function getPageToc()
    {
        return $this->page_toc;
    }

    /**
     * Set empty page template
     *
     * @param boolean $a_val empty page template
     */
    public function setEmptyPageTemplate($a_val)
    {
        $this->empty_page_templ = $a_val;
    }
    
    /**
     * Get empty page template
     *
     * @return boolean empty page template
     */
    public function getEmptyPageTemplate()
    {
        return $this->empty_page_templ;
    }
    
    /**
     * Set link md values
     *
     * @param bool $a_val link metadata values
     */
    public function setLinkMetadataValues($a_val)
    {
        $this->link_md_values = $a_val;
    }
    
    /**
     * Get link md values
     *
     * @return bool link metadata values
     */
    public function getLinkMetadataValues()
    {
        return $this->link_md_values;
    }
    
    /**
     * Is wiki an online help wiki?
     *
     * @return boolean true, if current wiki is an online help wiki
     */
    public static function isOnlineHelpWiki($a_ref_id)
    {
        if ($a_ref_id > 0 && $a_ref_id == OH_REF_ID) {
            //			return true;
        }
        return false;
    }

    /**
    * Create new wiki
    */
    public function create($a_prevent_start_page_creation = false)
    {
        $ilDB = $this->db;

        parent::create();
        
        $ilDB->insert("il_wiki_data", array(
            "id" => array("integer", $this->getId()),
            "is_online" => array("integer", (int) $this->getOnline()),
            "startpage" => array("text", $this->getStartPage()),
            "short" => array("text", $this->getShortTitle()),
            "rating" => array("integer", (int) $this->getRating()),
            "public_notes" => array("integer", (int) $this->getPublicNotes()),
            "introduction" => array("clob", $this->getIntroduction()),
            "empty_page_templ" => array("integer", (int) $this->getEmptyPageTemplate()),
            ));

        // create start page
        if ($this->getStartPage() != "" && !$a_prevent_start_page_creation) {
            include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
            $start_page = new ilWikiPage();
            $start_page->setWikiId($this->getId());
            $start_page->setTitle($this->getStartPage());
            $start_page->create();
        }

        if (((int) $this->getStyleSheetId()) > 0) {
            include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
            ilObjStyleSheet::writeStyleUsage($this->getId(), $this->getStyleSheetId());
        }
    }

    /**
    * update object data
    *
    * @access	public
    * @return	boolean
    */
    public function update($a_prevent_start_page_creation = false)
    {
        $ilDB = $this->db;
        
        if (!parent::update()) {
            return false;
        }
        
        $ilDB->update("il_wiki_data", array(
            "is_online" => array("integer", $this->getOnline()),
            "startpage" => array("text", $this->getStartPage()),
            "short" => array("text", $this->getShortTitle()),
            "rating_overall" => array("integer", $this->getRatingOverall()),
            "rating" => array("integer", $this->getRating()),
            "rating_side" => array("integer", (bool) $this->getRatingAsBlock()), // #13455
            "rating_new" => array("integer", $this->getRatingForNewPages()),
            "rating_ext" => array("integer", $this->getRatingCategories()),
            "public_notes" => array("integer", $this->getPublicNotes()),
            "introduction" => array("clob", $this->getIntroduction()),
            "imp_pages" => array("integer", $this->getImportantPages()),
            "page_toc" => array("integer", $this->getPageToc()),
            "link_md_values" => array("integer", $this->getLinkMetadataValues()),
            "empty_page_templ" => array("integer", $this->getEmptyPageTemplate())
            ), array(
            "id" => array("integer", $this->getId())
            ));

        // check whether start page exists
        include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
        if (!ilWikiPage::exists($this->getId(), $this->getStartPage())
            && !$a_prevent_start_page_creation) {
            $start_page = new ilWikiPage();
            $start_page->setWikiId($this->getId());
            $start_page->setTitle($this->getStartPage());
            $start_page->create();
        }

        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        ilObjStyleSheet::writeStyleUsage($this->getId(), $this->getStyleSheetId());

        return true;
    }
    
    /**
    * Read wiki data
    */
    public function read()
    {
        $ilDB = $this->db;
        
        parent::read();
        
        $query = "SELECT * FROM il_wiki_data WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        $this->setOnline($rec["is_online"]);
        $this->setStartPage($rec["startpage"]);
        $this->setShortTitle($rec["short"]);
        $this->setRatingOverall($rec["rating_overall"]);
        $this->setRating($rec["rating"]);
        $this->setRatingAsBlock($rec["rating_side"]);
        $this->setRatingForNewPages($rec["rating_new"]);
        $this->setRatingCategories($rec["rating_ext"]);
        $this->setPublicNotes($rec["public_notes"]);
        $this->setIntroduction($rec["introduction"]);
        $this->setImportantPages($rec["imp_pages"]);
        $this->setPageToc($rec["page_toc"]);
        $this->setEmptyPageTemplate($rec["empty_page_templ"]);
        $this->setLinkMetadataValues($rec["link_md_values"]);

        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $this->setStyleSheetId((int) ilObjStyleSheet::lookupObjectStyle($this->getId()));
    }


    /**
    * delete object and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        $ilDB = $this->db;
        
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
                
        // delete record of table il_wiki_data
        $query = "DELETE FROM il_wiki_data" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);

        // remove all notifications
        include_once "./Services/Notification/classes/class.ilNotification.php";
        ilNotification::removeForObject(ilNotification::TYPE_WIKI, $this->getId());
        
        include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
        ilWikiPage::deleteAllPagesOfWiki($this->getId());
        
        return true;
    }

    /**
    * Check availability of short title
    */
    public static function checkShortTitleAvailability($a_short_title)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = $ilDB->queryF(
            "SELECT id FROM il_wiki_data WHERE short = %s",
            array("text"),
            array($a_short_title)
        );
        if ($ilDB->fetchAssoc($res)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Lookup whether rating is activated for whole object.
     *
     * @param	int			$a_wiki_id		Wiki ID
     *
     * @return	boolean		Rating activated?
     */
    public static function _lookupRatingOverall($a_wiki_id)
    {
        return ilObjWiki::_lookup($a_wiki_id, "rating_overall");
    }
    
    /**
     * Lookup whether rating is activated.
     *
     * @param	int			$a_wiki_id		Wiki ID
     *
     * @return	boolean		Rating activated?
     */
    public static function _lookupRating($a_wiki_id)
    {
        return ilObjWiki::_lookup($a_wiki_id, "rating");
    }
    
    /**
     * Lookup whether rating categories are activated.
     *
     * @param	int			$a_wiki_id		Wiki ID
     *
     * @return	boolean		Rating categories activated?
     */
    public static function _lookupRatingCategories($a_wiki_id)
    {
        return ilObjWiki::_lookup($a_wiki_id, "rating_ext");
    }
    
    /**
     * Lookup whether rating side block is activated.
     *
     * @param	int			$a_wiki_id		Wiki ID
     *
     * @return	boolean		Rating side block activated?
     */
    public static function _lookupRatingAsBlock($a_wiki_id)
    {
        return ilObjWiki::_lookup($a_wiki_id, "rating_side");
    }

    /**
     * Lookup whether public notes are activated
     *
     * @param	int			$a_wiki_id		Wiki ID
     *
     * @return	boolean		public notes activated?
     */
    public static function _lookupPublicNotes($a_wiki_id)
    {
        return ilObjWiki::_lookup($a_wiki_id, "public_notes");
    }

    /**
     * Lookup whether metadata should be auto linked
     *
     * @param	int			$a_wiki_id		Wiki ID
     *
     * @return	boolean		auto linking activated?
     */
    public static function _lookupLinkMetadataValues($a_wiki_id)
    {
        return ilObjWiki::_lookup($a_wiki_id, "link_md_values");
    }

    /**
    * Lookup a data field
    *
    * @param	int			$a_wiki_id		Wiki ID
    * @param	string		$a_field		Field Name
    *
    * @return	mixed		field value
    */
    private static function _lookup($a_wiki_id, $a_field)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT $a_field FROM il_wiki_data WHERE id = " .
            $ilDB->quote($a_wiki_id, "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_field];
    }

    /**
    * Lookup start page
    *
    * @param	int			$a_wiki_id		Wiki ID
    *
    * @return	boolean
    */
    public static function _lookupStartPage($a_wiki_id)
    {
        return ilObjWiki::_lookup($a_wiki_id, "startpage");
    }

    /**
     * Write start page
     */
    public static function writeStartPage($a_id, $a_name)
    {
        global $DIC;

        $ilDB = $DIC->database();

        include_once("./Modules/Wiki/classes/class.ilWikiUtil.php");
        $ilDB->manipulate(
            "UPDATE il_wiki_data SET " .
            " startpage = " . $ilDB->quote(ilWikiUtil::makeDbTitle($a_name), "text") .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
    }

    /**
    * Search in Wiki
    */
    public static function _performSearch($a_wiki_id, $a_searchterm)
    {
        // query parser
        include_once 'Services/Search/classes/class.ilQueryParser.php';

        $query_parser = new ilQueryParser($a_searchterm);
        $query_parser->setCombination("or");
        $query_parser->parse();

        include_once 'Services/Search/classes/class.ilSearchResult.php';
        $search_result = new ilSearchResult();
        if ($query_parser->validate()) {
            include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
            $wiki_search = ilObjectSearchFactory::_getWikiContentSearchInstance($query_parser);
            $wiki_search->setFilter(array('wpg'));
            $search_result->mergeEntries($wiki_search->performSearch());
        }
        
        $entries = $search_result->getEntries();
        
        $found_pages = array();
        foreach ($entries as $entry) {
            if ($entry["obj_id"] == $a_wiki_id && is_array($entry["child"])) {
                foreach ($entry["child"] as $child) {
                    $found_pages[] = array("page_id" => $child);
                }
            }
        }

        return $found_pages;
    }

    //
    // Important pages
    //

    /**
     * Lookup whether important pages are activated.
     *
     * @param	int			$a_wiki_id		Wiki ID
     *
     * @return	boolean		Important pages activated?
     */
    public static function _lookupImportantPages($a_wiki_id)
    {
        return ilObjWiki::_lookup($a_wiki_id, "imp_pages");
    }

    /**
     * Get important pages list
     *
     * @param
     * @return
     */
    public static function _lookupImportantPagesList($a_wiki_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM il_wiki_imp_pages WHERE " .
            " wiki_id = " . $ilDB->quote($a_wiki_id, "integer") . " ORDER BY ord ASC "
        );

        $imp_pages = array();

        while ($rec = $ilDB->fetchAssoc($set)) {
            $imp_pages[] = $rec;
        }
        return $imp_pages;
    }

    /**
     * Get important pages list
     *
     * @param
     * @return
     */
    public static function _lookupMaxOrdNrImportantPages($a_wiki_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT MAX(ord) as m FROM il_wiki_imp_pages WHERE " .
            " wiki_id = " . $ilDB->quote($a_wiki_id, "integer")
        );

        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["m"];
    }


    /**
     * Add important page
     *
     * @param	int		page id
     */
    public function addImportantPage($a_page_id, $a_nr = 0, $a_indent = 0)
    {
        $ilDB = $this->db;

        if (!$this->isImportantPage($a_page_id)) {
            if ($a_nr == 0) {
                $a_nr = ilObjWiki::_lookupMaxOrdNrImportantPages($this->getId()) + 10;
            }

            $ilDB->manipulate("INSERT INTO il_wiki_imp_pages " .
                "(wiki_id, ord, indent, page_id) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($a_nr, "integer") . "," .
                $ilDB->quote($a_indent, "integer") . "," .
                $ilDB->quote($a_page_id, "integer") .
                ")");
        }
    }

    /**
     * Is page an important page?
     *
     * @param
     * @return
     */
    public function isImportantPage($a_page_id)
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM il_wiki_imp_pages WHERE " .
            " wiki_id = " . $ilDB->quote($this->getId(), "integer") . " AND " .
            " page_id = " . $ilDB->quote($a_page_id, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Remove important page
     *
     * @param	int		page id
     */
    public function removeImportantPage($a_id)
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM il_wiki_imp_pages WHERE "
            . " wiki_id = " . $ilDB->quote($this->getId(), "integer")
            . " AND page_id = " . $ilDB->quote($a_id, "integer")
        );

        $this->fixImportantPagesNumbering();
    }

    /**
     * Save ordering and indentation
     *
     * @param
     * @return
     */
    public function saveOrderingAndIndentation($a_ord, $a_indent)
    {
        $ilDB = $this->db;

        $ipages = ilObjWiki::_lookupImportantPagesList($this->getId());

        foreach ($ipages as $k => $v) {
            if (isset($a_ord[$v["page_id"]])) {
                $ipages[$k]["ord"] = (int) $a_ord[$v["page_id"]];
            }
            if (isset($a_indent[$v["page_id"]])) {
                $ipages[$k]["indent"] = (int) $a_indent[$v["page_id"]];
            }
        }
        $ipages = ilUtil::sortArray($ipages, "ord", "asc", true);

        // fix indentation: no 2 is allowed after a 0
        $c_indent = 0;
        $fixed = false;
        foreach ($ipages as $k => $v) {
            if ($ipages[$k]["indent"] == 2 && $c_indent == 0) {
                $ipages[$k]["indent"] = 1;
                $fixed = true;
            }
            $c_indent = $ipages[$k]["indent"];
        }
        
        $ord = 10;
        reset($ipages);
        foreach ($ipages as $k => $v) {
            $ilDB->manipulate(
                $q = "UPDATE il_wiki_imp_pages SET " .
                " ord = " . $ilDB->quote($ord, "integer") . "," .
                " indent = " . $ilDB->quote($v["indent"], "integer") .
                " WHERE wiki_id = " . $ilDB->quote($v["wiki_id"], "integer") .
                " AND page_id = " . $ilDB->quote($v["page_id"], "integer")
            );
            $ord+=10;
        }
        
        return $fixed;
    }

    /**
     * Fix important pages numbering
     */
    public function fixImportantPagesNumbering()
    {
        $ilDB = $this->db;

        $ipages = ilObjWiki::_lookupImportantPagesList($this->getId());

        // fix indentation: no 2 is allowed after a 0
        $c_indent = 0;
        $fixed = false;
        foreach ($ipages as $k => $v) {
            if ($ipages[$k]["indent"] == 2 && $c_indent == 0) {
                $ipages[$k]["indent"] = 1;
                $fixed = true;
            }
            $c_indent = $ipages[$k]["indent"];
        }

        $ord = 10;
        foreach ($ipages as $k => $v) {
            $ilDB->manipulate(
                $q = "UPDATE il_wiki_imp_pages SET " .
                " ord = " . $ilDB->quote($ord, "integer") .
                ", indent = " . $ilDB->quote($v["indent"], "integer") .
                " WHERE wiki_id = " . $ilDB->quote($v["wiki_id"], "integer") .
                " AND page_id = " . $ilDB->quote($v["page_id"], "integer")
            );
            $ord+=10;
        }
    }

    //
    // Page TOC
    //

    /**
     * Lookup whether important pages are activated.
     *
     * @param	int			$a_wiki_id		Wiki ID
     *
     * @return	boolean		Important pages activated?
     */
    public static function _lookupPageToc($a_wiki_id)
    {
        return ilObjWiki::_lookup($a_wiki_id, "page_toc");
    }

    /**
     * Clone wiki
     *
     * @param int target ref_id
     * @param int copy id
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOnline($this->getOnline());
        }
        
        //$new_obj->setTitle($this->getTitle());		// see #20074
        $new_obj->setStartPage($this->getStartPage());
        $new_obj->setShortTitle($this->getShortTitle());
        $new_obj->setRatingOverall($this->getRatingOverall());
        $new_obj->setRating($this->getRating());
        $new_obj->setRatingAsBlock($this->getRatingAsBlock());
        $new_obj->setRatingForNewPages($this->getRatingForNewPages());
        $new_obj->setRatingCategories($this->getRatingCategories());
        $new_obj->setPublicNotes($this->getPublicNotes());
        $new_obj->setIntroduction($this->getIntroduction());
        $new_obj->setImportantPages($this->getImportantPages());
        $new_obj->setPageToc($this->getPageToc());
        $new_obj->update();

        // set/copy stylesheet
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $style_id = $this->getStyleSheetId();
        if ($style_id > 0 && !ilObjStyleSheet::_lookupStandard($style_id)) {
            $style_obj = ilObjectFactory::getInstanceByObjId($style_id);
            $new_id = $style_obj->ilClone();
            $new_obj->setStyleSheetId($new_id);
            $new_obj->update();
        }

        // copy content
        include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
        $pages = ilWikiPage::getAllWikiPages($this->getId());
        if (count($pages) > 0) {
            // if we have any pages, delete the start page first
            $pg_id = ilWikiPage::getPageIdForTitle($new_obj->getId(), $new_obj->getStartPage());
            $start_page = new ilWikiPage($pg_id);
            $start_page->delete();
        }
        $map = array();
        foreach ($pages as $p) {
            $page = new ilWikiPage($p["id"]);
            $new_page = new ilWikiPage();
            $new_page->setTitle($page->getTitle());
            $new_page->setWikiId($new_obj->getId());
            $new_page->setTitle($page->getTitle());
            $new_page->setBlocked($page->getBlocked());
            $new_page->setRating($page->getRating());
            $new_page->hideAdvancedMetadata($page->isAdvancedMetadataHidden());
            $new_page->create();

            $page->copy($new_page->getId(), "", 0, true);
            //$new_page->setXMLContent($page->copyXMLContent(true));
            //$new_page->buildDom(true);
            //$new_page->update();
            $map[$p["id"]] = $new_page->getId();
        }
        
        // copy important pages
        foreach (ilObjWiki::_lookupImportantPagesList($this->getId()) as $ip) {
            $new_obj->addImportantPage($map[$ip["page_id"]], $ip["ord"], $ip["indent"]);
        }

        // copy rating categories
        include_once("./Services/Rating/classes/class.ilRatingCategory.php");
        foreach (ilRatingCategory::getAllForObject($this->getId()) as $rc) {
            $new_rc = new ilRatingCategory();
            $new_rc->setParentId($new_obj->getId());
            $new_rc->setTitle($rc["title"]);
            $new_rc->setDescription($rc["description"]);
            $new_rc->save();
        }
        
        return $new_obj;
    }

    /**
     * Get template selection on creation? If more than one template (including empty page template)
     * is activated -> return true
     *
     * @return boolean true, if manual template selection needed
     */
    public function getTemplateSelectionOnCreation()
    {
        $num = (int) $this->getEmptyPageTemplate();
        include_once("./Modules/Wiki/classes/class.ilWikiPageTemplate.php");
        $wt = new ilWikiPageTemplate($this->getId());
        $ts = $wt->getAllInfo(ilWikiPageTemplate::TYPE_NEW_PAGES);
        $num += count($ts);
        if ($num > 1) {
            return true;
        }
        return false;
    }

    /**
     * Create new wiki page
     *
     * @param string $a_page_title page title
     * @param int $a_template_page template page id
     * @return ilWikiPage new wiki page
     */
    public function createWikiPage($a_page_title, $a_template_page = 0)
    {
        // check if template has to be used
        if ($a_template_page == 0) {
            if (!$this->getEmptyPageTemplate()) {
                include_once("./Modules/Wiki/classes/class.ilWikiPageTemplate.php");
                $wt = new ilWikiPageTemplate($this->getId());
                $ts = $wt->getAllInfo(ilWikiPageTemplate::TYPE_NEW_PAGES);
                if (count($ts) == 1) {
                    $t = current($ts);
                    $a_template_page = $t["wpage_id"];
                }
            }
        }

        // create the page
        $page = new ilWikiPage();
        $page->setWikiId($this->getId());
        $page->setTitle(ilWikiUtil::makeDbTitle($a_page_title));
        if ($this->getRating() && $this->getRatingForNewPages()) {
            $page->setRating(true);
        }

        // needed for notification
        $page->setWikiRefId($this->getRefId());
        $page->create();

        // copy template into new page
        if ($a_template_page > 0) {
            $orig = new ilWikiPage($a_template_page);
            $orig->copy($page->getId());
            
            // #15718
            include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php";
            ilAdvancedMDValues::_cloneValues(
                $this->getId(),
                $this->getId(),
                "wpg",
                $a_template_page,
                $page->getId()
            );
        }

        return $page;
    }

    public static function getAdvMDSubItemTitle($a_obj_id, $a_sub_type, $a_sub_id)
    {
        global $DIC;

        $lng = $DIC->language();
    
        if ($a_sub_type == "wpg") {
            $lng->loadLanguageModule("wiki");
            include_once "./Modules/Wiki/classes/class.ilWikiPage.php";
            return $lng->txt("wiki_wpg") . ' "' . ilWikiPage::lookupTitle($a_sub_id) . '"';
        }
    }

    /**
     * Init user html export
     *
     * @param
     * @return
     */
    public function initUserHTMLExport()
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        include_once("./Modules/Wiki/classes/class.ilWikiUserHTMLExport.php");

        $user_export = new ilWikiUserHTMLExport($this, $ilDB, $ilUser);
        $user_export->initUserHTMLExport();
    }

    /**
     * Start user html export
     *
     * @param
     * @return
     */
    public function startUserHTMLExport()
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        include_once("./Modules/Wiki/classes/class.ilWikiUserHTMLExport.php");

        $user_export = new ilWikiUserHTMLExport($this, $ilDB, $ilUser);
        $user_export->startUserHTMLExport();
    }

    /**
     * Get user html export progress
     *
     * @return array progress info
     */
    public function getUserHTMLExportProgress()
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        include_once("./Modules/Wiki/classes/class.ilWikiUserHTMLExport.php");

        $user_export = new ilWikiUserHTMLExport($this, $ilDB, $ilUser);
        return $user_export->getProgress();
    }

    /**
     * Send user html export file
     */
    public function deliverUserHTMLExport()
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        include_once("./Modules/Wiki/classes/class.ilWikiUserHTMLExport.php");

        $user_export = new ilWikiUserHTMLExport($this, $ilDB, $ilUser);
        return $user_export->deliverFile();
    }
    
    
    /**
     * Decorate adv md value
     *
     * @param string $a_value value
     * @return string decorated value (includes HTML)
     */
    public function decorateAdvMDValue($a_value)
    {
        include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
        if (ilWikiPage::_wikiPageExists($this->getId(), $a_value)) {
            $url = ilObjWikiGUI::getGotoLink($this->getRefId(), $a_value);
            return "<a href='" . $url . "'>" . $a_value . "</a>";
        }

        return $a_value;
    }
}
