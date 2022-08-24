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
 * Class ilWikiPage
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiPage extends ilPageObject
{
    protected int $parent_ref_id = 0;
    protected string $title = "";
    protected bool $blocked = false;
    protected bool $rating = false;
    protected bool $hide_adv_md = false;

    public function getParentType(): string
    {
        return "wpg";
    }

    public function afterConstructor(): void
    {
        $this->getPageConfig()->configureByObjectId($this->getParentId());
    }

    public function setTitle(string $a_title): void
    {
        $this->title = ilWikiUtil::makeDbTitle($a_title);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setWikiId(int $a_wikiid): void
    {
        $this->setParentId($a_wikiid);
    }

    public function getWikiId(): int
    {
        return $this->getParentId();
    }

    public function setWikiRefId(int $a_wiki_ref_id): void
    {
        $this->parent_ref_id = $a_wiki_ref_id;
    }

    public function getWikiRefId(): int
    {
        return $this->parent_ref_id;
    }

    public function setBlocked(bool $a_val): void
    {
        $this->blocked = $a_val;
    }

    public function getBlocked(): bool
    {
        return $this->blocked;
    }

    public function setRating(bool $a_val): void
    {
        $this->rating = $a_val;
    }

    public function getRating(): bool
    {
        return $this->rating;
    }

    public function hideAdvancedMetadata(bool $a_val): void
    {
        $this->hide_adv_md = $a_val;
    }

    public function isAdvancedMetadataHidden(): bool
    {
        return $this->hide_adv_md;
    }

    public function createFromXML(): void
    {
        $ilDB = $this->db;

        // ilWikiDataset creates wiki pages without copage objects
        // (see create function in this class, parameter $a_prevent_page_creation)
        // The ilCOPageImporter will call createFromXML without running through the read
        // method -> we will miss the important wiki id, thus we read it now
        // see also bug #12224
        $set = $ilDB->query(
            "SELECT id FROM il_wiki_page " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $this->read(true);
        }

        parent::createFromXML();
    }

    public function create(
        bool $a_import = false
    ): void {
        $ilDB = $this->db;

        $id = $ilDB->nextId("il_wiki_page");
        $this->setId($id);
        $query = "INSERT INTO il_wiki_page (" .
            "id" .
            ", title" .
            ", wiki_id" .
            ", blocked" .
            ", rating" .
            ", hide_adv_md" .
            " ) VALUES (" .
            $ilDB->quote($this->getId(), "integer")
            . "," . $ilDB->quote($this->getTitle(), "text")
            . "," . $ilDB->quote($this->getWikiId(), "integer")
            . "," . $ilDB->quote((int) $this->getBlocked(), "integer")
            . "," . $ilDB->quote((int) $this->getRating(), "integer")
            . "," . $ilDB->quote((int) $this->isAdvancedMetadataHidden(), "integer")
            . ")";
        $ilDB->manipulate($query);

        // create page object
        if (!$a_import) {
            parent::create($a_import);
            $this->saveInternalLinks($this->getDomDoc());

            ilWikiStat::handleEvent(ilWikiStat::EVENT_PAGE_CREATED, $this);
            ilWikiUtil::sendNotification("new", ilNotification::TYPE_WIKI, $this->getWikiRefId(), $this->getId());
        }

        $this->updateNews();
    }

    public function afterUpdate(
        DOMDocument $domdoc,
        string $xml
    ): void {
        // internal == wiki links

        $this->log->debug("collect internal links");
        $int_links = count(ilWikiUtil::collectInternalLinks($xml, $this->getWikiId(), true));

        $xpath = new DOMXPath($domdoc);

        // external = internal + external links
        $ext_links = count($xpath->query('//IntLink'));
        $ext_links += count($xpath->query('//ExtLink'));

        $footnotes = count($xpath->query('//Footnote'));


        // words/characters (xml)

        $xml = strip_tags($xml);

        $num_chars = ilStr::strLen($xml);
        $num_words = count(explode(" ", $xml));

        $page_data = array(
            "int_links" => $int_links,
            "ext_links" => $ext_links,
            "footnotes" => $footnotes,
            "num_words" => $num_words,
            "num_chars" => $num_chars
        );
        $this->log->debug("handle stats");
        ilWikiStat::handleEvent(ilWikiStat::EVENT_PAGE_UPDATED, $this, null, $page_data);
    }

    /**
     * @return array|bool
     * @throws ilDateTimeException
     */
    public function update(
        bool $a_validate = true,
        bool $a_no_history = false
    ) {
        $ilDB = $this->db;
        $this->log->debug("start...");
        // update wiki page data
        $query = "UPDATE il_wiki_page SET " .
            " title = " . $ilDB->quote($this->getTitle(), "text") .
            ",wiki_id = " . $ilDB->quote($this->getWikiId(), "integer") .
            ",blocked = " . $ilDB->quote((int) $this->getBlocked(), "integer") .
            ",rating = " . $ilDB->quote((int) $this->getRating(), "integer") .
            ",hide_adv_md = " . $ilDB->quote((int) $this->isAdvancedMetadataHidden(), "integer") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);
        $updated = parent::update($a_validate, $a_no_history);

        if ($updated === true) {
            $this->log->debug("send notification");
            ilWikiUtil::sendNotification("update", ilNotification::TYPE_WIKI_PAGE, $this->getWikiRefId(), $this->getId());

            $this->log->debug("update news");
            $this->updateNews(true);
        } else {
            return $updated;
        }

        return true;
    }

    public function read(
        bool $a_omit_page_read = false
    ): void {
        $ilDB = $this->db;

        $query = "SELECT * FROM il_wiki_page WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        $this->setTitle($rec["title"]);
        $this->setWikiId($rec["wiki_id"]);
        $this->setBlocked($rec["blocked"]);
        $this->setRating($rec["rating"]);
        $this->hideAdvancedMetadata($rec["hide_adv_md"]);

        // get co page
        if (!$a_omit_page_read) {
            parent::read();
        }
    }


    public function delete(): void
    {
        $ilDB = $this->db;

        // get other pages that link to this page
        $linking_pages = self::getLinksToPage(
            $this->getWikiId(),
            $this->getId()
        );

        // delete internal links information to this page
        ilInternalLink::_deleteAllLinksToTarget("wpg", $this->getId());

        ilWikiStat::handleEvent(ilWikiStat::EVENT_PAGE_DELETED, $this);

        ilWikiUtil::sendNotification("delete", ilNotification::TYPE_WIKI_PAGE, $this->getWikiRefId(), $this->getId());

        // remove all notifications
        ilNotification::removeForObject(ilNotification::TYPE_WIKI_PAGE, $this->getId());

        // delete record of table il_wiki_data
        $query = "DELETE FROM il_wiki_page" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);

        // delete co page
        parent::delete();

        // make links of other pages to this page a missing link
        foreach ($linking_pages as $lp) {
            $ilDB->manipulateF(
                "DELETE FROM il_wiki_missing_page " .
                " WHERE wiki_id = %s AND source_id = %s AND target_name = %s ",
                array("integer", "integer", "text"),
                array($this->getWikiId(), $lp["id"], $this->getTitle())
            );
            $ilDB->manipulateF(
                "INSERT INTO il_wiki_missing_page " .
                "(wiki_id, source_id, target_name) VALUES " .
                "(%s,%s,%s)",
                array("integer", "integer", "text"),
                array($this->getWikiId(), $lp["id"], $this->getTitle())
            );
        }
    }

    public static function deleteAllPagesOfWiki(int $a_wiki_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        // delete record of table il_wiki_data
        $query = "SELECT * FROM il_wiki_page" .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer");
        $set = $ilDB->query($query);

        while ($rec = $ilDB->fetchAssoc($set)) {
            $wiki_page = new ilWikiPage($rec["id"]);
            $wiki_page->delete();
        }
    }

    /**
     * Checks whether a page with given title exists
     */
    public static function exists(
        int $a_wiki_id,
        string $a_title
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();

        $a_title = ilWikiUtil::makeDbTitle($a_title);

        $query = "SELECT id FROM il_wiki_page" .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
            " AND title = " . $ilDB->quote($a_title, "text");
        $set = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }

        return false;
    }


    /**
     * Get wiki page object for id and title
     */
    public static function getPageIdForTitle(
        int $a_wiki_id,
        string $a_title
    ): ?int {
        global $DIC;

        $ilDB = $DIC->database();

        $a_title = ilWikiUtil::makeDbTitle($a_title);

        $query = "SELECT * FROM il_wiki_page" .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
            " AND title = " . $ilDB->quote($a_title, "text");
        $set = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (int) $rec["id"];
        }

        return null;
    }

    public static function lookupTitle(int $a_page_id): ?string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM il_wiki_page" .
            " WHERE id = " . $ilDB->quote($a_page_id, "integer");
        $set = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (string) $rec["title"];
        }
        return null;
    }

    public static function lookupWikiId(
        int $a_page_id
    ): ?int {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT wiki_id FROM il_wiki_page" .
            " WHERE id = " . $ilDB->quote($a_page_id, "integer");
        $set = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (int) $rec["wiki_id"];
        }

        return null;
    }

    public static function getAllWikiPages(
        int $a_wiki_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $pages = parent::getAllPages("wpg", $a_wiki_id);

        $query = "SELECT * FROM il_wiki_page" .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
            " ORDER BY title";
        $set = $ilDB->query($query);

        $pg = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (isset($pages[$rec["id"]])) {
                $pg[$rec["id"]] = $pages[$rec["id"]];
                $pg[$rec["id"]]["title"] = $rec["title"];
            }
        }

        return $pg;
    }

    public static function getLinksToPage(
        int $a_wiki_id,
        int $a_page_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $sources = ilInternalLink::_getSourcesOfTarget("wpg", $a_page_id, 0);

        $ids = array();
        foreach ($sources as $source) {
            if ($source["type"] === "wpg:pg") {
                $ids[] = $source["id"];
            }
        }
        // get wiki page record
        $query = "SELECT * FROM il_wiki_page wp, page_object p" .
            " WHERE " . $ilDB->in("wp.id", $ids, false, "integer") .
            " AND wp.id = p.page_id AND p.parent_type = " . $ilDB->quote("wpg", "text") .
            " AND wp.wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
            " ORDER BY title";
        $set = $ilDB->query($query);

        $pages = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $pages[] = array_merge($rec, array("user" => $rec["last_change_user"],
                "date" => $rec["last_change"]));
        }

        return $pages;
    }

    public static function getOrphanedPages(
        int $a_wiki_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $pages = self::getAllWikiPages($a_wiki_id);

        $orphaned = array();
        foreach ($pages as $k => $page) {
            $sources = ilInternalLink::_getSourcesOfTarget("wpg", $page["id"], 0);

            $ids = array();
            foreach ($sources as $source) {
                if ($source["type"] === "wpg:pg") {
                    $ids[] = $source["id"];
                }
            }
            $query = "SELECT count(*) cnt FROM il_wiki_page" .
                " WHERE " . $ilDB->in("id", $ids, false, "integer") .
                " AND wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
                " GROUP BY wiki_id";
            $set = $ilDB->query($query);
            $rec = $ilDB->fetchAssoc($set);
            if ($rec && (int) $rec["cnt"] === 0 &&
                ilObjWiki::_lookupStartPage($a_wiki_id) !== $page["title"]) {
                $orphaned[] = $page;
            }
        }

        return $orphaned;
    }

    public static function _wikiPageExists(
        int $a_wiki_id,
        string $a_title
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();

        $a_title = ilWikiUtil::makeDbTitle($a_title);

        $query = "SELECT id FROM il_wiki_page" .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
            " AND title = " . $ilDB->quote($a_title, "text");
        $set = $ilDB->query($query);

        if ($ilDB->fetchAssoc($set)) {
            return true;
        }

        return false;
    }

    public static function getWikiContributors(
        int $a_wiki_id
    ): array {
        return parent::getParentObjectContributors("wpg", $a_wiki_id);
    }

    public static function getWikiPageContributors(
        int $a_page_id
    ): array {
        return parent::getPageContributors("wpg", $a_page_id);
    }

    public function saveInternalLinks(
        DOMDocument $a_domdoc
    ): void {
        $ilDB = $this->db;

        $this->log->debug("start...");
        // *** STEP 1: Standard Processing ***

        parent::saveInternalLinks($a_domdoc);


        // *** STEP 2: Other Pages -> This Page ***

        // Check, whether ANOTHER page links to this page as a "missing" page
        // (this is the case, when this page is created newly)
        $set = $ilDB->queryF(
            "SELECT * FROM il_wiki_missing_page WHERE " .
            " wiki_id = %s AND target_name = %s",
            array("integer", "text"),
            array($this->getWikiId(), ilWikiUtil::makeDbTitle($this->getTitle()))
        );
        while ($anmiss = $ilDB->fetchAssoc($set)) {	// insert internal links instead
            //echo "adding link";
            ilInternalLink::_saveLink(
                "wpg:pg",
                $anmiss["source_id"],
                "wpg",
                $this->getId(),
                0
            );
        }
        //exit;
        // now remove the missing page entries
        $ilDB->manipulateF(
            "DELETE FROM il_wiki_missing_page WHERE " .
            " wiki_id = %s AND target_name = %s",
            array("integer", "text"),
            array($this->getWikiId(), $this->getTitle())
        );


        // *** STEP 3: This Page -> Other Pages ***

        // remove the exising "missing page" links for THIS page (they will be re-inserted below)
        $ilDB->manipulateF(
            "DELETE FROM il_wiki_missing_page WHERE " .
            " wiki_id = %s AND source_id = %s",
            array("integer", "integer"),
            array($this->getWikiId(), $this->getId())
        );

        // collect the wiki links of the page
        $xml = $a_domdoc->saveXML();
        $int_wiki_links = ilWikiUtil::collectInternalLinks($xml, $this->getWikiId(), true);
        foreach ($int_wiki_links as $wlink) {
            $page_id = self::_getPageIdForWikiTitle($this->getWikiId(), $wlink);

            if ($page_id > 0) {		// save internal link for existing page
                ilInternalLink::_saveLink(
                    "wpg:pg",
                    $this->getId(),
                    "wpg",
                    $page_id,
                    0
                );
            } else {		// save missing link for non-existing page
                $ilDB->manipulateF(
                    "DELETE FROM il_wiki_missing_page WHERE" .
                    " wiki_id = %s AND source_id = %s AND target_name = %s",
                    array("integer", "integer", "text"),
                    array($this->getWikiId(), $this->getId(), $wlink)
                );
                $ilDB->manipulateF(
                    "INSERT INTO il_wiki_missing_page (wiki_id, source_id, target_name)" .
                    " VALUES (%s,%s,%s)",
                    array("integer", "integer", "text"),
                    array($this->getWikiId(), $this->getId(), $wlink)
                );
            }
        }
        $this->log->debug("...end");
    }

    /**
     * @deprecated use getPageIdForTitle instead
     */
    public static function _getPageIdForWikiTitle(
        int $a_wiki_id,
        string $a_title
    ): ?int {
        return self::getPageIdForTitle($a_wiki_id, $a_title);
    }

    public static function getPopularPages(
        int $a_wiki_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT wp.*, po.view_cnt as cnt FROM il_wiki_page wp, page_object po" .
            " WHERE wp.wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
            " AND wp.id = po.page_id " .
            " AND po.parent_type = " . $ilDB->quote("wpg", "text") . " " .
            " ORDER BY po.view_cnt";
        $set = $ilDB->query($query);

        $pages = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $pages[] = $rec;
        }

        return $pages;
    }

    public static function countPages(
        int $a_wiki_id
    ): int {
        global $DIC;

        $ilDB = $DIC->database();

        // delete record of table il_wiki_data
        $query = "SELECT count(*) as cnt FROM il_wiki_page" .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer");
        $s = $ilDB->query($query);
        $r = $ilDB->fetchAssoc($s);

        return $r["cnt"];
    }

    public static function getRandomPage(
        int $a_wiki_id
    ): string {
        global $DIC;

        $ilDB = $DIC->database();

        $cnt = self::countPages($a_wiki_id);

        if ($cnt < 1) {
            return "";
        }

        $random = new \ilRandom();
        $rand = $random->int(1, $cnt);

        // delete record of table il_wiki_data
        $ilDB->setLimit(1, $rand);
        $query = "SELECT title FROM il_wiki_page" .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer");
        $s = $ilDB->query($query);
        $r = $ilDB->fetchAssoc($s);

        return $r["title"];
    }

    public static function getNewWikiPages(
        int $a_wiki_id
    ): array {
        $pages = parent::getNewPages("wpg", $a_wiki_id);
        foreach ($pages as $k => $page) {
            $pages[$k]["title"] = self::lookupTitle($page["id"]);
        }

        return $pages;
    }


    /**
     * returns the wiki/object id to a given page id
     */
    public static function lookupObjIdByPage(
        int $a_page_id
    ): ?int {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT wiki_id FROM il_wiki_page" .
            " WHERE id = " . $ilDB->quote($a_page_id, "integer");
        $set = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (int) $rec["wiki_id"];
        }

        return null;
    }

    /**
     * Rename page
     */
    public function rename(
        string $a_new_name
    ): string {
        $ilDB = $this->db;

        // replace unallowed characters
        $a_new_name = str_replace(array("<", ">"), '', $a_new_name);

        // replace multiple whitespace characters by one single space
        $a_new_name = trim(preg_replace('!\s+!', ' ', $a_new_name));

        $page_title = ilWikiUtil::makeDbTitle($a_new_name);
        $pg_id = self::_getPageIdForWikiTitle($this->getWikiId(), $page_title);

        $xml_new_name = str_replace("&", "&amp;", $a_new_name);

        if ($pg_id == 0 || $pg_id == $this->getId()) {
            $sources = ilInternalLink::_getSourcesOfTarget("wpg", $this->getId(), 0);

            foreach ($sources as $s) {
                if ($s["type"] === "wpg:pg" && ilPageObject::_exists("wpg", $s["id"])) {
                    $wpage = new ilWikiPage($s["id"]);

                    $col = ilWikiUtil::collectInternalLinks(
                        $wpage->getXMLContent(),
                        0
                    );
                    $new_content = $wpage->getXMLContent();
                    foreach ($col as $c) {

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

                        $set = $ilDB->query($q = "SELECT " . $ilDB->quote($t1, "text") . " = " . $ilDB->quote($t2, "text") . " isequal");
                        $rec = $ilDB->fetchAssoc($set);

                        if ($rec["isequal"]) {
                            $new_content =
                                str_replace(
                                    "[[" . $c["nt"]->mTextform . "]]",
                                    "[[" . $xml_new_name . "]]",
                                    $new_content
                                );
                            if ($c["text"] != "") {
                                $new_content =
                                    str_replace(
                                        "[[" . $c["text"] . "]]",
                                        "[[" . $xml_new_name . "]]",
                                        $new_content
                                    );
                            }
                            $add = ($c["text"] != "")
                                ? "|" . $c["text"]
                                : "";
                            $new_content =
                                str_replace(
                                    "[[" . $c["nt"]->mTextform . $add . "]]",
                                    "[[" . $xml_new_name . $add . "]]",
                                    $new_content
                                );
                        }
                    }
                    $wpage->setXMLContent($new_content);
                    //echo htmlentities($new_content);
                    $wpage->update();
                }
            }

            if (ilObjWiki::_lookupStartPage($this->getWikiId()) === $this->getTitle()) {
                ilObjWiki::writeStartPage($this->getWikiId(), $a_new_name);
            }

            $this->setTitle($a_new_name);

            $this->update();
        }

        return $a_new_name;
    }


    public function updateNews(
        bool $a_update = false
    ): void {
        $ilUser = $this->user;

        $news_set = new ilSetting("news");
        $default_visibility = ($news_set->get("default_visibility") != "")
                ? $news_set->get("default_visibility")
                : "users";

        if (!$a_update) {
            $news_item = new ilNewsItem();
            $news_item->setContext(
                $this->getWikiId(),
                "wiki",
                $this->getId(),
                "wpg"
            );
            $news_item->setPriority(NEWS_NOTICE);
            $news_item->setTitle($this->getTitle());
            $news_item->setContentTextIsLangVar(true);
            $news_item->setContent("wiki_news_page_created");
            $news_item->setUserId($ilUser->getId());
            $news_item->setVisibility($default_visibility);
            $news_item->create();
        } else {
            // get last news item of the day (if existing)
            $news_id = ilNewsItem::getLastNewsIdForContext(
                $this->getWikiId(),
                "wiki",
                $this->getId(),
                "wpg",
                true
            );

            if ($news_id > 0) {
                $news_item = new ilNewsItem($news_id);
                $news_item->setContent("wiki_news_page_changed");
                $news_item->setUserId($ilUser->getId());
                $news_item->setTitle($this->getTitle());
                $news_item->setContentTextIsLangVar(true);
                $news_item->update(true);
            } else {
                $news_item = new ilNewsItem();
                $news_item->setContext(
                    $this->getWikiId(),
                    "wiki",
                    $this->getId(),
                    "wpg"
                );
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

    public static function getGotoForWikiPageTarget(
        string $a_target,
        bool $a_offline = false
    ): string {
        if (!$a_offline) {
            $href = "./goto.php?target=wiki_wpage_" . $a_target;
        } else {
            $href = ILIAS_HTTP_PATH . "/goto.php?target=wiki_wpage_" . $a_target;
        }
        return $href;
    }


    /**
     * Get content templates
     * @return array array of arrays with "id" => page id (int), "parent_type" => parent type (string), "title" => title (string)
     */
    public function getContentTemplates(): array
    {
        $wt = new ilWikiPageTemplate($this->getWikiId());
        $templates = array();
        foreach ($wt->getAllInfo(ilWikiPageTemplate::TYPE_ADD_TO_PAGE) as $t) {
            $templates[] = array("id" => $t["wpage_id"], "parent_type" => "wpg", "title" => $t["title"]);
        }
        return $templates;
    }

    public static function getPagesForSearch(
        int $a_wiki_id,
        string $a_term
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT DISTINCT title FROM il_wiki_page" .
            " WHERE wiki_id = " . $ilDB->quote($a_wiki_id, "integer") .
            " AND " . $ilDB->like("title", "text", "%" . $a_term . "%") .
            " ORDER by title");
        $res = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $res[] = $rec["title"];
        }

        return $res;
    }

    public static function lookupAdvancedMetadataHidden(
        int $a_page_id
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM il_wiki_page" .
            " WHERE id = " . $ilDB->quote($a_page_id, "integer");
        $set = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (bool) $rec["hide_adv_md"];
        }

        return false;
    }
}
