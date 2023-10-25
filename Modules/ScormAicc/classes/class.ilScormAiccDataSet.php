<?php

declare(strict_types=1);

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

class ilScormAiccDataSet extends ilDataSet
{
    private string $db_table;
    public array $properties;
    private array $_archive;
    private array $element_db_mapping;

    public function __construct()
    {
        $this->db_table = "sahs_lm";
        $this->_archive = [];
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
    ): array {
        return [];
    }

    public function writeData(string $a_entity, string $a_version, int $a_id, array $data): void
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
                if ($key === "MasteryScore" && isset($data[$key][0]) && $data[$key][0] == 0) {
                    continue;
                }
                if ($key === "Localization" && isset($data[$key][0]) && $data[$key][0] == "") {
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

                if (isset($od_columns) && is_array($od_columns)) {
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
    ): string {
        $GLOBALS['DIC']["ilLog"]->write(json_encode($this->getTypes("sahs", "5.1.0"), JSON_PRETTY_PRINT));

        $this->dircnt = 1;

        $this->readData($a_entity, $a_schema_version, $a_ids, $a_field = "");
        $id = (int) $this->data["id"];
        $exportDir = ilExport::_getExportDirectory((int) $id, "xml", "sahs");

        // prepare archive skeleton
        $objTypeAndId = "sahs_" . $id;
        $this->_archive['directories'] = [
            "exportDir" => ilExport::_getExportDirectory($id)
            ,"tempDir" => ilExport::_getExportDirectory($id) . "/temp"
            ,"archiveDir" => time() . "__" . IL_INST_ID . "__" . $objTypeAndId
            ,"moduleDir" => $objTypeAndId
        ];

        $this->_archive['files'] = [
            "properties" => "properties.xml",
            "metadata" => "metadata.xml",
            "manifest" => 'manifest.xml',
            'scormFile' => "content.zip"
        ];

        // Prepare temp storage on the local filesystem
        if (!file_exists($this->_archive['directories']['exportDir'])) {
            mkdir($this->_archive['directories']['exportDir'], 0755, true);
            //$DIC->filesystem()->storage()->createDir($this->_archive['directories']['tempDir']);
        }
        if (!file_exists($this->_archive['directories']['tempDir'])) {
            mkdir($this->_archive['directories']['tempDir'], 0755, true);
        }

        // build metadata xml file
        file_put_contents(
            $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['metadata'],
            $this->buildMetaData($id)
        );

        // build manifest xml file
        file_put_contents(
            $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['manifest'],
            $this->buildManifest()
        );

        // build content zip file
        if (isset($this->_archive['files']['scormFile'])) {
            $lmDir = ilFileUtils::getWebspaceDir("filesystem") . "/lm_data/lm_" . $id;
            ilFileUtils::zip($lmDir, $this->_archive['directories']['tempDir'] . "/" . substr($this->_archive['files']['scormFile'], 0, -4), true);
        }

        // build property xml file
        file_put_contents(
            $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['properties'],
            $this->buildProperties($a_entity, $a_omit_header)
        );

        // zip tempDir and append to export folder
        $fileName = $this->_archive['directories']['exportDir'] . "/" . $this->_archive['directories']['archiveDir'] . ".zip";
        $zArchive = new ZipArchive();
        if ($zArchive->open($fileName, ZipArchive::CREATE) !== true) {
            exit("cannot open <$fileName>\n");
        }
        $zArchive->addFile(
            $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['properties'],
            $this->_archive['directories']['archiveDir'] . '/properties.xml'
        );
        $zArchive->addFile(
            $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['manifest'],
            $this->_archive['directories']['archiveDir'] . '/' . "manifest.xml"
        );
        $zArchive->addFile(
            $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['metadata'],
            $this->_archive['directories']['archiveDir'] . '/' . "metadata.xml"
        );
        if (isset($this->_archive['files']['scormFile'])) {
            $zArchive->addFile(
                $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['scormFile'],
                $this->_archive['directories']['archiveDir'] . '/content.zip'
            );
        }
        $zArchive->close();

        // unlink tempDir and its content
        unlink($this->_archive['directories']['tempDir'] . "/metadata.xml");
        unlink($this->_archive['directories']['tempDir'] . "/manifest.xml");
        unlink($this->_archive['directories']['tempDir'] . "/properties.xml");
        if (isset($this->_archive['files']['scormFile']) && file_exists($this->_archive['directories']['tempDir'] . "/content.zip")) {
            unlink($this->_archive['directories']['tempDir'] . "/content.zip");
        }

        return $fileName;
    }

    /**
     * Get field types for entity
     * @param string $a_entity entity
     * @param string $a_version version number
     * @return array types array
     */
    protected function getTypes(string $a_entity, string $a_version): array
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

    public function readData(string $a_entity, string $a_version, array $a_ids, string $a_field = ""): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $obj_id = (int) $a_ids[0];
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
    public function getElementNameByDbColumn(string $db_col_name): string
    {
        if ($db_col_name === "title") {
            return "Title";
        }
        if ($db_col_name === "description") {
            return "Description";
        }
        return $this->element_db_mapping[$db_col_name];
    }

    public function buildMetaData(int $id): string
    {
        $md2xml = new ilMD2XML($id, $id, "sahs");
        $md2xml->startExport();
        return $md2xml->getXML();
    }

    /**
     * Get xml namespace
     */
    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "http://www.ilias.de/xml/Modules/ScormAicc/" . $a_entity;
    }

    /**
     * @return string[]
     */
    public function getSupportedVersions(): array
    {
        return ["5.1.0"];
    }

    /**
     * @return string
     */
    private function buildManifest(): string
    {
        $manWriter = new ilXmlWriter();
        $manWriter->xmlHeader();
        foreach ($this->_archive['files'] as $key => $value) {
            $manWriter->xmlElement($key, null, $value, true, true);
        }

        return $manWriter->xmlDumpMem(true);
    }

    /**
     * @param $a_entity
     * @param bool $a_omit_header
     * @return string
     */
    private function buildProperties($a_entity, bool $a_omit_header = false): string
    {
        $writer = new ilXmlWriter();

        if (!$a_omit_header) {
            $writer->xmlHeader();
        }

        $writer->appendXML("\n");
        $writer->xmlStartTag('DataSet', array(
            "InstallationId" => IL_INST_ID,
            "InstallationUrl" => ILIAS_HTTP_PATH,
            "TopEntity" => $a_entity
        ));

        $writer->appendXML("\n");

        foreach ($this->data as $key => $value) {
            $writer->xmlElement($this->getElementNameByDbColumn($key), null, $value, true, true);
            $writer->appendXML("\n");
        }

        $writer->xmlEndTag("DataSet");

        return $writer->xmlDumpMem(false);
    }
}
