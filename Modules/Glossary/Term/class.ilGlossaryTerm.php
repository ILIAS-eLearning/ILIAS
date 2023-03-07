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
 * Glossary terms
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryTerm
{
    protected string $type;
    protected ilDBInterface $db;
    public ilLanguage $lng;
    public ilGlobalTemplateInterface $tpl;
    public int $id = 0;
    public ilObjGlossary $glossary;
    public string $term = "";
    public string $language = "";
    public int $glo_id = 0;
    public string $import_id = "";
    public string $short_text = "";
    public int $short_text_dirty = 0;
    public ilGlossaryDefPage $page_object;
    protected ilAppEventHandler $event_handler;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];

        $this->lng = $lng;
        $this->tpl = $tpl;

        $this->id = $a_id;
        $this->type = "term";
        if ($a_id != 0) {
            $this->read();
        }
        $this->event_handler = $DIC->event();
    }

    public function read(): void
    {
        $ilDB = $this->db;

        $q = "SELECT * FROM glossary_term WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $term_set = $ilDB->query($q);
        $term_rec = $ilDB->fetchAssoc($term_set);

        $this->setTerm((string) $term_rec["term"]);
        $this->setImportId((string) $term_rec["import_id"]);
        $this->setLanguage((string) $term_rec["language"]);
        $this->setGlossaryId((int) $term_rec["glo_id"]);

        $this->page_object = new ilGlossaryDefPage($this->getId());
    }

    public static function _getIdForImportId(
        string $a_import_id
    ): int {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_import_id == "") {
            return 0;
        }

        $q = "SELECT * FROM glossary_term WHERE import_id = " .
            $ilDB->quote($a_import_id, "text") .
            " ORDER BY create_date DESC";
        $term_set = $ilDB->query($q);
        while ($term_rec = $ilDB->fetchAssoc($term_set)) {
            $glo_id = self::_lookGlossaryID($term_rec["id"]);

            $ref_ids = ilObject::_getAllReferences($glo_id);	// will be 0 if import of lm is in progress (new import)
            if (count($ref_ids) == 0 || ilObject::_hasUntrashedReference($glo_id)) {
                return (int) $term_rec["id"];
            }
        }

        return 0;
    }


    /**
     * checks whether a glossary term with specified id exists or not
     */
    public static function _exists(int $a_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (is_int(strpos($a_id, "_"))) {
            $a_id = ilInternalLink::_extractObjIdOfTarget($a_id);
        }

        $q = "SELECT * FROM glossary_term WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $obj_set = $ilDB->query($q);
        if ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * set glossary term id (= glossary item id)
     */
    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setGlossary(
        ilObjGlossary $a_glossary
    ): void {
        $this->glossary = $a_glossary;
        $this->setGlossaryId($a_glossary->getId());
    }

    public function setGlossaryId(
        int $a_glo_id
    ): void {
        $this->glo_id = $a_glo_id;
    }

    public function getGlossaryId(): int
    {
        return $this->glo_id;
    }

    public function setTerm(string $a_term): void
    {
        $this->term = $a_term;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function setLanguage(
        string $a_language
    ): void {
        $this->language = $a_language;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setImportId(string $a_import_id): void
    {
        $this->import_id = $a_import_id;
    }

    public function getImportId(): string
    {
        return $this->import_id;
    }

    public function setShortText(string $a_text): void
    {
        $this->short_text = $this->shortenShortText($a_text);
    }

    public function getShortText(): string
    {
        return $this->short_text;
    }

    public function setShortTextDirty(int $a_val): void
    {
        $this->short_text_dirty = $a_val;
    }

    public function getShortTextDirty(): int
    {
        return $this->short_text_dirty;
    }

    public function assignPageObject(ilGlossaryDefPage $a_page_object): void
    {
        $this->page_object = $a_page_object;
    }

    public function getPageObject(): ilGlossaryDefPage
    {
        return $this->page_object;
    }

    public function create(bool $a_omit_page_creation = false): void
    {
        $ilDB = $this->db;

        $this->setId($ilDB->nextId("glossary_term"));
        $ilDB->manipulate("INSERT INTO glossary_term" .
            " (id, glo_id, term, language, import_id, create_date, last_update, short_text, short_text_dirty)" .
            " VALUES (" .
            $ilDB->quote($this->getId(), "integer") . ", " .
            $ilDB->quote($this->getGlossaryId(), "integer") . ", " .
            $ilDB->quote($this->getTerm(), "text") . ", " .
            $ilDB->quote($this->getLanguage(), "text") . ", " .
            $ilDB->quote($this->getImportId(), "text") . ", " .
            $ilDB->now() . ", " .
            $ilDB->now() . ", " .
            $ilDB->quote($this->getShortText()) . ", " .
            $ilDB->quote($this->getShortTextDirty()) . ")");

        if (!$a_omit_page_creation) {
            $this->page_object = new ilGlossaryDefPage();
            $this->page_object->setId($this->getId());
            $this->page_object->setParentId($this->getGlossaryId());
            $this->page_object->create(false);
        }
    }

    /**
     * delete glossary term
     */
    public function delete(): void
    {
        $ilDB = $this->db;

        // delete term references
        ilGlossaryTermReferences::deleteReferencesOfTerm($this->getId());

        // delete glossary_term record
        $ilDB->manipulate("DELETE FROM glossary_term " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));

        $this->page_object->delete();

        // delete flashcard entries
        $this->event_handler->raise("Modules/Glossary", "deleteTerm", ["term_id" => $this->getId()]);
    }

    public function update(): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate("UPDATE glossary_term SET " .
            " glo_id = " . $ilDB->quote($this->getGlossaryId(), "integer") . ", " .
            " term = " . $ilDB->quote($this->getTerm(), "text") . ", " .
            " import_id = " . $ilDB->quote($this->getImportId(), "text") . ", " .
            " language = " . $ilDB->quote($this->getLanguage(), "text") . ", " .
            " last_update = " . $ilDB->now() . ", " .
            " short_text = " . $ilDB->quote($this->getShortText(), "text") . ", " .
            " short_text_dirty = " . $ilDB->quote($this->getShortTextDirty(), "integer") . " " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
    }

    /**
     * Shorten short text
     */
    public function shortenShortText(string $text): string
    {
        $a_length = 196;

        if ($this->getId() > 0) {
            $glo_id = self::_lookGlossaryID($this->getId());
            $snippet_length = ilObjGlossary::lookupSnippetLength($glo_id);
            if ($snippet_length > 0) {
                $a_length = $snippet_length;
            }
        }

        $text = str_replace("<br/>", "<br>", $text);
        $text = strip_tags($text, "<br>");
        $offset = 0;
        if (is_int(strpos(substr($text, $a_length - 16 - 5, 10), "[tex]"))) {
            $offset = 5;
        }
        $short = ilStr::shortenTextExtended($text, $a_length - 16 + $offset, true);

        // make short text longer, if tex end tag is missing
        $ltexs = strrpos($short, "[tex]");
        $ltexe = strrpos($short, "[/tex]");
        if ($ltexs > $ltexe) {
            $ltexe = strpos($text, "[/tex]", $ltexs);
            if ($ltexe > 0) {
                $text = ilStr::shortenTextExtended($text, $ltexe + 6, true);
            }
        }

        $short = ilStr::shortenTextExtended($text, $a_length, true);

        return $short;
    }

    public function updateShortText(): void
    {
        $this->page_object->buildDom();
        $text = $this->page_object->getFirstParagraphText();
        $short = $this->shortenShortText($text);

        $this->setShortText($short);
        $this->setShortTextDirty(0);
        $this->update();
    }

    /**
     * Set all short texts of glossary dirty
     * (e.g. if length is changed in settings)
     */
    public static function setShortTextsDirty(int $a_glo_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $term_ids = self::getTermsOfGlossary($a_glo_id);

        foreach ($term_ids as $term_id) {
            $ilDB->manipulate(
                "UPDATE glossary_term SET " .
                " short_text_dirty = " . $ilDB->quote(1, "integer") .
                " WHERE id = " . $ilDB->quote($term_id, "integer")
            );
        }
    }

    /**
     * Set short texts dirty (for all glossaries)
     */
    public static function setShortTextsDirtyGlobally(): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "UPDATE glossary_term SET " .
            " short_text_dirty = " . $ilDB->quote(1, "integer")
        );
    }

    /**
     * get glossary id form term id
     */
    public static function _lookGlossaryID(int $term_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM glossary_term WHERE id = " .
            $ilDB->quote($term_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return (int) ($obj_rec["glo_id"] ?? 0);
    }

    /**
     * get glossary term
     */
    public static function _lookGlossaryTerm(int $term_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM glossary_term WHERE id = " .
            $ilDB->quote($term_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec["term"] ?? "";
    }

    /**
     * lookup term language
     */
    public static function _lookLanguage(int $term_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM glossary_term WHERE id = " .
            $ilDB->quote($term_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec["language"];
    }

    /**
     * get definition short text
     */
    public static function _lookShortText(int $term_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM glossary_term WHERE id = " .
            $ilDB->quote($term_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec["short_text"] ?? "";
    }

    /**
     * get definition short text dirty
     */
    public static function _lookShortTextDirty(int $term_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM glossary_term WHERE id = " .
            $ilDB->quote($term_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return (int) ($obj_rec["short_text_dirty"] ?? 0);
    }

    /**
     * Get all terms for given set of glossary ids.
     */
    public static function getTermList(
        array $a_glo_ref_id,
        string $searchterm = "",
        string $a_first_letter = "",
        string $a_def = "",
        int $a_tax_node = 0,
        bool $a_add_amet_fields = false,
        array $a_amet_filter = null,
        bool $a_include_references = false
    ): array {
        global $DIC;

        if (count($a_glo_ref_id) > 1) {
            $a_glo_id = array_map(static function ($id): int {
                return ilObject::_lookupObjectId($id);
            }, $a_glo_ref_id);
        } else {
            $a_glo_id = ilObject::_lookupObjectId(current($a_glo_ref_id));
        }
        $ilDB = $DIC->database();

        $join = $in = "";

        $terms = array();

        // get all term ids under taxonomy node (if given)
        if ($a_tax_node > 1) {
            $tax_ids = ilObjTaxonomy::getUsageOfObject($a_glo_id);
            if (count($tax_ids) > 0) {
                $items = ilObjTaxonomy::getSubTreeItems("glo", $a_glo_id, "term", $tax_ids[0], $a_tax_node);
                $sub_tree_ids = array();
                foreach ($items as $i) {
                    $sub_tree_ids[] = $i["item_id"];
                }
                $in = " AND " . $ilDB->in("gt.id", $sub_tree_ids, false, "integer");
            }
        }

        if ($a_def != "") {
            // meta glossary?
            if (is_array($a_glo_id)) {
                $glo_where = $ilDB->in("page_object.parent_id", $a_glo_id, false, "integer");
            } else {
                $glo_where = " page_object.parent_id = " . $ilDB->quote($a_glo_id, "integer");
            }

            $join = " JOIN page_object ON (" .
            $glo_where .
            " AND page_object.parent_type = " . $ilDB->quote("term", "text") .
            " AND page_object.page_id = gt.id" .
            " AND " . $ilDB->like("page_object.content", "text", "%" . $a_def . "%") .
            ")";
        }

        $searchterm = (!empty($searchterm))
            ? " AND " . $ilDB->like("term", "text", "%" . $searchterm . "%") . " "
            : "";

        if ($a_first_letter != "") {
            $searchterm .= " AND " . $ilDB->upper($ilDB->substr("term", 1, 1)) . " = " . $ilDB->upper($ilDB->quote($a_first_letter, "text")) . " ";
        }

        // include references
        $where_glo_id_or = "";
        if ($a_include_references) {
            $join .= " LEFT JOIN glo_term_reference tr ON (gt.id = tr.term_id) ";
            if (is_array($a_glo_id)) {
                $where_glo_id_or = " OR " . $ilDB->in("tr.glo_id", $a_glo_id, false, "integer");
            } else {
                $where_glo_id_or = " OR tr.glo_id = " . $ilDB->quote($a_glo_id, "integer");
            }
        }

        // meta glossary
        if (is_array($a_glo_id)) {
            $where = "(" . $ilDB->in("gt.glo_id", $a_glo_id, false, "integer") . $where_glo_id_or . ")";
        } else {
            $where = "(gt.glo_id = " . $ilDB->quote($a_glo_id, "integer") . $where_glo_id_or . ")";
        }

        $where .= $in;


        $q = "SELECT DISTINCT(gt.term), gt.id, gt.glo_id, gt.language, gt.short_text, gt.short_text_dirty FROM glossary_term gt " .
            $join . " WHERE " . $where . $searchterm . " ORDER BY gt.term, gt.id";

        $term_set = $ilDB->query($q);
        $glo_ids = array();
        while ($term_rec = $ilDB->fetchAssoc($term_set)) {
            $terms[] = array("term" => $term_rec["term"],
                "language" => $term_rec["language"], "id" => $term_rec["id"], "glo_id" => $term_rec["glo_id"],
                "short_text" => strip_tags($term_rec["short_text"], "<br>"),
                "short_text_dirty" => $term_rec["short_text_dirty"]);
            $glo_ids[] = $term_rec["glo_id"];
        }

        // add advanced metadata
        if (($a_add_amet_fields || is_array($a_amet_filter)) && count($a_glo_ref_id) == 1) {
            $terms = ilAdvancedMDValues::queryForRecords(current($a_glo_ref_id), "glo", "term", $glo_ids, "term", $terms, "glo_id", "id", $a_amet_filter);
        }
        return $terms;
    }

    public static function getFirstLetters(
        array $a_glo_id,
        int $a_tax_node = 0
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        // meta glossary
        if (count($a_glo_id) > 1) {
            $where = $ilDB->in("glo_id", $a_glo_id, false, "integer");
        } else {
            $a_glo_id = current($a_glo_id);
            $where = " glo_id = " . $ilDB->quote($a_glo_id, "integer") . " ";
            $in = "";
            // get all term ids under taxonomy node (if given)
            if ($a_tax_node > 1) {
                $tax_ids = ilObjTaxonomy::getUsageOfObject($a_glo_id);
                if (count($tax_ids) > 0) {
                    $items = ilObjTaxonomy::getSubTreeItems("glo", $a_glo_id, "term", $tax_ids[0], $a_tax_node);
                    $sub_tree_ids = array();
                    foreach ($items as $i) {
                        $sub_tree_ids[] = $i["item_id"];
                    }
                    $in = " AND " . $ilDB->in("id", $sub_tree_ids, false, "integer");
                }
            }

            $where .= $in;
        }

        $q = "SELECT DISTINCT " . $ilDB->upper($ilDB->substr("term", 1, 1)) . " let FROM glossary_term WHERE " . $where . " ORDER BY let";
        $let_set = $ilDB->query($q);

        $let = array();
        while ($let_rec = $ilDB->fetchAssoc($let_set)) {
            $let[$let_rec["let"]] = $let_rec["let"];
        }
        return $let;
    }

    public function exportXML(
        ilXmlWriter $a_xml_writer,
        int $a_inst
    ): void {
        $attrs = array();
        $attrs["Language"] = $this->getLanguage();
        $attrs["Id"] = "il_" . IL_INST_ID . "_git_" . $this->getId();
        $a_xml_writer->xmlStartTag("GlossaryItem", $attrs);

        $attrs = array();
        $a_xml_writer->xmlElement("GlossaryTerm", $attrs, $this->getTerm());

        $a_xml_writer->xmlEndTag("GlossaryItem");
    }

    public static function getNumberOfUsages(int $a_term_id): int
    {
        return count(self::getUsages($a_term_id));
    }

    public static function getUsages(int $a_term_id): array
    {
        $usages = (ilInternalLink::_getSourcesOfTarget("git", $a_term_id, 0));

        foreach (ilGlossaryTermReferences::lookupReferencesOfTerm($a_term_id) as $glo_id) {
            $usages["glo:termref:" . $glo_id . ":-"] = array(
                "type" => "glo:termref",
                "id" => $glo_id,
                "lang" => "-"
            );
        }

        return $usages;
    }

    /**
     * Copy a term to a glossary
     * @return int new term id
     */
    public static function _copyTerm(
        int $a_term_id,
        int $a_glossary_id
    ): int {
        $old_term = new ilGlossaryTerm($a_term_id);

        // copy the term
        $new_term = new ilGlossaryTerm();
        $new_term->setTerm($old_term->getTerm());
        $new_term->setLanguage($old_term->getLanguage());
        $new_term->setGlossaryId($a_glossary_id);
        $new_term->setShortText($old_term->getShortText());
        $new_term->setShortTextDirty($old_term->getShortTextDirty());
        $new_term->create();

        // copy meta data
        $md = new ilMD(
            $old_term->getGlossaryId(),
            $old_term->getPageObject()->getId(),
            $old_term->getPageObject()->getParentType()
        );
        $new_md = $md->cloneMD(
            $a_glossary_id,
            $new_term->getPageObject()->getId(),
            $old_term->getPageObject()->getParentType()
        );

        $new_page = $new_term->getPageObject();
        $old_term->getPageObject()->copy($new_page->getId(), $new_page->getParentType(), $new_page->getParentId(), true);

        // adv metadata
        $old_recs = ilAdvancedMDRecord::_getSelectedRecordsByObject("glo", $old_term->getGlossaryId(), "term");
        $new_recs = ilAdvancedMDRecord::_getSelectedRecordsByObject("glo", $a_glossary_id, "term");
        foreach ($old_recs as $old_record_obj) {
            reset($new_recs);
            foreach ($new_recs as $new_record_obj) {
                if ($old_record_obj->getRecordId() == $new_record_obj->getRecordId()) {
                    foreach (ilAdvancedMDFieldDefinition::getInstancesByRecordId($old_record_obj->getRecordId()) as $def) {
                        // now we need to copy $def->getFieldId() values from old term to new term
                        // how?
                        // clone values

                        $source_primary = array("obj_id" => array("integer", $old_term->getGlossaryId()));
                        $source_primary["sub_type"] = array("text", "term");
                        $source_primary["sub_id"] = array("integer", $old_term->getId());
                        $source_primary["field_id"] = array("integer", $def->getFieldId());
                        $target_primary = array("obj_id" => array("integer", $new_term->getGlossaryId()));
                        $target_primary["sub_type"] = array("text", "term");
                        $target_primary["sub_id"] = array("integer", $new_term->getId());

                        ilADTFactory::getInstance()->initActiveRecordByType();
                        $has_cloned = ilADTActiveRecordByType::cloneByPrimary(
                            "adv_md_values",
                            array(
                                "obj_id" => "integer",
                                "sub_type" => "text",
                                "sub_id" => "integer",
                                "field_id" => "integer"
                            ),
                            $source_primary,
                            $target_primary,
                            array("disabled" => "integer")
                        );
                    }
                }
            }
        }

        return $new_term->getId();
    }

    /**
     * @return int[] term ids
     */
    public static function getTermsOfGlossary(
        int $a_glo_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT id FROM glossary_term WHERE " .
            " glo_id = " . $ilDB->quote($a_glo_id, "integer")
        );
        $ids = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ids[] = (int) $rec["id"];
        }
        return $ids;
    }
}
