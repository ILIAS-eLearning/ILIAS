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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryDefinition
{
    protected array $files_contained = [];
    protected array $mobs_contained = [];
    protected string $description = "";
    protected string $title = "";
    protected ilDBInterface $db;
    protected ilObjUser $user;
    public ilLanguage $lng;
    public ilGlobalTemplateInterface $tpl;
    public int $id = 0;
    public int $term_id = 0;
    public int $glo_id = 0;
    public ilGlossaryDefPage $page_object;
    public string $short_text = "";
    public int $nr = 0;
    public bool $short_text_dirty = false;

    public function __construct(
        int $a_id = 0
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];

        $this->lng = $lng;
        $this->tpl = $tpl;

        $this->id = $a_id;
        if ($a_id != 0) {
            $this->read();
        }
    }

    public function read(): void
    {
        $ilDB = $this->db;

        $q = "SELECT * FROM glossary_definition WHERE id = " .
            $ilDB->quote($this->id, "integer");
        $def_set = $ilDB->query($q);
        $def_rec = $ilDB->fetchAssoc($def_set);

        $this->setTermId((int) $def_rec["term_id"]);
        $this->setShortText((string) $def_rec["short_text"]);
        $this->setNr((int) $def_rec["nr"]);
        $this->setShortTextDirty((bool) $def_rec["short_text_dirty"]);

        $this->page_object = new ilGlossaryDefPage($this->id);
    }

    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return "gdf";
    }

    public function setTermId(int $a_term_id): void
    {
        $this->term_id = $a_term_id;
    }

    public function getTermId(): int
    {
        return $this->term_id;
    }

    public function setShortText(string $a_text): void
    {
        $this->short_text = $this->shortenShortText($a_text);
    }

    public function getShortText(): string
    {
        return $this->short_text;
    }

    public function setNr(int $a_nr): void
    {
        $this->nr = $a_nr;
    }

    public function getNr(): int
    {
        return $this->nr;
    }

    public function assignPageObject(ilGlossaryDefPage $a_page_object): void
    {
        $this->page_object = $a_page_object;
    }

    public function getPageObject(): ilGlossaryDefPage
    {
        return $this->page_object;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $a_description): void
    {
        $this->description = $a_description;
    }

    public function setShortTextDirty(bool $a_val): void
    {
        $this->short_text_dirty = $a_val;
    }

    public function getShortTextDirty(): bool
    {
        return $this->short_text_dirty;
    }
    public function create(
        bool $a_upload = false,
        bool $a_omit_page_creation = false
    ): void {
        $ilDB = $this->db;

        $term = new ilGlossaryTerm($this->getTermId());

        $this->setId($ilDB->nextId("glossary_definition"));

        $ilAtomQuery = $ilDB->buildAtomQuery();
        $ilAtomQuery->addTableLock('glossary_definition');

        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) {
            // get maximum definition number
            $q = "SELECT max(nr) AS max_nr FROM glossary_definition WHERE term_id = " .
                $ilDB->quote($this->getTermId(), "integer");
            $max_set = $ilDB->query($q);
            $max_rec = $ilDB->fetchAssoc($max_set);
            $max = (int) $max_rec["max_nr"];

            // insert new definition record
            $ilDB->manipulate("INSERT INTO glossary_definition (id, term_id, short_text, nr, short_text_dirty)" .
                " VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($this->getTermId(), "integer") . "," .
                $ilDB->quote($this->getShortText(), "text") . ", " .
                $ilDB->quote(($max + 1), "integer") . ", " .
                $ilDB->quote($this->getShortTextDirty(), "integer") .
                ")");
        });

        $ilAtomQuery->run();

        // get number
        $q = "SELECT nr FROM glossary_definition WHERE id = " .
            $ilDB->quote($this->id, "integer");
        $def_set = $ilDB->query($q);
        $def_rec = $ilDB->fetchAssoc($def_set);
        $this->setNr($def_rec["nr"]);

        // meta data will be created by
        // import parser
        if (!$a_upload) {
            $this->createMetaData();
        }

        if (!$a_omit_page_creation) {
            $this->page_object = new ilGlossaryDefPage();
            $this->page_object->setId($this->getId());
            $this->page_object->setParentId($term->getGlossaryId());
            $this->page_object->create(false);
        }
    }

    public function delete(): void
    {
        $ilDB = $this->db;

        $ilAtomQuery = $ilDB->buildAtomQuery();
        $ilAtomQuery->addTableLock("glossary_definition");

        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) {
            // be sure to get the right number
            $q = "SELECT * FROM glossary_definition WHERE id = " .
                $ilDB->quote($this->id, "integer");
            $def_set = $ilDB->query($q);
            $def_rec = $ilDB->fetchAssoc($def_set);
            $this->setNr($def_rec["nr"]);

            // update numbers of other definitions
            $ilDB->manipulate("UPDATE glossary_definition SET " .
                " nr = nr - 1 " .
                " WHERE term_id = " . $ilDB->quote($this->getTermId(), "integer") . " " .
                " AND nr > " . $ilDB->quote($this->getNr(), "integer"));

            // delete current definition
            $ilDB->manipulate("DELETE FROM glossary_definition " .
                " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        });
        $ilAtomQuery->run();

        // delete page and meta data
        $this->page_object->delete();

        // delete meta data
        $this->deleteMetaData();
    }


    public function moveUp(): void
    {
        $ilDB = $this->db;

        $ilAtomQuery = $ilDB->buildAtomQuery();
        $ilAtomQuery->addTableLock('glossary_definition');

        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) {
            // be sure to get the right number
            $q = "SELECT * FROM glossary_definition WHERE id = " .
                $ilDB->quote($this->id, "integer");
            $def_set = $ilDB->query($q);
            $def_rec = $ilDB->fetchAssoc($def_set);
            $this->setNr($def_rec["nr"]);

            if ($this->getNr() < 2) {
                return;
            }

            // update numbers of other definitions
            $ilDB->manipulate("UPDATE glossary_definition SET " .
                " nr = nr + 1 " .
                " WHERE term_id = " . $ilDB->quote($this->getTermId(), "integer") . " " .
                " AND nr = " . $ilDB->quote(($this->getNr() - 1), "integer"));

            // delete current definition
            $ilDB->manipulate("UPDATE glossary_definition SET " .
                " nr = nr - 1 " .
                " WHERE term_id = " . $ilDB->quote($this->getTermId(), "integer") . " " .
                " AND id = " . $ilDB->quote($this->getId(), "integer"));
        });
        $ilAtomQuery->run();
    }

    public function moveDown(): void
    {
        $ilDB = $this->db;

        $ilAtomQuery = $ilDB->buildAtomQuery();
        $ilAtomQuery->addTableLock('glossary_definition');

        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) {
            // be sure to get the right number
            $q = "SELECT * FROM glossary_definition WHERE id = " .
                $ilDB->quote($this->id, "integer");
            $def_set = $ilDB->query($q);
            $def_rec = $ilDB->fetchAssoc($def_set);
            $this->setNr($def_rec["nr"]);

            // get max number
            $q = "SELECT max(nr) as max_nr FROM glossary_definition WHERE term_id = " .
                $ilDB->quote($this->getTermId(), "integer");
            $max_set = $ilDB->query($q);
            $max_rec = $ilDB->fetchAssoc($max_set);

            if ($this->getNr() >= $max_rec["max_nr"]) {
                return;
            }

            // update numbers of other definitions
            $ilDB->manipulate("UPDATE glossary_definition SET " .
                " nr = nr - 1 " .
                " WHERE term_id = " . $ilDB->quote($this->getTermId(), "integer") . " " .
                " AND nr = " . $ilDB->quote(($this->getNr() + 1), "integer"));

            // delete current definition
            $ilDB->manipulate("UPDATE glossary_definition SET " .
                " nr = nr + 1 " .
                " WHERE term_id = " . $ilDB->quote($this->getTermId(), "integer") . " " .
                " AND id = " . $ilDB->quote($this->getId(), "integer"));
        });

        $ilAtomQuery->run();
    }

    public function update(): void
    {
        $ilDB = $this->db;

        $this->updateMetaData();

        $ilDB->manipulate("UPDATE glossary_definition SET " .
            " term_id = " . $ilDB->quote($this->getTermId(), "integer") . ", " .
            " nr = " . $ilDB->quote($this->getNr(), "integer") . ", " .
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

        if ($this->getTermId() > 0) {
            $glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
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
        $this->setShortTextDirty(false);
        $this->update();
    }

    public static function getDefinitionList(int $a_term_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $defs = array();
        $q = "SELECT * FROM glossary_definition WHERE term_id = " .
            $ilDB->quote($a_term_id, "integer") .
            " ORDER BY nr";
        $def_set = $ilDB->query($q);
        while ($def_rec = $ilDB->fetchAssoc($def_set)) {
            $defs[] = array("term_id" => $def_rec["term_id"],
                "page_id" => (int) ($def_rec["page_id"] ?? 0), "id" => $def_rec["id"],
                "short_text" => strip_tags($def_rec["short_text"], "<br>"),
                "nr" => $def_rec["nr"],
                "short_text_dirty" => $def_rec["short_text_dirty"]);
        }
        return $defs;
    }

    public function exportXML(
        ilXmlWriter $a_xml_writer,
        int $a_inst
    ): void {
        $attrs = array();
        $a_xml_writer->xmlStartTag("Definition", $attrs);

        $this->exportXMLMetaData($a_xml_writer);
        $this->exportXMLDefinition($a_xml_writer, $a_inst);

        $a_xml_writer->xmlEndTag("Definition");
    }


    public function exportXMLMetaData(
        ilXmlWriter $a_xml_writer
    ): void {
        $glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
        $md2xml = new ilMD2XML($glo_id, $this->getId(), $this->getType());
        $md2xml->setExportMode(true);
        $md2xml->startExport();
        $a_xml_writer->appendXML($md2xml->getXML());
    }

    public function modifyExportIdentifier(
        string $a_tag,
        string $a_param,
        string $a_value
    ): string {
        if ($a_tag == "Identifier" && $a_param == "Entry") {
            $a_value = "il_" . IL_INST_ID . "_gdf_" . $this->getId();
        }

        return $a_value;
    }

    /**
     * export page objects meta data to xml
     */
    public function exportXMLDefinition(
        ilXmlWriter $a_xml_writer,
        int $a_inst = 0
    ): void {
        $this->page_object->buildDom();
        $this->page_object->insertInstIntoIDs($a_inst);
        $this->mobs_contained = $this->page_object->collectMediaObjects(false);
        $this->files_contained = ilPCFileList::collectFileItems($this->page_object, $this->page_object->getDomDoc());
        $xml = $this->page_object->getXMLFromDom(false, false, false, "", true);
        $xml = str_replace("&", "&amp;", $xml);
        $a_xml_writer->appendXML($xml);

        $this->page_object->freeDom();
    }

    public function createMetaData(): void
    {
        $glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
        $lang = ilGlossaryTerm::_lookLanguage($this->getTermId());
        $md_creator = new ilMDCreator($glo_id, $this->getId(), $this->getType());
        $md_creator->setTitle($this->getTitle());
        $md_creator->setTitleLanguage($lang);
        $md_creator->setDescription($this->getDescription());
        $md_creator->setDescriptionLanguage($lang);
        $md_creator->setKeywordLanguage($lang);
        $md_creator->setLanguage($lang);
        $md_creator->create();
    }

    public function updateMetaData(): void
    {
        $glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
        $md = new ilMD($glo_id, $this->getId(), $this->getType());
        $md_gen = $md->getGeneral();
        $md_gen->setTitle($this->getTitle());

        // sets first description (maybe not appropriate)
        $md_des_ids = $md_gen->getDescriptionIds();
        if (count($md_des_ids) > 0) {
            $md_des = $md_gen->getDescription($md_des_ids[0]);
            $md_des->setDescription($this->getDescription());
            $md_des->update();
        }
        $md_gen->update();
    }

    public function deleteMetaData(): void
    {
        // Delete meta data
        $glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
        $md = new ilMD($glo_id, $this->getId(), $this->getType());
        $md->deleteAll();
    }

    /**
     * Meta data update listener
     *
     * Important note: Do never call create() or update()
     * method of ilObject here. It would result in an
     * endless loop: update object -> update meta -> update
     * object -> ...
     * Use static _writeTitle() ... methods instead.
     *
     * Even if this is not stored to db, it should be stored to the object
     * e.g. for during import parsing
     */
    public function MDUpdateListener(string $a_element): bool
    {
        switch ($a_element) {
            case 'General':

                // Update Title and description
                $glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
                $md = new ilMD($glo_id, $this->getId(), $this->getType());
                $md_gen = $md->getGeneral();

                //ilObject::_writeTitle($this->getId(),$md_gen->getTitle());
                $this->setTitle($md_gen->getTitle());

                foreach ($md_gen->getDescriptionIds() as $id) {
                    $md_des = $md_gen->getDescription($id);
                    //ilObject::_writeDescription($this->getId(),$md_des->getDescription());
                    $this->setDescription($md_des->getDescription());
                    break;
                }

                break;

            default:
        }
        return true;
    }

    /**
     * Looks up term id for a definition id
     * @param	int		$a_def_id		definition id
     */
    public static function _lookupTermId(int $a_def_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM glossary_definition WHERE id = " .
            $ilDB->quote($a_def_id, "integer");
        $def_set = $ilDB->query($q);
        $def_rec = $ilDB->fetchAssoc($def_set);

        return $def_rec["term_id"];
    }

    /**
     * Set all short texts of glossary dirty
     * (e.g. if length is changed in settings)
     */
    public static function setShortTextsDirty(int $a_glo_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $term_ids = ilGlossaryTerm::getTermsOfGlossary($a_glo_id);

        foreach ($term_ids as $term_id) {
            $ilDB->manipulate(
                "UPDATE glossary_definition SET " .
                " short_text_dirty = " . $ilDB->quote(1, "integer") .
                " WHERE term_id = " . $ilDB->quote($term_id, "integer")
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
            "UPDATE glossary_definition SET " .
            " short_text_dirty = " . $ilDB->quote(1, "integer")
        );
    }
}
