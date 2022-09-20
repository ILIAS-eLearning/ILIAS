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
 * Handles Page Objects of ILIAS Learning Modules
 *
 * Note: This class has a member variable that contains an instance
 * of class ilPageObject and provides the method getPageObject() to access
 * this instance. ilPageObject handles page objects and their content.
 * Page objects can be assigned to different container like learning modules
 * or glossaries definitions. This class, ilLMPageObject, provides additional
 * methods for the handling of page objects in learning modules.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMPageObject extends ilLMObject
{
    protected bool $halt_on_error;
    protected array $files_contained;
    protected array $mobs_contained;
    protected bool $contains_int_link;
    public ?ilLMPage $page_object = null;

    public function __construct(
        ilObjLearningModule $a_content_obj,
        int $a_id = 0,
        bool $a_halt = true
    ) {
        parent::__construct($a_content_obj, $a_id);
        $this->setType("pg");
        $this->id = $a_id;

        $this->contains_int_link = false;
        $this->mobs_contained = array();
        $this->files_contained = array();
        $this->halt_on_error = $a_halt;

        if ($a_id != 0) {
            $this->read();
        }
    }

    public function read(): void
    {
        parent::read();
        $this->page_object = new ilLMPage($this->id, 0);
    }

    public function create(
        bool $a_upload = false,
        bool $a_omit_page_object_creation = false,
        int $a_layout_id = 0
    ): void {
        parent::create($a_upload);
        if ($a_omit_page_object_creation) {
            return;
        }
        if (!is_object($this->page_object)) {
            $this->page_object = new ilLMPage();
        }
        $this->page_object->setId($this->getId());
        $this->page_object->setParentId($this->getLMId());
        if ($a_layout_id == 0) {
            $this->page_object->create(false);
        } else {
            $this->page_object->createWithLayoutId($a_layout_id);
        }
    }

    public function delete(bool $a_delete_meta_data = true): void
    {
        parent::delete($a_delete_meta_data);
        $this->page_object->delete();
    }

    // copy page
    public function copy(
        ilObjLearningModule $a_target_lm
    ): ilLMPageObject {
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
        $lm_page->read();	// this gets the updated page object into lm page

        // copy translations
        ilLMObjTranslation::copy($this->getId(), $lm_page->getId());

        return $lm_page;
    }

    /**
     * copy a page to another content object (learning module / dlib book)
     */
    public function copyToOtherContObject(
        ilObjLearningModule $a_cont_obj,
        array &$a_copied_nodes
    ): ilLMPageObject {
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
     * the main reason for this method being static is that a lm page
     * object is not available within ilPageContentGUI where this method
     * is called
     */
    public static function _splitPage(
        int $a_page_id,
        string $a_pg_parent_type,
        string $a_hier_id
    ): ilLMPageObject {
        // get content object (learning module / digilib book)
        $lm_id = ilLMObject::_lookupContObjID($a_page_id);
        $type = ilObject::_lookupType($lm_id, false);
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
        $page->setXMLContent($source_page->copyXmlContent());
        $page->buildDom(true);
        $page->update();

        // copy meta data
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
        $page->addHierIDs();
        $page->deleteContentBeforeHierId($a_hier_id);
        //		$page->update();

        // remove all nodes >= hierarchical id from source page
        $source_page->buildDom();
        $source_page->addHierIDs();
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
    public static function _splitPageNext(
        int $a_page_id,
        string $a_pg_parent_type,
        string $a_hier_id
    ): int {
        // get content object (learning module / digilib book)
        $lm_id = ilLMObject::_lookupContObjID($a_page_id);
        $type = ilObject::_lookupType($lm_id, false);
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
            $target_page->addHierIDs();

            // move nodes to target page
            $source_page->buildDom();
            $source_page->addHierIDs();
            ilLMPage::_moveContentAfterHierId($source_page, $target_page, $a_hier_id);
            //$source_page->deleteContentFromHierId($a_hier_id);

            return (int) $succ["child"];
        }
        return 0;
    }


    /**
     * assign page object
     */
    public function assignPageObject(ilLMPage $a_page_obj): void
    {
        $this->page_object = $a_page_obj;
    }


    /**
     * get assigned page object
     */
    public function getPageObject(): ilLMPage
    {
        return $this->page_object;
    }

    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public static function getPageList(int $lm_id): array
    {
        return ilLMObject::getObjectList($lm_id, "pg");
    }

    /**
     * Get all pages of lm that contain any internal links
     */
    public static function getPagesWithLinksList(
        int $a_lm_id,
        string $a_par_type
    ): array {
        $pages = ilLMPageObject::getPageList($a_lm_id);
        $linked_pages = ilLMPage::getPagesWithLinks($a_par_type, $a_lm_id);
        $result = array();
        foreach ($pages as $page) {
            if (isset($linked_pages[$page["obj_id"]])) {
                $result[] = $page;
            }
        }
        return $result;
    }

    /**
     * presentation title doesn't have to be page title, it may be
     * chapter title + page title or chapter title only, depending on settings
     * @param	string	$a_mode		ilLMOBject::CHAPTER_TITLE | ilLMOBject::PAGE_TITLE | ilLMOBject::NO_HEADER
     */
    public static function _getPresentationTitle(
        int $a_pg_id,
        string $a_mode = self::CHAPTER_TITLE,
        bool $a_include_numbers = false,
        bool $a_time_scheduled_activation = false,
        bool $a_force_content = false,
        int $a_lm_id = 0,
        string $a_lang = "-",
        bool $a_include_short = false
    ): string {
        if ($a_mode == self::NO_HEADER && !$a_force_content) {
            return "";
        }

        $cur_cnt = 0;

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
        $ot = ilObjectTranslation::getInstance($a_lm_id);
        $languages = $ot->getLanguages();

        if ($a_lang != "-" && $ot->getContentActivated()) {
            $lmobjtrans = new ilLMObjTranslation($a_pg_id, $a_lang);
            $trans_title = "";
            if ($a_include_short) {
                $trans_title = trim($lmobjtrans->getShortTitle());
            }
            if ($trans_title == "") {
                $trans_title = $lmobjtrans->getTitle();
            }
            if ($trans_title == "") {
                $lmobjtrans = new ilLMObjTranslation($a_pg_id, $ot->getFallbackLanguage());
                $trans_title = $lmobjtrans->getTitle();
            }
            if ($trans_title != "") {
                $title = $trans_title;
            }
        }

        if ($a_mode == self::PAGE_TITLE) {
            return $title;
        }

        $tree = ilLMTree::getInstance($a_lm_id);

        if ($tree->isInTree($a_pg_id)) {
            $pred_node = $tree->fetchPredecessorNode($a_pg_id, "st");
            $childs = $tree->getChildsByType($pred_node["obj_id"], "pg");
            $cnt_str = "";
            if (count($childs) > 1) {
                $cnt = 0;
                foreach ($childs as $child) {
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
            return ilStructureObject::_getPresentationTitle(
                $pred_node["obj_id"],
                self::CHAPTER_TITLE,
                $a_include_numbers,
                false,
                false,
                0,
                $a_lang,
                true
            ) . $cnt_str;
        } else {
            return $title;
        }
    }

    /**
     * export page object to xml (see ilias_co.dtd)
     */
    public function exportXML(
        ilXmlWriter $a_xml_writer,
        string $a_mode = "normal",
        int $a_inst = 0
    ): void {
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
        $a_xml_writer->xmlEndTag("PageObject");
    }

    /**
     * export page alias to xml
     */
    public static function _exportXMLAlias(
        ilXmlWriter $a_xml_writer,
        int $a_id,
        int $a_inst = 0
    ): void {
        $attrs = array();
        $a_xml_writer->xmlStartTag("PageObject", $attrs);

        $attrs = array();
        $attrs["OriginId"] = "il_" . $a_inst .
            "_pg_" . $a_id;
        $a_xml_writer->xmlElement("PageAlias", $attrs);

        $a_xml_writer->xmlEndTag("PageObject");
    }

    public function exportXMLMetaData(
        ilXmlWriter $a_xml_writer
    ): void {
        $md2xml = new ilMD2XML($this->getLMId(), $this->getId(), $this->getType());
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
            $a_value = "il_" . IL_INST_ID . "_pg_" . $this->getId();
        }

        return $a_value;
    }

    public function exportXMLPageContent(
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

    /**
     * Get question ids
     * note: this method must be called afer exportXMLPageContent
     */
    public function getQuestionIds(): array
    {
        return ilPCQuestion::_getQuestionIdsForPage(
            $this->content_object->getType(),
            $this->getId()
        );
    }

    /**
     * get ids of all media objects within the page
     * note: this method must be called afer exportXMLPageContent
     */
    public function getMediaObjectIds(): array
    {
        return $this->mobs_contained;
    }

    /**
     * get ids of all file items within the page
     * note: this method must be called afer exportXMLPageContent
     */
    public function getFileItemIds(): array
    {
        return $this->files_contained;
    }

    /**
     * export page object to fo
     */
    public function exportFO(
        ilXmlWriter $a_xml_writer
    ): void {
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
     */
    public static function queryQuestionsOfLearningModule(
        int $a_lm_id,
        string $a_order_field,
        string $a_order_dir,
        int $a_offset,
        int $a_limit
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

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

        $offset = $a_offset;
        $limit = $a_limit;
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

    /**
     * Insert (multiple) pages templates at node
     */
    public static function insertPagesFromTemplate(
        int $lm_id,
        int $num,
        int $node_id,
        bool $first_child,
        int $layout_id,
        string $title = ""
    ): array {
        global $DIC;

        $lng = $DIC->language();

        if ($title == "") {
            $title = $lng->txt("cont_new_page");
        }
        $lm_tree = new ilLMTree($lm_id);
        $lm = new ilObjLearningModule($lm_id, false);
        if (!$first_child) {	// insert after node id
            $parent_id = $lm_tree->getParentId($node_id);
            $target = $node_id;
        } else {           // insert as first child
            $parent_id = $node_id;
            $target = ilTree::POS_FIRST_NODE;
        }

        $page_ids = array();
        for ($i = 1; $i <= $num; $i++) {
            $page = new ilLMPageObject($lm);
            $page->setTitle($title);
            $page->setLMId($lm->getId());
            $page->create(false, false, $layout_id);
            ilLMObject::putInTree($page, $parent_id, $target);
            $page_ids[] = $page->getId();
        }
        $page_ids = array_reverse($page_ids);
        return $page_ids;
    }
}
