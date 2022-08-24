<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilObjWiki
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjWiki extends ilObject implements ilAdvancedMetaDataSubItems
{
    protected bool $page_toc = false;
    protected int $style_id = 0;
    protected string $introduction = "";
    protected string $shorttitle = "";
    protected string $startpage = "";
    protected bool $rating_categories = false;
    protected bool $rating_new_pages = false;
    protected bool $rating = false;
    protected bool $rating_block = false;
    protected bool $rating_overall = false;
    protected ilObjUser $user;
    protected bool $online = false;
    protected bool $public_notes = true;
    protected bool $empty_page_templ = true;
    protected bool $link_md_values = false;
    protected ilSetting $setting;
    protected \ILIAS\Style\Content\DomainService $content_style_service;

    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->type = "wiki";
        $this->setting = $DIC->settings();
        parent::__construct($a_id, $a_call_by_reference);

        $this->content_style_service = $DIC
            ->contentStyle()
            ->domain();
    }

    public function setOnline(bool $a_online): void
    {
        $this->online = $a_online;
    }

    public function getOnline(): bool
    {
        return $this->online;
    }

    // Set Enable Rating For Object.
    public function setRatingOverall(bool $a_rating): void
    {
        $this->rating_overall = $a_rating;
    }

    public function getRatingOverall(): bool
    {
        return $this->rating_overall;
    }

    // Set Enable Rating.
    public function setRating(bool $a_rating): void
    {
        $this->rating = $a_rating;
    }

    public function getRating(): bool
    {
        return $this->rating;
    }

    public function setRatingAsBlock(bool $a_rating): void
    {
        $this->rating_block = $a_rating;
    }

    public function getRatingAsBlock(): bool
    {
        return $this->rating_block;
    }

    public function setRatingForNewPages(bool $a_rating): void
    {
        $this->rating_new_pages = $a_rating;
    }

    public function getRatingForNewPages(): bool
    {
        return $this->rating_new_pages;
    }

    public function setRatingCategories(bool $a_rating): void
    {
        $this->rating_categories = $a_rating;
    }

    public function getRatingCategories(): bool
    {
        return $this->rating_categories;
    }

    public function setPublicNotes(bool $a_val): void
    {
        $this->public_notes = $a_val;
    }

    public function getPublicNotes(): bool
    {
        return $this->public_notes;
    }

    public function setStartPage(string $a_startpage): void
    {
        $this->startpage = ilWikiUtil::makeDbTitle($a_startpage);
    }

    public function getStartPage(): string
    {
        return $this->startpage;
    }

    public function setShortTitle(string $a_shorttitle): void
    {
        $this->shorttitle = $a_shorttitle;
    }

    public function getShortTitle(): string
    {
        return $this->shorttitle;
    }

    public function setIntroduction(string $a_introduction): void
    {
        $this->introduction = $a_introduction;
    }

    public function getIntroduction(): string
    {
        return $this->introduction;
    }

    public function setPageToc(bool $a_val): void
    {
        $this->page_toc = $a_val;
    }

    public function getPageToc(): bool
    {
        return $this->page_toc;
    }

    public function setEmptyPageTemplate(bool $a_val): void
    {
        $this->empty_page_templ = $a_val;
    }

    public function getEmptyPageTemplate(): bool
    {
        return $this->empty_page_templ;
    }

    public function setLinkMetadataValues(bool $a_val): void
    {
        $this->link_md_values = $a_val;
    }

    public function getLinkMetadataValues(): bool
    {
        return $this->link_md_values;
    }

    public function create(
        bool $a_prevent_start_page_creation = false
    ): int {
        $ilDB = $this->db;

        $id = parent::create();

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
        if ($this->getStartPage() !== "" && !$a_prevent_start_page_creation) {
            $start_page = new ilWikiPage();
            $start_page->setWikiId($this->getId());
            $start_page->setTitle($this->getStartPage());
            $start_page->create();
        }

        return $id;
    }

    public function update(
        bool $a_prevent_start_page_creation = false
    ): bool {
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
            "rating_side" => array("integer", $this->getRatingAsBlock()), // #13455
            "rating_new" => array("integer", $this->getRatingForNewPages()),
            "rating_ext" => array("integer", $this->getRatingCategories()),
            "public_notes" => array("integer", $this->getPublicNotes()),
            "introduction" => array("clob", $this->getIntroduction()),
            "page_toc" => array("integer", $this->getPageToc()),
            "link_md_values" => array("integer", $this->getLinkMetadataValues()),
            "empty_page_templ" => array("integer", $this->getEmptyPageTemplate())
            ), array(
            "id" => array("integer", $this->getId())
            ));

        // check whether start page exists
        if (!ilWikiPage::exists($this->getId(), $this->getStartPage())
            && !$a_prevent_start_page_creation) {
            $start_page = new ilWikiPage();
            $start_page->setWikiId($this->getId());
            $start_page->setTitle($this->getStartPage());
            $start_page->create();
        }

        return true;
    }

    public function read(): void
    {
        $ilDB = $this->db;

        parent::read();

        $query = "SELECT * FROM il_wiki_data WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        $this->setOnline((bool) $rec["is_online"]);
        $this->setStartPage((string) $rec["startpage"]);
        $this->setShortTitle((string) $rec["short"]);
        $this->setRatingOverall((bool) $rec["rating_overall"]);
        $this->setRating((bool) $rec["rating"]);
        $this->setRatingAsBlock((bool) $rec["rating_side"]);
        $this->setRatingForNewPages((bool) $rec["rating_new"]);
        $this->setRatingCategories((bool) $rec["rating_ext"]);
        $this->setPublicNotes((bool) $rec["public_notes"]);
        $this->setIntroduction((string) $rec["introduction"]);
        $this->setPageToc((bool) $rec["page_toc"]);
        $this->setEmptyPageTemplate((bool) $rec["empty_page_templ"]);
        $this->setLinkMetadataValues((bool) $rec["link_md_values"]);
    }


    /**
     * delete object and all related data
     */
    public function delete(): bool
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
        ilNotification::removeForObject(ilNotification::TYPE_WIKI, $this->getId());

        ilWikiPage::deleteAllPagesOfWiki($this->getId());

        return true;
    }

    public static function checkShortTitleAvailability(
        string $a_short_title
    ): bool {
        global $DIC;
        $ilDB = $DIC->database();

        if ($a_short_title === "") {
            return true;
        }
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
     */
    public static function _lookupRatingOverall(int $a_wiki_id): bool
    {
        return (bool) self::_lookup($a_wiki_id, "rating_overall");
    }

    /**
     * Lookup whether rating is activated.
     */
    public static function _lookupRating(int $a_wiki_id): bool
    {
        return (bool) self::_lookup($a_wiki_id, "rating");
    }

    /**
     * Lookup whether rating categories are activated.
     */
    public static function _lookupRatingCategories(int $a_wiki_id): bool
    {
        return (bool) self::_lookup($a_wiki_id, "rating_ext");
    }

    /**
     * Lookup whether rating side block is activated.
     */
    public static function _lookupRatingAsBlock(int $a_wiki_id): bool
    {
        return (bool) self::_lookup($a_wiki_id, "rating_side");
    }

    /**
     * Lookup whether public notes are activated
     */
    public static function _lookupPublicNotes(int $a_wiki_id): bool
    {
        return (bool) self::_lookup($a_wiki_id, "public_notes");
    }

    /**
     * Lookup whether metadata should be auto linked
     */
    public static function _lookupLinkMetadataValues(int $a_wiki_id): bool
    {
        return (bool) self::_lookup($a_wiki_id, "link_md_values");
    }

    /**
     * Lookup a data field
     * @return mixed
     */
    private static function _lookup(int $a_wiki_id, string $a_field)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT $a_field FROM il_wiki_data WHERE id = " .
            $ilDB->quote($a_wiki_id, "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_field];
    }

    public static function _lookupStartPage(int $a_wiki_id): string
    {
        return (string) self::_lookup($a_wiki_id, "startpage");
    }

    public static function writeStartPage(int $a_id, string $a_name): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "UPDATE il_wiki_data SET " .
            " startpage = " . $ilDB->quote(ilWikiUtil::makeDbTitle($a_name), "text") .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
    }

    /**
     * Search in Wiki
     */
    public static function _performSearch(
        int $a_wiki_id,
        string $a_searchterm
    ): array {
        // query parser
        $query_parser = new ilQueryParser($a_searchterm);
        $query_parser->setCombination("or");
        $query_parser->parse();

        $search_result = new ilSearchResult();
        if ($query_parser->validate()) {
            $wiki_search = ilObjectSearchFactory::_getWikiContentSearchInstance($query_parser);
            $wiki_search->setFilter(array('wpg'));
            $r = $wiki_search->performSearch();
            $search_result->mergeEntries($r);
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

    public static function _lookupImportantPagesList(int $a_wiki_id): array
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

    public static function _lookupMaxOrdNrImportantPages(
        int $a_wiki_id
    ): int {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT MAX(ord) as m FROM il_wiki_imp_pages WHERE " .
            " wiki_id = " . $ilDB->quote($a_wiki_id, "integer")
        );

        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["m"];
    }


    public function addImportantPage(
        int $a_page_id,
        int $a_nr = 0,
        int $a_indent = 0
    ): void {
        $ilDB = $this->db;

        if (!$this->isImportantPage($a_page_id)) {
            if ($a_nr === 0) {
                $a_nr = self::_lookupMaxOrdNrImportantPages($this->getId()) + 10;
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

    public function isImportantPage(
        int $a_page_id
    ): bool {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM il_wiki_imp_pages WHERE " .
            " wiki_id = " . $ilDB->quote($this->getId(), "integer") . " AND " .
            " page_id = " . $ilDB->quote($a_page_id, "integer")
        );
        if ($ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    public function removeImportantPage(
        int $a_id
    ): void {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM il_wiki_imp_pages WHERE "
            . " wiki_id = " . $ilDB->quote($this->getId(), "integer")
            . " AND page_id = " . $ilDB->quote($a_id, "integer")
        );

        $this->fixImportantPagesNumbering();
    }

    public function saveOrderingAndIndentation(
        array $a_ord,
        array $a_indent
    ): bool {
        $ilDB = $this->db;

        $ipages = self::_lookupImportantPagesList($this->getId());

        foreach ($ipages as $k => $v) {
            if (isset($a_ord[$v["page_id"]])) {
                $ipages[$k]["ord"] = (int) $a_ord[$v["page_id"]];
            }
            if (isset($a_indent[$v["page_id"]])) {
                $ipages[$k]["indent"] = (int) $a_indent[$v["page_id"]];
            }
        }
        $ipages = ilArrayUtil::sortArray($ipages, "ord", "asc", true);

        // fix indentation: no 2 is allowed after a 0
        $c_indent = 0;
        $fixed = false;
        foreach ($ipages as $k => $v) {
            if ($v["indent"] == 2 && $c_indent == 0) {
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
            $ord += 10;
        }

        return $fixed;
    }

    public function fixImportantPagesNumbering(): void
    {
        $ilDB = $this->db;

        $ipages = self::_lookupImportantPagesList($this->getId());

        // fix indentation: no 2 is allowed after a 0
        $c_indent = 0;
        foreach ($ipages as $k => $v) {
            if ($v["indent"] == 2 && $c_indent == 0) {
                $ipages[$k]["indent"] = 1;
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
            $ord += 10;
        }
    }

    //
    // Page TOC
    //

    public static function _lookupPageToc(
        int $a_wiki_id
    ): bool {
        return (bool) self::_lookup($a_wiki_id, "page_toc");
    }

    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);

        // Custom meta data activation is stored in a container setting
        ilContainer::_writeContainerSetting(
            $new_obj->getId(),
            ilObjectServiceSettingsGUI::CUSTOM_METADATA,
            ilContainer::_lookupContainerSetting(
                $this->getId(),
                ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                0
            )
        );

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($copy_id);

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
        $new_obj->setPageToc($this->getPageToc());
        $new_obj->update();

        $this->content_style_service
            ->styleForRefId($this->getRefId())
            ->cloneTo($new_obj->getId());

        // copy content
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
        foreach (self::_lookupImportantPagesList($this->getId()) as $ip) {
            $new_obj->addImportantPage($map[$ip["page_id"]], $ip["ord"], $ip["indent"]);
        }

        // copy rating categories
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
     * @return bool true, if manual template selection needed
     */
    public function getTemplateSelectionOnCreation(): bool
    {
        $num = (int) $this->getEmptyPageTemplate();
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
     */
    public function createWikiPage(
        string $a_page_title,
        int $a_template_page = 0
    ): ilWikiPage {
        // check if template has to be used
        if ($a_template_page === 0) {
            if (!$this->getEmptyPageTemplate()) {
                $wt = new ilWikiPageTemplate($this->getId());
                $ts = $wt->getAllInfo(ilWikiPageTemplate::TYPE_NEW_PAGES);
                if (count($ts) === 1) {
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
            ilAdvancedMDValues::_cloneValues(
                0,
                $this->getId(),
                $this->getId(),
                "wpg",
                $a_template_page,
                $page->getId()
            );
        }

        return $page;
    }

    public static function getAdvMDSubItemTitle($a_obj_id, $a_sub_type, $a_sub_id): string // TODO PHP8-REVIEW Type hints are missing here
    {
        global $DIC;

        $lng = $DIC->language();

        if ($a_sub_type === "wpg") {
            $lng->loadLanguageModule("wiki");
            return $lng->txt("wiki_wpg") . ' "' . ilWikiPage::lookupTitle($a_sub_id) . '"';
        }
        return "";
    }

    public function initUserHTMLExport(
        bool $with_comments = false
    ): void {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $user_export = new ilWikiUserHTMLExport($this, $ilDB, $ilUser, $with_comments);
        $user_export->initUserHTMLExport();
    }

    public function startUserHTMLExport(
        bool $with_comments = false
    ): void {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $user_export = new ilWikiUserHTMLExport($this, $ilDB, $ilUser, $with_comments);
        $user_export->startUserHTMLExport();
    }

    /**
     * Get user html export progress
     * @return array progress info
     */
    public function getUserHTMLExportProgress(
        bool $with_comments = false
    ): array {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $user_export = new ilWikiUserHTMLExport($this, $ilDB, $ilUser, $with_comments);
        return $user_export->getProgress();
    }

    public function deliverUserHTMLExport(
        bool $with_comments = false
    ): void {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $user_export = new ilWikiUserHTMLExport($this, $ilDB, $ilUser, $with_comments);
        $user_export->deliverFile();
    }


    /**
     * Decorate adv md value
     * @param string $a_value value
     * @return string decorated value (includes HTML)
     */
    public function decorateAdvMDValue(string $a_value): string
    {
        if (ilWikiPage::_wikiPageExists($this->getId(), $a_value)) {
            $url = ilObjWikiGUI::getGotoLink($this->getRefId(), $a_value);
            return "<a href='" . $url . "'>" . $a_value . "</a>";
        }

        return $a_value;
    }

    /**
     * Is export possible
     */
    public function isCommentsExportPossible(): bool
    {
        $setting = $this->setting;
        $privacy = ilPrivacySettings::getInstance();
        if ($setting->get("disable_comments")) {
            return false;
        }

        if (!$this->getPublicNotes()) {
            return false;
        }
        if (!$privacy->enabledCommentsExport()) {
            return false;
        }
        return true;
    }
}
