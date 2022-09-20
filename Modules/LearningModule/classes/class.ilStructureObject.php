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
 * Handles StructureObjects of ILIAS Learning Modules (see ILIAS DTD)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilStructureObject extends ilLMObject
{
    public string $origin_id;
    public ilLMTree $tree;

    public function __construct(
        ilObjLearningModule $a_content_obj,
        int $a_id = 0
    ) {
        $this->setType("st");
        parent::__construct($a_content_obj, $a_id);
        $this->tree = new ilLMTree($this->getLMId());
    }

    public function delete(bool $a_delete_meta_data = true): void
    {
        // only relevant for online help authoring
        ilHelpMapping::removeScreenIdsOfChapter($this->getId());

        $node_data = $this->tree->getNodeData($this->getId());
        $this->delete_rec($this->tree, $a_delete_meta_data);
        $this->tree->deleteTree($node_data);
    }

    /**
     * Delete sub tree
     */
    private function delete_rec(
        ilLMTree $a_tree,
        bool $a_delete_meta_data = true
    ): void {
        $childs = $a_tree->getChilds($this->getId());
        foreach ($childs as $child) {
            $obj = ilLMObjectFactory::getInstance($this->content_object, $child["obj_id"], false);
            if (is_object($obj)) {
                if ($obj->getType() == "st") {
                    $obj->delete_rec($a_tree, $a_delete_meta_data);
                }
                if ($obj->getType() == "pg") {
                    $obj->delete($a_delete_meta_data);
                }
            }
            unset($obj);
        }
        parent::delete($a_delete_meta_data);
    }

    /**
     * copy chapter
     */
    public function copy(
        ilObjLearningModule $a_target_lm
    ): ilStructureObject {
        $chap = new ilStructureObject($a_target_lm);
        $chap->setTitle($this->getTitle());
        if ($this->getLMId() != $a_target_lm->getId()) {
            $chap->setImportId("il__st_" . $this->getId());
        }
        $chap->setLMId($a_target_lm->getId());
        $chap->setType($this->getType());
        $chap->setDescription($this->getDescription());
        $chap->create(true);
        $a_copied_nodes[$this->getId()] = $chap->getId();

        // copy meta data
        $md = new ilMD($this->getLMId(), $this->getId(), $this->getType());
        $new_md = $md->cloneMD($a_target_lm->getId(), $chap->getId(), $this->getType());

        // copy translations
        ilLMObjTranslation::copy($this->getId(), $chap->getId());


        return $chap;
    }

    public function exportXML(
        ilXmlWriter $a_xml_writer,
        int $a_inst,
        ilLog $expLog
    ): void {
        $expLog->write(date("[y-m-d H:i:s] ") . "Structure Object " . $this->getId());
        $attrs = array();
        $a_xml_writer->xmlStartTag("StructureObject", $attrs);

        // MetaData
        $this->exportXMLMetaData($a_xml_writer);

        // StructureObjects
        $this->exportXMLPageObjects($a_xml_writer, $a_inst);

        // PageObjects
        $this->exportXMLStructureObjects($a_xml_writer, $a_inst, $expLog);

        $a_xml_writer->xmlEndTag("StructureObject");
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
            $a_value = "il_" . IL_INST_ID . "_st_" . $this->getId();
        }

        return $a_value;
    }

    public static function _getPresentationTitle(
        int $a_st_id,
        string $a_mode = self::CHAPTER_TITLE,
        bool $a_include_numbers = false,
        bool $a_time_scheduled_activation = false,
        bool $a_force_content = false,
        int $a_lm_id = 0,
        string $a_lang = "-",
        bool $a_include_short = false
    ): string {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_lm_id == 0) {
            $a_lm_id = ilLMObject::_lookupContObjID($a_st_id);
        }

        if ($a_lm_id == 0) {
            return "";
        }

        // this is optimized when ilLMObject::preloadDataByLM is invoked (e.g. done in ilLMExplorerGUI)
        $title = "";
        if ($a_include_short) {
            $title = trim(ilLMObject::_lookupShortTitle($a_st_id));
        }
        if ($title == "") {
            $title = ilLMObject::_lookupTitle($a_st_id);
        }

        // this is also optimized since ilObjectTranslation re-uses instances for one lm
        $ot = ilObjectTranslation::getInstance($a_lm_id);
        $languages = $ot->getLanguages();

        if ($a_lang != "-" && $ot->getContentActivated()) {
            $lmobjtrans = new ilLMObjTranslation($a_st_id, $a_lang);
            $trans_title = "";
            if ($a_include_short) {
                $trans_title = trim($lmobjtrans->getShortTitle());
            }
            if ($trans_title == "") {
                $trans_title = $lmobjtrans->getTitle();
            }
            if ($trans_title == "") {
                $lmobjtrans = new ilLMObjTranslation($a_st_id, $ot->getFallbackLanguage());
                $trans_title = $lmobjtrans->getTitle();
            }
            if ($trans_title != "") {
                $title = $trans_title;
            }
        }

        $tree = ilLMTree::getInstance($a_lm_id);

        $nr = "";
        if ($a_include_numbers) {
            // this is optimized, since isInTree is cached
            if ($tree->isInTree($a_st_id)) {
                // optimization needed from here

                // get chapter tree node
                $query = "SELECT * FROM lm_tree WHERE child = " .
                    $ilDB->quote($a_st_id, "integer") . " AND lm_id = " .
                    $ilDB->quote($a_lm_id, "integer");
                $tree_set = $ilDB->query($query);
                $tree_node = $tree_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
                $depth = $tree_node["depth"];

                $nr = $tree->getChildSequenceNumber($tree_node, "st") . " ";
                for ($i = $depth - 1; $i > 1; $i--) {
                    // get next parent tree node
                    $query = "SELECT * FROM lm_tree WHERE child = " .
                        $ilDB->quote($tree_node["parent"], "integer") . " AND lm_id = " .
                        $ilDB->quote($a_lm_id, "integer");
                    $tree_set = $ilDB->query($query);
                    $tree_node = $tree_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
                    $seq = $tree->getChildSequenceNumber($tree_node, "st");

                    $nr = $seq . "." . $nr;
                }
            }
        }

        return $nr . $title;
    }

    public function exportXMLPageObjects(
        ilXmlWriter $a_xml_writer,
        int $a_inst = 0
    ): void {
        $childs = $this->tree->getChilds($this->getId());
        foreach ($childs as $child) {
            if ($child["type"] != "pg") {
                continue;
            }

            // export xml to writer object
            ilLMPageObject::_exportXMLAlias($a_xml_writer, $child["obj_id"], $a_inst);
        }
    }

    public function exportXMLStructureObjects(
        ilXmlWriter $a_xml_writer,
        int $a_inst,
        ilLog $expLog
    ): void {
        $childs = $this->tree->getChilds($this->getId());
        foreach ($childs as $child) {
            if ($child["type"] != "st") {
                continue;
            }

            // export xml to writer object
            $structure_obj = new ilStructureObject(
                $this->getContentObject(),
                $child["obj_id"]
            );
            $structure_obj->exportXML($a_xml_writer, $a_inst, $expLog);
            unset($structure_obj);
        }
    }

    public function exportFO(
        ilXmlWriter $a_xml_writer
    ): void {
        // fo:block (complete)
        $attrs = array();
        $attrs["font-family"] = "Times";
        $attrs["font-size"] = "14pt";
        $a_xml_writer->xmlElement("fo:block", $attrs, $this->getTitle());

        // page objects
        $this->exportFOPageObjects($a_xml_writer);
    }

    public function exportFOPageObjects(
        ilXmlWriter $a_xml_writer
    ): void {
        $childs = $this->tree->getChilds($this->getId());
        foreach ($childs as $child) {
            if ($child["type"] != "pg") {
                continue;
            }

            // export xml to writer object
            $page_obj = new ilLMPageObject($this->getContentObject(), $child["obj_id"]);
            $page_obj->exportFO($a_xml_writer);
        }
    }

    public static function getChapterList(
        int $a_lm_id
    ): array {
        $tree = new ilLMTree($a_lm_id);

        $chapters = array();
        $ndata = $tree->getNodeData($tree->readRootId());
        $childs = $tree->getSubTree($ndata);
        foreach ($childs as $child) {
            if ($child["type"] == "st") {
                $chapters[] = $child;
            }
        }
        return $chapters;
    }
}
