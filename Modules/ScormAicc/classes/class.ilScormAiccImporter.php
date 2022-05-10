<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilScormAiccImporter extends ilXmlImporter
{
    private ilScormAiccDataSet $dataset;
    public array $moduleProperties;

    public function __construct()
    {
        $this->dataset = new ilScormAiccDataSet();
        //todo: at the moment restricted to one module in xml file, extend?
        $this->moduleProperties = [];
        //$this->manifest = [];
    }

    public function init() : void
    {
    }

    /**
     * Import XML
     * @throws ilDatabaseException
     * @throws ilFileUtilsException
     * @throws ilObjectNotFoundException
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ?ilImportMapping $a_mapping) : void
    {
        global $DIC;
        $ilLog = ilLoggerFactory::getLogger('sahs');
        
//        if ($this->handleEditableLmXml($a_entity, $a_id, $a_xml, $a_mapping)) {
//            return;
//        }
        // case i container
        if ($a_id !== "" && $a_mapping !== null && $new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
            $exportDir = ilExport::_getExportDirectory((int) $a_id);
            $tempFile = dirname($exportDir) . '/export/' . basename($this->getImportDirectory()) . '.zip';
            $timeStamp = time();
            $lmDir = ilFileUtils::getWebspaceDir("filesystem") . "/lm_data/";
            $lmTempDir = $lmDir . $timeStamp;
            if (!file_exists($lmTempDir)) {
                if (!mkdir($lmTempDir, 0755, true) && !is_dir($lmTempDir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $lmTempDir));
                }
            }
            $zar = new ZipArchive();
            $zar->open($tempFile);
            $zar->extractTo($lmTempDir);
            $zar->close();
            $a_xml = $lmTempDir . '/' . basename($this->getImportDirectory());
        }



        $result = false;
        if (file_exists($a_xml)) {
            $manifestFile = $a_xml . "/manifest.xml";
            if (file_exists($manifestFile)) {
                $manifest = file_get_contents($manifestFile);
                if (isset($manifest)) {
                    $propertiesFile = $a_xml . "/properties.xml";
                    $xml = file_get_contents($propertiesFile);
                    if (isset($xml)) {
                        $xmlRoot = simplexml_load_string($xml);
                        foreach ($this->dataset->properties as $key => $value) {
                            $this->moduleProperties[$key] = $xmlRoot->$key;
                        }
                        $this->moduleProperties["Title"] = $xmlRoot->Title;
                        $this->moduleProperties["Description"] = $xmlRoot->Description;
                        
                        if ($a_id !== "" && $a_mapping !== null && $new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
                            if ($newObj !== null) {
                                $this->dataset->writeData("sahs", "5.1.0", $newObj->getId(), $this->moduleProperties);

                                $newObj->createReference();

                                $scormFile = "content.zip";
                                $scormFilePath = $a_xml . "/" . $scormFile;
                                $targetPath = $newObj->getDataDirectory() . "/" . $scormFile;
                                $file_path = $targetPath;

                                ilFileUtils::rename($scormFilePath, $targetPath);
                                ilFileUtils::unzip($file_path);
                                unlink($file_path);
                                ilFileUtils::delDir($lmTempDir, false);
                                ilFileUtils::renameExecutables($newObj->getDataDirectory());

                                $newId = $newObj->getRefId();
                                // $newObj->putInTree($newId);
                                // $newObj->setPermissions($newId);
                                $subType = $this->moduleProperties["SubType"][0];
                                if ($subType === "scorm") {
                                    $newObj = new ilObjSCORMLearningModule($newId);
                                } else {
                                    $newObj = new ilObjSCORM2004LearningModule($newId);
                                }
                                $title = $newObj->readObject();
                                //auto set learning progress settings
                                $newObj->setLearningProgressSettingsAtUpload();
                            }
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
    }

    public function writeData(string $a_entity, string $a_version, int $a_id) : void
    {
        $this->dataset->writeData($a_entity, $a_version, $a_id, $this->moduleProperties);
    }

//    /**
//     * Handle editable (authoring) scorm lms
//     *
//     * @param string $a_entity entity
//     * @param string $a_id id
//     * @param string $a_xml xml
//     * @param ilImportMapping $a_mapping import mapping object
//     * @return bool success
//     */
//    public function handleEditableLmXml(string $a_entity, string $a_id, string $a_xml, \ilImportMapping $a_mapping) : bool
//    {
//        // if editable...
//        if (is_int(strpos($a_xml, "<Editable>1</Editable>"))) {
//            $dataset = new ilScorm2004DataSet();
//            $dataset->setDSPrefix("ds");
//            $dataset->setImportDirectory($this->getImportDirectory());
//            $parser = new ilDataSetImportParser(
//                $a_entity,
//                $this->getSchemaVersion(),
//                $a_xml,
//                $dataset,
//                $a_mapping
//            );
//            return true;
//        }
//        return false;
//    }
}
