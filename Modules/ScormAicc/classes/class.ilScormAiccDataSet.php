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
class ilScormAiccDataSet extends ilDataSet
{
    private string $db_table;
    public array $properties;
    private array $element_db_mapping;

    public function __construct()
    {
        $this->db_table = "sahs_lm";
        $this->properties = [
            "Id" => ["db_col" => "id", "db_type" => "integer"],
            "APIAdapterName" => ["db_col" => "api_adapter", "db_type" => "text"],
            "APIFunctionsPrefix" => ["db_col" => "api_func_prefix", "db_type" => "text"],
            "AssignedGlossary" => ["db_col" => "glossary", "db_type" => "integer"],
            "AutoContinue" => ["db_col" => "auto_continue", "db_type" => "text"],
            "AutoReviewChar" => ["db_col" => "auto_review", "db_type" => "text"],
            "AutoSuspend" => ["db_col" => "auto_suspend", "db_type" => "text"],
            "Auto_last_visited" => ["db_col" => "auto_last_visited", "db_type" => "text"],
            "Check_values" => ["db_col" => "check_values", "db_type" => "text"],
            "Comments" => ["db_col" => "comments", "db_type" => "text"],
            "CreditMode" => ["db_col" => "credit", "db_type" => "text"],
            "Debug" => ["db_col" => "debug", "db_type" => "text"],
            "DebugPw" => ["db_col" => "debugpw", "db_type" => "text"],
            "DefaultLessonMode" => ["db_col" => "default_lesson_mode", "db_type" => "text"],
            "Editable" => ["db_col" => "editable", "db_type" => "integer"],
            "Fourth_edition" => ["db_col" => "fourth_edition", "db_type" => "text"],
            "Height" => ["db_col" => "height", "db_type" => "integer"],
            "HideNavig" => ["db_col" => "hide_navig", "db_type" => "text"],
            "Ie_force_render" => ["db_col" => "ie_force_render", "db_type" => "text"],
            "Interactions" => ["db_col" => "interactions", "db_type" => "text"],
            "Localization" => ["db_col" => "localization", "db_type" => "text"],
            "MasteryScore" => ["db_col" => "mastery_score", "db_type" => "integer"],
            "MaxAttempt" => ["db_col" => "max_attempt", "db_type" => "integer"],
            "ModuleVersion" => ["db_col" => "module_version", "db_type" => "integer"],
            "NoMenu" => ["db_col" => "no_menu", "db_type" => "text"],
            "Objectives" => ["db_col" => "objectives", "db_type" => "text"],
            "OfflineMode" => ["db_col" => "offline_mode", "db_type" => "text"],
            "OpenMode" => ["db_col" => "open_mode", "db_type" => "integer"],
            "Sequencing" => ["db_col" => "sequencing", "db_type" => "text"],
            "SequencingExpertMode" => ["db_col" => "seq_exp_mode", "db_type" => "integer"],
            "Session" => ["db_col" => "unlimited_session", "db_type" => "text"],
            "StyleSheetId" => ["db_col" => "stylesheet", "db_type" => "integer"],
            "SubType" => ["db_col" => "c_type", "db_type" => "text"],
            "Time_from_lms" => ["db_col" => "time_from_lms", "db_type" => "text"],
            "Tries" => ["db_col" => "question_tries", "db_type" => "integer"],
            "Width" => ["db_col" => "width", "db_type" => "integer"],
            "IdSetting" => ["db_col" => "id_setting", "db_type" => "integer"],
            "NameSetting" => ["db_col" => "name_setting", "db_type" => "integer"]
        ];

        $this->element_db_mapping = [];
        foreach ($this->properties as $key => $value) {
            $this->element_db_mapping [$value["db_col"]] = $key;
        }
    }

    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        return [];
    }

    public function writeData(string $a_entity, string $a_version, int $a_id, array $data) : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilLog = ilLoggerFactory::getLogger('sahs');
        if (count($data) > 0) {
            $columns = [];
            foreach ($this->properties as $key => $value) {
                if ($key === "Id" || $key === "title" || $key === "description") {
                    continue;
                }
                //fix localization and mastery_score
                if ($key === "MasteryScore" && $data[$key][0] == 0) {
                    continue;
                }
                if ($key === "Localization" && $data[$key][0] == "") {
                    continue;
                }
                //end fix
                if (isset($data[$key]) && is_array($data[$key])) {
                    if (count($data[$key]) > 0) {
                        $columns [$value["db_col"]] = [$value["db_type"], $data[$key][0]];
                    }
                }
            }
            if (is_array($columns)) {
                if (count($columns) > 0) {
                    $conditions ["id"] = ["integer", $a_id];
                    $ilDB->update($this->db_table, $columns, $conditions);
                }
            }

            //setting title and description in table object_data
            $od_table = "object_data";
            $od_properties = [
                "Title" => ["db_col" => "title", "db_type" => "text"],
                "Description" => ["db_col" => "description", "db_type" => "text"]
            ];
            foreach ($od_properties as $key => $value) {
                if (isset($data[$key]) && is_array($data[$key])) {
                    if (count($data[$key]) > 0) {
                        $od_columns [$value["db_col"]] = [$value["db_type"], $data[$key][0]];
                    }
                }

                if (is_array($od_columns)) {
                    if (count($od_columns) > 0) {
                        $od_conditions ["obj_id"] = ["integer", $a_id];
                        $ilDB->update("object_data", $od_columns, $od_conditions);
                    }
                }
            }
        } else {
            $ilLog->write("no module properties for imported object");
        }
    }

    /**
     * own getXmlRepresentation function to embed zipfile in xml
     */
    public function getExtendedXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        array $a_ids,
        string $a_field = "",
        bool $a_omit_header = false,
        bool $a_omit_types = false
    ) : string {
        $GLOBALS['DIC']["ilLog"]->write(json_encode($this->getTypes("sahs", "5.1.0"), JSON_PRETTY_PRINT));

        $this->dircnt = 1;

        $this->readData($a_entity, $a_schema_version, $a_ids, $a_field = "");
        $id = (string) $a_ids[0];
        $exportDir = ilExport::_getExportDirectory((int) $id, "xml", "sahs");
        $writer = new ilXmlWriter();
        if (!$a_omit_header) {
            $writer->xmlHeader();
        }

        $atts = array("InstallationId" => IL_INST_ID,
                      "InstallationUrl" => ILIAS_HTTP_PATH,
                      "TopEntity" => $a_entity
        );

        $writer->appendXML("\n");
        $writer->xmlStartTag($this->getDSPrefixString() . 'DataSet', $atts);
        $writer->appendXML("\n");

        foreach ($this->data as $key => $value) {
            $writer->xmlElement($this->getElementNameByDbColumn($key), null, $value, true, true);
            $writer->appendXML("\n");
        }

        $lmDir = ilFileUtils::getWebspaceDir("filesystem") . "/lm_data/lm_" . $id;
        $baseFileName = "sahs_" . $id;
        $scormBasePath = $exportDir . "/" . $baseFileName;
        if (!file_exists($exportDir)) {
            if (!mkdir($exportDir, 0755, true) && !is_dir($exportDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $exportDir));
            }
        }
        ilFileUtils::zip($lmDir, $scormBasePath, true);
        $scormFilePath = $scormBasePath . ".zip";

        $writer->xmlEndTag($this->getDSPrefixString() . "DataSet");
        $writer->appendXML("\n");

        $xml = $writer->xmlDumpMem(false);
        $baseExportName = time() . "__" . IL_INST_ID . "__" . $baseFileName;
        $xmlFilePath = $exportDir . "/" . $baseExportName . ".xml";

        if (!file_exists($xmlFilePath)) {
            $xmlFile = fopen($xmlFilePath, "wb");//changed from w to wb
            fwrite($xmlFile, $xml);
            fclose($xmlFile);
        }

        //create metadata
        $metaData = $this->buildMetaData((int) $id);

        $metaDataFilePath = $exportDir . "/" . $baseExportName . "_metadata.xml";
        if (!file_exists($metaDataFilePath)) {
            $metaDataFile = fopen($metaDataFilePath, "wb");//changed from w to wb
            fwrite($metaDataFile, $metaData);
            fclose($metaDataFile);
        }

        //create manifest file
        $manWriter = new ilXmlWriter();
        $manWriter->xmlHeader();
        $manWriter->appendXML("\n<content>\n");

        $files = [
            "scormFile" => "content.zip",
            "properties" => "properties.xml",
            "metadata" => "metadata.xml"
        ];
        foreach ($files as $key => $value) {
            $manWriter->xmlElement($key, null, $value, true, true);
            $manWriter->appendXML("\n");
        }

        $manWriter->appendXML("</content>\n");
        $manifest = $manWriter->xmlDumpMem(false);

        $manifestFilePath = $exportDir . "/" . $baseExportName . "_manifest.xml";
        if (!file_exists($manifestFilePath)) {
            $manifestFile = fopen($manifestFilePath, "wb");//changed from w to wb
            fwrite($manifestFile, $manifest);
            fclose($manifestFile);
        }

        usleep(2_000_000);
        $zArchive = new zipArchive();
        $fileName = $exportDir . "/" . $baseExportName . ".zip";

        if ($zArchive->open($fileName, ZipArchive::CREATE) !== true) {
            exit("cannot open <$fileName>\n");
        }

        //creating final zip file
        $zArchive->addFile($xmlFilePath, $baseExportName . '/properties.xml');
        $zArchive->addFile($scormFilePath, $baseExportName . '/content.zip');
        $zArchive->addFile($manifestFilePath, $baseExportName . '/' . "manifest.xml");
        $zArchive->addFile($metaDataFilePath, $baseExportName . '/' . "metadata.xml");
        $zArchive->close();
        //delete temporary files
        unlink($xmlFilePath);
        unlink($scormFilePath);
        unlink($manifestFilePath);
        unlink($metaDataFilePath);

        return $fileName;
    }

    /**
     * Get field types for entity
     * @param string $a_entity entity
     * @param string $a_version version number
     * @return array types array
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        if ($a_entity === "sahs") {
            switch ($a_version) {
                case "5.1.0":
                    $types = [];
                    foreach ($this->properties as $key => $value) {
                        $types[$key] = $value["db_type"];
                    }
                    return $types;
            }
        }
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $obj_id = (int) $a_ids;
        $columns = [];
        foreach ($this->properties as $property) {
            $columns[] = $property["db_col"];
        }

        $query = "SELECT " . implode(",", $columns) . " FROM " . $this->db_table;
        $query .= " WHERE id=" . $ilDB->quote($obj_id, "integer");
        $result = $ilDB->query($query);
        $this->data = [];
        if ($dataset = $ilDB->fetchAssoc($result)) {
            $this->data = $dataset;
        }

        $query = "SELECT title,description FROM object_data";
        $query .= " WHERE obj_id=" . $ilDB->quote($obj_id, "integer");
        $result = $ilDB->query($query);
        while ($dataset = $ilDB->fetchAssoc($result)) {
            $this->data ["title"] = $dataset["title"];
            $this->data ["description"] = $dataset["description"];
        }
    }

    /**
     * retrieve element name by database column name
     */
    public function getElementNameByDbColumn(string $db_col_name) : string
    {
        if ($db_col_name === "title") {
            return "Title";
        }
        if ($db_col_name === "description") {
            return "Description";
        }
        return $this->element_db_mapping[$db_col_name];
    }

    public function buildMetaData(int $id) : string
    {
        $md2xml = new ilMD2XML($id, $id, "sahs");
        $md2xml->startExport();
        return $md2xml->getXML();
    }

    /**
     * Get xml namespace
     */
    public function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "http://www.ilias.de/xml/Modules/ScormAicc/" . $a_entity;
    }

    /**
     * @return string[]
     */
    public function getSupportedVersions() : array
    {
        return ["5.1.0"];
    }
}
