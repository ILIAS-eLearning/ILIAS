<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for files
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesLearningModule
 */
class ilLearningModuleImporter extends ilXmlImporter
{
    protected $config;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * Initialisation
     */
    public function init()
    {
        include_once("./Modules/LearningModule/classes/class.ilLearningModuleDataSet.php");
        $this->ds = new ilLearningModuleDataSet();
        $this->ds->setDSPrefix("ds");

        $this->log = ilLoggerFactory::getLogger('lm');

        $this->config = $this->getImport()->getConfig("Modules/LearningModule");
        if ($this->config->getTranslationImportMode()) {
            $this->ds->setTranslationImportMode(
                $this->config->getTranslationLM(),
                $this->config->getTranslationLang()
            );
            $cop_config = $this->getImport()->getConfig("Services/COPage");
            $cop_config->setUpdateIfExists(true);
            $cop_config->setForceLanguage($this->config->getTranslationLang());
            $cop_config->setReuseOriginallyExportedMedia(true);
            $cop_config->setSkipInternalLinkResolve(true);

            $mob_config = $this->getImport()->getConfig("Services/MediaObjects");
            $mob_config->setUsePreviousImportIds(true);
        }
    }


    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        include_once './Modules/File/classes/class.ilObjFile.php';

        $this->log->debug("import XML Representation");

        // case i container
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
            $newObj->createLMTree();
            $this->log->debug("got mapping, new id is: " . $new_id);
        }

        // in the new version (5.1)  we are also here, but the following file should not exist
        // if being exported with 5.1 or higher
        $xml_file = $this->getImportDirectory() . '/' . basename($this->getImportDirectory()) . '.xml';

        // old school import
        // currently this means we got a container and mapping, too, since
        // for single lms the processing in ilObjContentObjectGUI->importFileObject is used
        // (this should be streamlined, see glossary)
        if (file_exists($xml_file)) {
            $newObj->setImportDirectory(dirname(rtrim($this->getImportDirectory(), '/')));
            $mess = $newObj->importFromDirectory($this->getImportDirectory(), true, $a_mapping);
            $this->log->debug("imported from directory ($mess)");
            $a_mapping->addMapping("Modules/LearningModule", "lm", $a_id, $newObj->getId());
            $a_mapping->addMapping("Services/Object", "obj", $a_id, $newObj->getId());
        } else {	// new import version (does mapping, too)
            $this->log->debug("create ilDataSetIportParser instance");
            include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
            $parser = new ilDataSetImportParser(
                $a_entity,
                $this->getSchemaVersion(),
                $a_xml,
                $this->ds,
                $a_mapping
            );
        }

        // import qti stuff
        $this->log->debug("import qti data");
        $qti_file = $this->getImportDirectory() . '/qti.xml';
        $this->qtis = array();
        if (is_file($qti_file)) {
            include_once "./Services/QTI/classes/class.ilQTIParser.php";
            include_once("./Modules/Test/classes/class.ilObjTest.php");
            $qtiParser = new ilQTIParser(
                $qti_file,
                IL_MO_VERIFY_QTI,
                0,
                ""
            );
            $result = $qtiParser->startParsing();
            $founditems = &$qtiParser->getFoundItems();
            $testObj = new ilObjTest(0, true);
            if (count($founditems) > 0) {
                $qtiParser = new ilQTIParser($qti_file, IL_MO_PARSE_QTI, 0, "");
                $qtiParser->setTestObject($testObj);
                $result = $qtiParser->startParsing();
                $this->qtis = array_merge($this->qtis, $qtiParser->getImportMapping());
            }
        }
    }

    /**
     * Final processing
     *
     * @param	array		mapping array
     */
    public function finalProcessing($a_mapping)
    {
        $pg_map = $a_mapping->getMappingsOfEntity("Modules/LearningModule", "pg");

        include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
        include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
        $this->log->debug("pg map entries: " . count($pg_map));
        foreach ($pg_map as $pg_id) {
            $lm_id = ilLMPageObject::_lookupContObjID($pg_id);
            ilLMPage::_writeParentId("lm", $pg_id, $lm_id);
            $this->log->debug("write parent id, pg id: " . $pg_id . ", lm id: " . $lm_id);
        }

        // header footer page
        foreach ($a_mapping->getMappingsOfEntity("Modules/LearningModule", "lm_header_page") as $old_id => $dummy) {
            $new_page_id = (int) $a_mapping->getMapping("Modules/LearningModule", "pg", $old_id);
            if ($new_page_id > 0) {
                $lm_id = ilLMPageObject::_lookupContObjID($new_page_id);
                ilObjLearningModule::writeHeaderPage($lm_id, $new_page_id);
            }
        }
        foreach ($a_mapping->getMappingsOfEntity("Modules/LearningModule", "lm_footer_page") as $old_id => $dummy) {
            $new_page_id = (int) $a_mapping->getMapping("Modules/LearningModule", "pg", $old_id);
            if ($new_page_id > 0) {
                $lm_id = ilLMPageObject::_lookupContObjID($new_page_id);
                ilObjLearningModule::writeFooterPage($lm_id, $new_page_id);
            }
        }


        $link_map = $a_mapping->getMappingsOfEntity("Modules/LearningModule", "link");
        $pages = $a_mapping->getMappingsOfEntity("Services/COPage", "pgl");
        foreach ($pages as $p) {
            $id = explode(":", $p);
            if (count($id) == 3) {
                include_once("./Services/COPage/classes/class.ilPageObject.php");
                if (ilPageObject::_exists($id[0], $id[1], $id[2], true)) {
                    include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");
                    $new_page = ilPageObjectFactory::getInstance($id[0], $id[1], 0, $id[2]);
                    $new_page->buildDom();

                    // fix question references
                    $updated = $new_page->resolveQuestionReferences($this->qtis);

                    // in translation mode use link mapping to fix internal links
                    //$a_mapping->addMapping("Modules/LearningModule", "link",
                    if ($this->config->getTranslationImportMode()) {
                        $il = $new_page->resolveIntLinks($link_map);
                        if ($il) {
                            $updated = true;
                        }
                    }

                    if ($updated) {
                        $new_page->update(false, true);
                    }
                }
            }
        }

        // assign style
        $alls_map = $a_mapping->getMappingsOfEntity("Modules/LearningModule", "lm_style");
        foreach ($alls_map as $new_lm_id => $old_style_id) {
            $new_style_id = (int) $a_mapping->getMapping("Services/Style", "sty", $old_style_id);
            if ($new_lm_id > 0 && $new_style_id > 0) {
                include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
                $lm = new ilObjLearningModule($new_lm_id, false);
                $lm->writeStyleSheetId($new_style_id);
            }
        }

        // menu item ref ids
        $ref_mapping = $a_mapping->getMappingsOfEntity('Services/Container', 'refs');
        $lm_map = $a_mapping->getMappingsOfEntity("Modules/LearningModule", "lm");
        foreach ($lm_map as $old_lm_id => $new_lm_id) {
            ilLMMenuEditor::fixImportMenuItems($new_lm_id, $ref_mapping);
        }
    }
}
