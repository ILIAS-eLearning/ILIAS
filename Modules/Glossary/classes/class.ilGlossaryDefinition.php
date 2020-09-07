<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Glossary/classes/class.ilGlossaryDefPage.php");

/**
* Class ilGlossaryDefinition
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesGlossary
*/
class ilGlossaryDefinition
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilObjUser
     */
    protected $user;

    public $lng;
    public $tpl;

    public $id;
    public $term_id;
    public $glo_id;
    public $page_object;
    public $short_text;
    public $nr;
    public $short_text_dirty = false;

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_id = 0)
    {
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

    /**
    * read data of content object
    */
    public function read()
    {
        $ilDB = $this->db;
        
        $q = "SELECT * FROM glossary_definition WHERE id = " .
            $ilDB->quote($this->id, "integer");
        $def_set = $ilDB->query($q);
        $def_rec = $ilDB->fetchAssoc($def_set);

        $this->setTermId($def_rec["term_id"]);
        $this->setShortText($def_rec["short_text"]);
        $this->setNr($def_rec["nr"]);
        $this->setShortTextDirty($def_rec["short_text_dirty"]);

        $this->page_object = new ilGlossaryDefPage($this->id);
    }

    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return "gdf";
    }

    public function setTermId($a_term_id)
    {
        $this->term_id = $a_term_id;
    }

    public function getTermId()
    {
        return $this->term_id;
    }

    public function setShortText($a_text)
    {
        $this->short_text = $this->shortenShortText($a_text);
    }

    public function getShortText()
    {
        return $this->short_text;
    }

    public function setNr($a_nr)
    {
        $this->nr = $a_nr;
    }

    public function getNr()
    {
        return $this->nr;
    }

    public function assignPageObject(&$a_page_object)
    {
        $this->page_object = $a_page_object;
    }

    public function &getPageObject()
    {
        return $this->page_object;
    }

    /**
    * get title of content object
    *
    * @return	string		title
    */
    public function getTitle()
    {
        return $this->title;
    }

    /**
    * set title of content object
    */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
     * Get description
     *
     * @return	string		description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string description
     */
    public function setDescription($a_description)
    {
        $this->description = $a_description;
    }

    /**
     * Set short text dirty
     *
     * @param	boolean	short text dirty
     */
    public function setShortTextDirty($a_val)
    {
        $this->short_text_dirty = $a_val;
    }

    /**
     * Get short text dirty
     *
     * @return	boolean	short text dirty
     */
    public function getShortTextDirty()
    {
        return $this->short_text_dirty;
    }
    /**
     * Create definition
     *
     * @param boolean upload true/false
     */
    public function create($a_upload = false, $a_omit_page_creation = false)
    {
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
            $this->page_object->create();
        }
    }

    public function delete()
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


    public function moveUp()
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

    public function moveDown()
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


    public function update()
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
     *
     * @param
     * @return
     */
    public function shortenShortText($text)
    {
        $a_length = 196;

        if ($this->getTermId() > 0) {
            include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
            include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
            $glo_id = ilGlossaryTerm::_lookGlossaryId($this->getTermId());
            $snippet_length = ilObjGlossary::lookupSnippetLength($glo_id);
            if ($snippet_length > 0) {
                $a_length = $snippet_length;
            }
        }

        $text = str_replace("<br/>", "<br>", $text);
        $text = strip_tags($text, "<br>");
        if (is_int(strpos(substr($text, $a_length - 16 - 5, 10), "[tex]"))) {
            $offset = 5;
        }
        $short = ilUtil::shortenText($text, $a_length - 16 + $offset, true);
        
        // make short text longer, if tex end tag is missing
        $ltexs = strrpos($short, "[tex]");
        $ltexe = strrpos($short, "[/tex]");
        if ($ltexs > $ltexe) {
            $ltexe = strpos($text, "[/tex]", $ltexs);
            if ($ltexe > 0) {
                $text = ilUtil::shortenText($text, $ltexe + 6, true);
            }
        }
        
        $short = ilUtil::shortenText($text, $a_length, true);

        return $short;
    }

    public function updateShortText()
    {
        $this->page_object->buildDom();
        $text = $this->page_object->getFirstParagraphText();
        $short = $this->shortenShortText($text);

        $this->setShortText($short);
        $this->setShortTextDirty(false);
        $this->update();
    }

    /**
    * static
    */
    public static function getDefinitionList($a_term_id)
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
                "page_id" => $def_rec["page_id"], "id" => $def_rec["id"],
                "short_text" => strip_tags($def_rec["short_text"], "<br>"),
                "nr" => $def_rec["nr"],
                "short_text_dirty" => $def_rec["short_text_dirty"]);
        }
        return $defs;
    }

    /**
    * export xml
    */
    public function exportXML(&$a_xml_writer, $a_inst)
    {
        $attrs = array();
        $a_xml_writer->xmlStartTag("Definition", $attrs);

        $this->exportXMLMetaData($a_xml_writer);
        $this->exportXMLDefinition($a_xml_writer, $a_inst);

        $a_xml_writer->xmlEndTag("Definition");
    }


    /**
    * export content objects meta data to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLMetaData(&$a_xml_writer)
    {
        $glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
        include_once("Services/MetaData/classes/class.ilMD2XML.php");
        $md2xml = new ilMD2XML($glo_id, $this->getId(), $this->getType());
        $md2xml->setExportMode(true);
        $md2xml->startExport();
        $a_xml_writer->appendXML($md2xml->getXML());
    }

    /**
    *
    */
    public function modifyExportIdentifier($a_tag, $a_param, $a_value)
    {
        if ($a_tag == "Identifier" && $a_param == "Entry") {
            $a_value = "il_" . IL_INST_ID . "_gdf_" . $this->getId();
        }

        return $a_value;
    }


    /**
    * export page objects meta data to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLDefinition(&$a_xml_writer, $a_inst = 0)
    {
        $this->page_object->buildDom();
        $this->page_object->insertInstIntoIDs($a_inst);
        $this->mobs_contained = $this->page_object->collectMediaObjects(false);
        include_once("./Services/COPage/classes/class.ilPCFileList.php");
        $this->files_contained = ilPCFileList::collectFileItems($this->page_object, $this->page_object->getDomDoc());
        $xml = $this->page_object->getXMLFromDom(false, false, false, "", true);
        $xml = str_replace("&", "&amp;", $xml);
        $a_xml_writer->appendXML($xml);

        $this->page_object->freeDom();
    }

    /**
    * create meta data entry
    */
    public function createMetaData()
    {
        include_once 'Services/MetaData/classes/class.ilMDCreator.php';

        $ilUser = $this->user;

        $glo_id = ilGlossaryTerm::_lookGlossaryID($this->getTermId());
        $lang = ilGlossaryTerm::_lookLanguage($this->getTermId());
        $md_creator = new ilMDCreator($glo_id, $this->getId(), $this->getType());
        $md_creator->setTitle($this->getTitle());
        $md_creator->setTitleLanguage($lang);
        $md_creator->setDescription($this->getDescription());
        $md_creator->setDescriptionLanguage($lang);
        $md_creator->setKeywordLanguage($lang);
        $md_creator->setLanguage($lang);
        //echo "-".$this->getTitle()."-"; exit;
        $md_creator->create();

        return true;
    }

    /**
    * update meta data entry
    */
    public function updateMetaData()
    {
        include_once("Services/MetaData/classes/class.ilMD.php");
        include_once("Services/MetaData/classes/class.ilMDGeneral.php");
        include_once("Services/MetaData/classes/class.ilMDDescription.php");

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

    /**
    * delete meta data entry
    */
    public function deleteMetaData()
    {
        // Delete meta data
        include_once('Services/MetaData/classes/class.ilMD.php');
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
    *
    * @param	string		$a_element
    */
    public function MDUpdateListener($a_element)
    {
        include_once 'Services/MetaData/classes/class.ilMD.php';

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
    *
    * @param	int		$a_def_id		definition id
    */
    public static function _lookupTermId($a_def_id)
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
     * Set short texts dirty
     *
     * @param
     * @return
     */
    public static function setShortTextsDirty($a_glo_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
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
     * Set short texts dirty
     *
     * @param
     * @return
     */
    public static function setShortTextsDirtyGlobally()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "UPDATE glossary_definition SET " .
            " short_text_dirty = " . $ilDB->quote(1, "integer")
        );
    }
}
