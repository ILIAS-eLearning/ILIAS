<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Export/classes/class.ilXmlImporter.php");
class ilScormAiccImporter extends ilXmlImporter
{
    public function __construct()
    {
        require_once "./Modules/ScormAicc/classes/class.ilScormAiccDataSet.php";
        $this->dataset = new ilScormAiccDataSet();
        //todo: at the moment restricted to one module in xml file, extend?
        $this->moduleProperties = [];
        $this->manifest = [];
    }

    public function init()
    {
    }

    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_import_dirname, $a_mapping)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        
        if ($this->handleEditableLmXml($a_entity, $a_id, $a_import_dirname, $a_mapping)) {
            return true;
        }
        // case i container
        if ($a_id != null && $new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);

            require_once("./Services/Export/classes/class.ilExport.php");
            $exportDir = ilExport::_getExportDirectory($a_id);
            $tempFile  = dirname($exportDir) . '/export/' . basename($this->getImportDirectory()) . '.zip';
            $timeStamp = time();
            $lmDir = ilUtil::getWebspaceDir("filesystem") . "/lm_data/";
            $lmTempDir = $lmDir . $timeStamp;
            if (!file_exists($lmTempDir)) {
                mkdir($lmTempDir, 0755, true);
            }
            $zar = new ZipArchive();
            $zar->open($tempFile);
            $zar->extractTo($lmTempDir);
            $zar->close();
            $a_import_dirname = $lmTempDir . '/' . basename($this->getImportDirectory());
        }



        $result = false;
        if (file_exists($a_import_dirname)) {
            $manifestFile = $a_import_dirname . "/manifest.xml";
            if (file_exists($manifestFile)) {
                $manifest = file_get_contents($manifestFile);
                if (isset($manifest)) {
                    $propertiesFile = $a_import_dirname . "/properties.xml";
                    $xml = file_get_contents($propertiesFile);
                    if (isset($xml)) {
                        $xmlRoot = simplexml_load_string($xml);
                        foreach ($this->dataset->properties as $key => $value) {
                            $this->moduleProperties[$key] = $xmlRoot->$key;
                        }
                        $this->moduleProperties["Title"] = $xmlRoot->Title;
                        $this->moduleProperties["Description"] = $xmlRoot->Description;
                        
                        if ($a_id != null && $new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
                            $this->dataset->writeData("sahs", "5.1.0", $newObj->getId(), $this->moduleProperties);

                            $newObj->createReference();

                            $scormFile = "content.zip";
                            $scormFilePath = $a_import_dirname . "/" . $scormFile;
                            $targetPath = $newObj->getDataDirectory() . "/" . $scormFile;
                            $file_path = $targetPath;

                            ilFileUtils::rename($scormFilePath, $targetPath);
                            ilUtil::unzip($file_path);
                            unlink($file_path);
                            ilUtil::delDir($lmTempDir, false);
                            ilUtil::renameExecutables($newObj->getDataDirectory());

                            $newId = $newObj->getRefId();
                            // $newObj->putInTree($newId);
                            // $newObj->setPermissions($newId);
                            $subType = $this->moduleProperties["SubType"][0];
                            if ($subType == "scorm") {
                                include_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php");
                                $newObj = new ilObjSCORMLearningModule($newId);
                            } else {
                                include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
                                $newObj = new ilObjSCORM2004LearningModule($newId);
                            }
                            $title = $newObj->readObject();
                            //auto set learning progress settings
                            $newObj->setLearningProgressSettingsAtUpload();
                        }
                        
                        
                        $result = true;
                    } else {
                        $ilLog->write("error parsing xml file for scorm import");
                        //error xml parsing
                    }
                } else {
                    $ilLog->write("error reading manifest file");
                }
            } else {
                $ilLog->write("error no manifest file found");
            }
        } else {
            $ilLog->write("error file lost while importing");
            //error non existing file
        }
        return $result;
    }

    public function writeData($a_entity, $a_version, $a_id)
    {
        $this->dataset->writeData($a_entity, $a_version, $a_id, $this->moduleProperties);
    }

    /**
     * Handle editable (authoring) scorm lms
     *
     * @param string $a_entity entity
     * @param string $a_id id
     * @param string $a_xml xml
     * @param ilImportMapping $a_mapping import mapping object
     * @return bool success
     */
    public function handleEditableLmXml($a_entity, $a_id, $a_xml, $a_mapping)
    {
        // if editable...
        if (is_int(strpos($a_xml, "<Editable>1</Editable>"))) {
            // ...use ilScorm2004DataSet for import
            include_once("./Modules/Scorm2004/classes/class.ilScorm2004DataSet.php");
            $dataset = new ilScorm2004DataSet();
            $dataset->setDSPrefix("ds");
            $dataset->setImportDirectory($this->getImportDirectory());

            include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
            $parser = new ilDataSetImportParser(
                $a_entity,
                $this->getSchemaVersion(),
                $a_xml,
                $dataset,
                $a_mapping
            );
            return true;
        }
        return false;
    }
}
