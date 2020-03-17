<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/LearningModule/classes/class.ilLMObject.php");
require_once("./Modules/LearningModule/classes/class.ilLMPage.php");

define("IL_CHAPTER_TITLE", "st_title");
define("IL_PAGE_TITLE", "pg_title");
define("IL_NO_HEADER", "none");

/**
* Class ilLMPageObject
*
* Handles Page Objects of ILIAS Learning Modules
*
* Note: This class has a member variable that contains an instance
* of class ilPageObject and provides the method getPageObject() to access
* this instance. ilPageObject handles page objects and their content.
* Page objects can be assigned to different container like learning modules
* or glossaries definitions. This class, ilLMPageObject, provides additional
* methods for the handling of page objects in learning modules.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMPageObject extends ilLMObject
{
    public $is_alias;
    public $origin_id;
    public $id;
    public $dom;
    public $page_object;

    /**
    * Constructor
    * @access	public
    */
    public function __construct(&$a_content_obj, $a_id = 0, $a_halt = true)
    {
        parent::__construct($a_content_obj, $a_id);
        $this->setType("pg");
        $this->id = $a_id;

        $this->is_alias = false;
        $this->contains_int_link = false;
        $this->mobs_contained = array();
        $this->files_contained = array();
        $this->halt_on_error = $a_halt;

        if ($a_id != 0) {
            $this->read();
        }
    }

    public function __desctruct()
    {
        if (is_object($this->page_object)) {
            unset($this->page_object);
        }
    }

    /**
    *
    */
    public function read()
    {
        parent::read();

        $this->page_object = new ilLMPage($this->id, 0);
    }

    public function create($a_upload = false, $a_omit_page_object_creation = false)
    {
        parent::create($a_upload);
        if ($a_omit_page_object_creation) {
            return;
        }
        if (!is_object($this->page_object)) {
            $this->page_object = new ilLMPage();
        }
        $this->page_object->setId($this->getId());
        $this->page_object->setParentId($this->getLMId());
        $this->page_object->create($a_upload);
    }

    public function delete($a_delete_meta_data = true)
    {
        parent::delete($a_delete_meta_data);
        $this->page_object->delete();
    }


    /**
    * copy page
    */
    public function copy($a_target_lm)
    {
        // copy page
        $lm_page = new ilLMPageObject($a_target_lm);
        $lm_page->setTitle($this->getTitle());
        $lm_page->setShortTitle($this->getShortTitle());
        $lm_page->setLayout($this->getLayout());
        $lm_page->setLMId($a_target_lm->getId());
        $lm_page->setType($this->getType());
        $lm_page->setDescription($this->getDescription());
        $lm_page->setImportId("il__pg_" . $this->getId());
        $lm_page->create(true);		// setting "upload" flag to true prevents creating of meta data

        // check whether export id already exists in the target lm
        $del_exp_id = false;
        $exp_id = ilLMPageObject::getExportId($this->getLMId(), $this->getId());
        if (trim($exp_id) != "") {
            if (ilLMPageObject::existsExportID($a_target_lm->getId(), $exp_id)) {
                $del_exp_id = true;
            }
        }

        // copy meta data
        include_once("Services/MetaData/classes/class.ilMD.php");
        $md = new ilMD($this->getLMId(), $this->getId(), $this->getType());
        $new_md = $md->cloneMD($a_target_lm->getId(), $lm_page->getId(), $this->getType());

        // check whether export id already exists in the target lm
        if ($del_exp_id) {
            ilLMPageObject::saveExportId($a_target_lm->getId(), $lm_page->getId(), "");
        } else {
            ilLMPageObject::saveExportId(
                $a_target_lm->getId(),
                $lm_page->getId(),
                trim($exp_id)
            );
        }

        // copy page content and activation
        $page = $lm_page->getPageObject();
        $this->page_object->copy($page->getId(), $page->getParentType(), $page->getParentId());
        /*$page->setXMLContent($this->page_object->copyXMLContent());
        $page->setActive($this->page_object->getActive());
        $page->setActivationStart($this->page_object->getActivationStart());
        $page->setActivationEnd($this->page_object->getActivationEnd());
        $page->buildDom();
        $page->update();*/
        $lm_page->read();	// this gets the updated page object into lm page

        // copy translations
        include_once("./Modules/LearningModule/classes/class.ilLMObjTranslation.php");
        ilLMObjTranslation::copy($this->getId(), $lm_page->getId());

        return $lm_page;
    }

    /**
    * copy a page to another content object (learning module / dlib book)
    */
    public function &copyToOtherContObject(&$a_cont_obj, &$a_copied_nodes)
    {
        // copy page
        $lm_page = new ilLMPageObject($a_cont_obj);
        $lm_page->setTitle($this->getTitle());
        $lm_page->setShortTitle($this->getShortTitle());
        $lm_page->setLMId($a_cont_obj->getId());
        $lm_page->setImportId("il__pg_" . $this->getId());
        $lm_page->setType($this->getType());
        $lm_page->setDescription($this->getDescription());
        $lm_page->create(true);		// setting "upload" flag to true prevents creating of meta data
        $a_copied_nodes[$this->getId()] = $lm_page->getId();

        // copy meta data
        include_once("Services/MetaData/classes/class.ilMD.php");
        $md = new ilMD($this->getLMId(), $this->getId(), $this->getType());
        $new_md = $md->cloneMD($a_cont_obj->getId(), $lm_page->getId(), $this->getType());

        // copy page content
        $page = $lm_page->getPageObject();
        $page->setXMLContent($this->page_object->getXMLContent());
        $page->buildDom();
        $page->update();

        return $lm_page;
    }
    
    /**
    * split page at hierarchical id
    *
    * the main reason for this method being static is that a lm page
    * object is not available within ilPageContentGUI where this method
    * is called
    */
    public static function _splitPage($a_page_id, $a_pg_parent_type, $a_hier_id)
    {
        // get content object (learning module / digilib book)
        $lm_id = ilLMObject::_lookupContObjID($a_page_id);
        $type = ilObject::_lookupType($lm_id, false);
        include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
        $cont_obj = new ilObjLearningModule($lm_id, false);
        $source_lm_page = new ilLMPageObject($cont_obj, $a_page_id);

        // create new page
        $lm_page = new ilLMPageObject($cont_obj);
        $lm_page->setTitle($source_lm_page->getTitle());
        $lm_page->setLMId($source_lm_page->getLMId());
        $lm_page->setType($source_lm_page->getType());
        $lm_page->setDescription($source_lm_page->getDescription());
        $lm_page->create(true);
        

        // copy complete content of source page to new page
        $source_page = $source_lm_page->getPageObject();
        $page = $lm_page->getPageObject();
        $page->setXMLContent($source_page->copyXMLContent());
        //echo htmlentities($source_page->copyXMLContent());
        $page->buildDom(true);
        $page->update();
        //		echo "-".$page->getId()."-".$page->getParentType()."-";

        // copy meta data
        include_once("Services/MetaData/classes/class.ilMD.php");
        $md = new ilMD($source_lm_page->getLMId(), $a_page_id, $source_lm_page->getType());
        $md->cloneMD($source_lm_page->getLMId(), $lm_page->getId(), $source_lm_page->getType());

        // insert new page in tree (after original page)
        $tree = new ilTree($cont_obj->getId());
        $tree->setTableNames('lm_tree', 'lm_data');
        $tree->setTreeTablePK("lm_id");
        if ($tree->isInTree($source_lm_page->getId())) {
            $parent_node = $tree->getParentNodeData($source_lm_page->getId());
            $tree->insertNode($lm_page->getId(), $parent_node["child"], $source_lm_page->getId());
        }

        // remove all nodes < hierarchical id from new page (incl. update)
        $page->addHierIds();
        $page->deleteContentBeforeHierId($a_hier_id);
        //		$page->update();

        // remove all nodes >= hierarchical id from source page
        $source_page->buildDom();
        $source_page->addHierIds();
        $source_page->deleteContentFromHierId($a_hier_id);
                
        return $lm_page;
    }

    /**
    * split page to next page at hierarchical id
    *
    * the main reason for this method being static is that a lm page
    * object is not available within ilPageContentGUI where this method
    * is called
    */
    public static function _splitPageNext($a_page_id, $a_pg_parent_type, $a_hier_id)
    {
        // get content object (learning module / digilib book)
        $lm_id = ilLMObject::_lookupContObjID($a_page_id);
        $type = ilObject::_lookupType($lm_id, false);
        include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
        $cont_obj = new ilObjLearningModule($lm_id, false);
        $tree = new ilTree($cont_obj->getId());
        $tree->setTableNames('lm_tree', 'lm_data');
        $tree->setTreeTablePK("lm_id");

        $source_lm_page = new ilLMPageObject($cont_obj, $a_page_id);
        $source_page = $source_lm_page->getPageObject();
        
        // get next page
        $succ = $tree->fetchSuccessorNode($a_page_id, "pg");
        if ($succ["child"] > 0) {
            $target_lm_page = new ilLMPageObject($cont_obj, $succ["child"]);
            $target_page = $target_lm_page->getPageObject();
            $target_page->buildDom();
            $target_page->addHierIds();
            
            // move nodes to target page
            $source_page->buildDom();
            $source_page->addHierIds();
            ilLMPage::_moveContentAfterHierId($source_page, $target_page, $a_hier_id);
            //$source_page->deleteContentFromHierId($a_hier_id);
            
            return $succ["child"];
        }
    }

    
    /**
    * assign page object
    *
    * @param	object		$a_page_obj		page object
    */
    public function assignPageObject(&$a_page_obj)
    {
        $this->page_object = $a_page_obj;
    }

    
    /**
    * get assigned page object
    *
    * @return	object		page object
    */
    public function &getPageObject()
    {
        return $this->page_object;
    }

    
    /**
    * set id
    */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
    * set wether page object is an alias
    */
    public function setAlias($a_is_alias)
    {
        $this->is_alias = $a_is_alias;
    }

    public function isAlias()
    {
        return $this->is_alias;
    }

    // only for page aliases
    public function setOriginID($a_id)
    {
        return $this->origin_id = $a_id;
    }

    // only for page aliases
    public function getOriginID()
    {
        return $this->origin_id;
    }

    /**
    * static
    */
    public static function getPageList($lm_id)
    {
        return ilLMObject::getObjectList($lm_id, "pg");
    }

    /**
    * Get all pages of lm that contain any internal links
    */
    public static function getPagesWithLinksList($a_lm_id, $a_par_type)
    {
        $pages = ilLMPageObject::getPageList($a_lm_id);
        $ids = array();
        foreach ($pages as $page) {
            $ids[] = $page["obj_id"];
        }

        $linked_pages = ilLMPage::getPagesWithLinks($a_par_type, $a_lm_id);
        $result = array();
        foreach ($pages as $page) {
            if (is_array($linked_pages[$page["obj_id"]])) {
                $result[] = $page;
            }
        }
        return $result;
    }

    /**
    * presentation title doesn't have to be page title, it may be
    * chapter title + page title or chapter title only, depending on settings
    *
    * @param	string	$a_mode		IL_CHAPTER_TITLE | IL_PAGE_TITLE | IL_NO_HEADER
    */
    public static function _getPresentationTitle(
        $a_pg_id,
        $a_mode = IL_CHAPTER_TITLE,
        $a_include_numbers = false,
        $a_time_scheduled_activation = false,
        $a_force_content = false,
        $a_lm_id = 0,
        $a_lang = "-",
        $a_include_short = false
    ) {
        if ($a_mode == IL_NO_HEADER && !$a_force_content) {
            return "";
        }

        if ($a_lm_id == 0) {
            $a_lm_id = ilLMObject::_lookupContObjID($a_pg_id);
        }

        if ($a_lm_id == 0) {
            return "";
        }

        // this is optimized when ilLMObject::preloadDataByLM is invoked (e.g. done in ilLMExplorerGUI)
        $title = "";
        if ($a_include_short) {
            $title = trim(ilLMObject::_lookupShortTitle($a_pg_id));
        }
        if ($title == "") {
            $title = ilLMObject::_lookupTitle($a_pg_id);
        }

        // this is also optimized since ilObjectTranslation re-uses instances for one lm
        include_once("./Services/Object/classes/class.ilObjectTranslation.php");
        $ot = ilObjectTranslation::getInstance($a_lm_id);
        $languages = $ot->getLanguages();

        if ($a_lang != "-" && $ot->getContentActivated() && isset($languages[$a_lang])) {
            include_once("./Modules/LearningModule/classes/class.ilLMObjTranslation.php");
            $lmobjtrans = new ilLMObjTranslation($a_pg_id, $a_lang);
            $trans_title = "";
            if ($a_include_short) {
                $trans_title = trim($lmobjtrans->getShortTitle());
            }
            if ($trans_title == "") {
                $trans_title = $lmobjtrans->getTitle();
            }
            if ($trans_title != "") {
                $title = $trans_title;
            }
        }

        if ($a_mode == IL_PAGE_TITLE) {
            return $title;
        }

        include_once("./Modules/LearningModule/classes/class.ilLMTree.php");
        $tree = ilLMTree::getInstance($a_lm_id);

        if ($tree->isInTree($a_pg_id)) {
            $pred_node = $tree->fetchPredecessorNode($a_pg_id, "st");
            $childs = $tree->getChildsByType($pred_node["obj_id"], "pg");
            $cnt_str = "";
            if (count($childs) > 1) {
                $cnt = 0;
                foreach ($childs as $child) {
                    include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
                    $active = ilLMPage::_lookupActive(
                        $child["obj_id"],
                        ilObject::_lookupType($a_lm_id),
                        $a_time_scheduled_activation
                    );

                    if (!$active) {
                        $act_data = ilLMPage::_lookupActivationData((int) $child["obj_id"], ilObject::_lookupType($a_lm_id));
                        if ($act_data["show_activation_info"] &&
                            (ilUtil::now() < $act_data["activation_start"])) {
                            $active = true;
                        }
                    }
                    
                    if ($child["type"] != "pg" || $active) {
                        $cnt++;
                    }
                    if ($child["obj_id"] == $a_pg_id) {
                        $cur_cnt = $cnt;
                    }
                }
                if ($cnt > 1) {
                    $cnt_str = " (" . $cur_cnt . "/" . $cnt . ")";
                }
            }
            require_once("./Modules/LearningModule/classes/class.ilStructureObject.php");
            //$struct_obj = new ilStructureObject($pred_node["obj_id"]);
            //return $struct_obj->getTitle();
            return ilStructureObject::_getPresentationTitle(
                $pred_node["obj_id"],
                IL_CHAPTER_TITLE,
                $a_include_numbers,
                false,
                false,
                0,
                $a_lang,
                true
            ) . $cnt_str;

        //return $pred_node["title"].$cnt_str;
        } else {
            return $title;
        }
    }

    /**
    * export page object to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXML(&$a_xml_writer, $a_mode = "normal", $a_inst = 0)
    {
        $attrs = array();
        $a_xml_writer->xmlStartTag("PageObject", $attrs);

        switch ($a_mode) {
            case "normal":
                // MetaData
                $this->exportXMLMetaData($a_xml_writer);

                // PageContent
                $this->exportXMLPageContent($a_xml_writer, $a_inst);
                break;

            case "alias":
                $attrs = array();
                $attrs["OriginId"] = "il_" . $a_inst .
                    "_pg_" . $this->getId();
                $a_xml_writer->xmlElement("PageAlias", $attrs);
                break;
        }

        // Layout
        // not implemented

        $a_xml_writer->xmlEndTag("PageObject");
    }

    /**
    * export page alias to xml
    */
    public static function _exportXMLAlias(&$a_xml_writer, $a_id, $a_inst = 0)
    {
        $attrs = array();
        $a_xml_writer->xmlStartTag("PageObject", $attrs);

        $attrs = array();
        $attrs["OriginId"] = "il_" . $a_inst .
            "_pg_" . $a_id;
        $a_xml_writer->xmlElement("PageAlias", $attrs);

        $a_xml_writer->xmlEndTag("PageObject");
    }


    /**
    * export page objects meta data to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLMetaData(&$a_xml_writer)
    {
        include_once("Services/MetaData/classes/class.ilMD2XML.php");
        $md2xml = new ilMD2XML($this->getLMId(), $this->getId(), $this->getType());
        $md2xml->setExportMode(true);
        $md2xml->startExport();
        $a_xml_writer->appendXML($md2xml->getXML());
    }

    public function modifyExportIdentifier($a_tag, $a_param, $a_value)
    {
        if ($a_tag == "Identifier" && $a_param == "Entry") {
            $a_value = "il_" . IL_INST_ID . "_pg_" . $this->getId();
            //$a_value = ilUtil::insertInstIntoID($a_value);
        }

        return $a_value;
    }


    /**
     * export page objects meta data to xml (see ilias_co.dtd)
     *
     * @param	object		$a_xml_writer	ilXmlWriter object that receives the
     *										xml data
     */
    public function exportXMLPageContent(&$a_xml_writer, $a_inst = 0)
    {
        //echo "exportxmlpagecontent:$a_inst:<br>";
        $cont_obj = $this->getContentObject();

        $this->page_object->buildDom();
        $this->page_object->insertInstIntoIDs($a_inst);
        $this->mobs_contained = $this->page_object->collectMediaObjects(false);
        //$this->files_contained = $this->page_object->collectFileItems();
        include_once("./Services/COPage/classes/class.ilPCFileList.php");
        $this->files_contained = ilPCFileList::collectFileItems($this->page_object, $this->page_object->getDomDoc());
        //		$this->questions_contained = $this->page_object->getQuestionIds();
        $xml = $this->page_object->getXMLFromDom(false, false, false, "", true);
        $xml = str_replace("&", "&amp;", $xml);
        $a_xml_writer->appendXML($xml);

        $this->page_object->freeDom();
    }

    /**
     * Get question ids
     *
     * note: this method must be called afer exportXMLPageContent
     */
    public function getQuestionIds()
    {
        include_once("./Services/COPage/classes/class.ilPCQuestion.php");
        return ilPCQuestion::_getQuestionIdsForPage(
            $this->content_object->getType(),
            $this->getId()
        );
    }

    /**
     * get ids of all media objects within the page
     *
     * note: this method must be called afer exportXMLPageContent
     */
    public function getMediaObjectIds()
    {
        return $this->mobs_contained;
    }

    /**
    * get ids of all file items within the page
    *
    * note: this method must be called afer exportXMLPageContent
    */
    public function getFileItemIds()
    {
        return $this->files_contained;
    }

    /**
    * export page object to fo
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportFO(&$a_xml_writer)
    {
        $title = ilLMPageObject::_getPresentationTitle($this->getId());
        if ($title != "") {
            $attrs = array();
            $attrs["font-family"] = "Times";
            $attrs["font-size"] = "14pt";
            $a_xml_writer->xmlElement("fo:block", $attrs, $title);
        }

        // PageContent
        $this->page_object->buildDom();
        $fo = $this->page_object->getFO();
        $a_xml_writer->appendXML($fo);
    }

    /**
     * Get questions of learning module
     *
     * @param
     * @return
     */
    public static function queryQuestionsOfLearningModule(
        $a_lm_id,
        $a_order_field,
        $a_order_dir,
        $a_offset,
        $a_limit
    ) {
        global $DIC;

        $ilDB = $DIC->database();
        $rbacreview = $DIC->rbac()->review();


        // count query
        $count_query = "SELECT count(pq.question_id) cnt ";

        // basic query
        $query = "SELECT pq.page_id, pq.question_id ";

        $from = " FROM page_question pq JOIN lm_tree t ON (t.lm_id = " . $ilDB->quote($a_lm_id, "integer") .
            " AND pq.page_id = t.child and pq.page_parent_type = " . $ilDB->quote("lm", "text") . ") " .
            " WHERE t.lm_id = " . $ilDB->quote($a_lm_id, "integer");
        $count_query .= $from;
        $query .= $from;


        // count query
        $set = $ilDB->query($count_query);
        $cnt = 0;
        if ($rec = $ilDB->fetchAssoc($set)) {
            $cnt = $rec["cnt"];
        }

        $offset = (int) $a_offset;
        $limit = (int) $a_limit;
        if ($a_limit > 0) {
            $ilDB->setLimit($limit, $offset);
        }

        // set query
        $set = $ilDB->query($query);
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec;
        }
        return array("cnt" => $cnt, "set" => $result);
    }
}
