<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for question pools
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */

class ilTestQuestionPoolImporter extends ilXmlImporter
{
    /**
     * @var ilObjQuestionPool
     */
    private $poolOBJ;
    
    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        /* @var ilObjQuestionPool $newObj */
        
        include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
        ilObjQuestionPool::_setImportDirectory($this->getImportDirectoryContainer());
        
        // Container import => pool object already created
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
            $newObj->setOnline(true);

            $_SESSION['qpl_import_subdir'] = $this->getImportPackageName();

            $newObj->setOnline(true);
        } elseif ($new_id = $a_mapping->getMapping('Modules/TestQuestionPool', 'qpl', "new_id")) {
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
        } else {
            // Shouldn't happen
            global $DIC; /* @var ILIAS\DI\Container $DIC */
            $DIC['ilLog']->write(__METHOD__ . ': non container and no tax mapping, perhaps old qpl export');
            return false;
        }

        

        list($xml_file, $qti_file) = $this->parseXmlFileNames();
        
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        if (!@file_exists($xml_file)) {
            $DIC['ilLog']->write(__METHOD__ . ': Cannot find xml definition: ' . $xml_file);
            return false;
        }
        if (!@file_exists($qti_file)) {
            $DIC['ilLog']->write(__METHOD__ . ': Cannot find qti definition: ' . $qti_file);
            return false;
        }
        
        $this->poolOBJ = $newObj;
        
        $newObj->fromXML($xml_file);

        // set another question pool name (if possible)
        if (isset($_POST["qpl_new"]) && strlen($_POST["qpl_new"])) {
            $newObj->setTitle($_POST["qpl_new"]);
        }
        
        $newObj->update();
        $newObj->saveToDb();

        // FIXME: Copied from ilObjQuestionPoolGUI::importVerifiedFileObject
        // TODO: move all logic to ilObjQuestionPoolGUI::importVerifiedFile and call
        // this method from ilObjQuestionPoolGUI and ilTestImporter
        
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $DIC['ilLog']->write(__METHOD__ . ': xml file: ' . $xml_file . ", qti file:" . $qti_file);
        
        if (isset($_SESSION["qpl_import_idents"])) {
            $idents = $_SESSION["qpl_import_idents"];
            unset($_SESSION["qpl_import_idents"]);
        } else {
            $idents = null;
        }
        
        // start parsing of QTI files
        include_once "./Services/QTI/classes/class.ilQTIParser.php";
        $qtiParser = new ilQTIParser($qti_file, IL_MO_PARSE_QTI, $newObj->getId(), $idents);
        $result = $qtiParser->startParsing();

        // import page data
        if (strlen($xml_file)) {
            include_once("./Modules/LearningModule/classes/class.ilContObjParser.php");
            $contParser = new ilContObjParser($newObj, $xml_file, basename($this->getImportDirectory()));
            $contParser->setQuestionMapping($qtiParser->getImportMapping());
            $contParser->startParsing();
            
            foreach ($qtiParser->getImportMapping() as $k => $v) {
                $oldQuestionId = substr($k, strpos($k, 'qst_') + strlen('qst_'));
                $newQuestionId = $v['pool']; // yes, this is the new question id ^^
                
                $a_mapping->addMapping(
                    "Services/Taxonomy",
                    "tax_item",
                    "qpl:quest:$oldQuestionId",
                    $newQuestionId
                );
                
                $a_mapping->addMapping(
                    "Services/Taxonomy",
                    "tax_item_obj_id",
                    "qpl:quest:$oldQuestionId",
                    $newObj->getId()
                );

                $a_mapping->addMapping(
                    "Modules/TestQuestionPool",
                    "quest",
                    $oldQuestionId,
                    $newQuestionId
                );
            }
        }
        
        $this->importQuestionSkillAssignments($xml_file, $a_mapping, $newObj->getId());

        $a_mapping->addMapping("Modules/TestQuestionPool", "qpl", $a_id, $newObj->getId());

        ilObjQuestionPool::_setImportDirectory(null);
        
        $newObj->saveToDb();
    }

    /**
     * Final processing
     *
     * @param ilImportMapping $a_mapping
     * @return
     */
    public function finalProcessing($a_mapping)
    {
        //echo "<pre>".print_r($a_mapping, true)."</pre>"; exit;
        // get all glossaries of the import
        include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
        $maps = $a_mapping->getMappingsOfEntity("Modules/TestQuestionPool", "qpl");
        foreach ($maps as $old => $new) {
            if ($old != "new_id" && (int) $old > 0) {
                // get all new taxonomys of this object
                $new_tax_ids = $a_mapping->getMapping("Services/Taxonomy", "tax_usage_of_obj", $old);
                if ($new_tax_ids !== false) {
                    $tax_ids = explode(":", $new_tax_ids);
                    foreach ($tax_ids as $tid) {
                        ilObjTaxonomy::saveUsage($tid, $new);
                    }
                }
                
                $taxMappings = $a_mapping->getMappingsOfEntity('Services/Taxonomy', 'tax');
                foreach ($taxMappings as $oldTaxId => $newTaxId) {
                    if ($oldTaxId == $this->poolOBJ->getNavTaxonomyId()) {
                        $this->poolOBJ->setNavTaxonomyId($newTaxId);
                        $this->poolOBJ->saveToDb();
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Create qti and xml file name
     * @return array
     */
    protected function parseXmlFileNames()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $DIC['ilLog']->write(__METHOD__ . ': ' . $this->getImportDirectory());
        
        $basename = basename($this->getImportDirectory());

        $xml = $this->getImportDirectory() . '/' . $basename . '.xml';
        $qti = $this->getImportDirectory() . '/' . preg_replace('/qpl/', 'qti', $basename) . '.xml';
        
        return array($xml,$qti);
    }

    private function getImportDirectoryContainer()
    {
        $dir = $this->getImportDirectory();
        $dir = dirname($dir);
        return $dir;
    }

    private function getImportPackageName()
    {
        $dir = $this->getImportDirectory();
        $name = basename($dir);
        return $name;
    }
    
    protected function importQuestionSkillAssignments($xmlFile, ilImportMapping $mappingRegistry, $targetParentObjId)
    {
        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentXmlParser.php';
        $parser = new ilAssQuestionSkillAssignmentXmlParser($xmlFile);
        $parser->startParsing();

        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImporter.php';
        $importer = new ilAssQuestionSkillAssignmentImporter();
        $importer->setTargetParentObjId($targetParentObjId);
        $importer->setImportInstallationId($this->getInstallId());
        $importer->setImportMappingRegistry($mappingRegistry);
        $importer->setImportMappingComponent('Modules/TestQuestionPool');
        $importer->setImportAssignmentList($parser->getAssignmentList());
        
        $importer->import();
        
        if ($importer->getFailedImportAssignmentList()->assignmentsExist()) {
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImportFails.php';
            $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($targetParentObjId);
            $qsaImportFails->registerFailedImports($importer->getFailedImportAssignmentList());
            
            $this->poolOBJ->setOnline(false);
        }
    }
}
