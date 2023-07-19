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

/**
 * Class ilCmiXapiDataSet
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 * @package     Module/CmiXapi
 */
class ilCmiXapiDataSet extends ilDataSet
{
    private array $_data = [];
    private array $_archive = [];
    private array $_element_db_mapping = [];
    public array $_cmixSettingsProperties = [
        "LrsTypeId" => ["db_col" => "lrs_type_id", "db_type" => "integer"]
        ,
        "ContentType" => ["db_col" => "content_type", "db_type" => "text"]
        ,
        "SourceType" => ["db_col" => "source_type", "db_type" => "text"]
        ,
        "ActivityId" => ["db_col" => "activity_id", "db_type" => "text"]
        ,
        "Instructions" => ["db_col" => "instructions", "db_type" => "text"]
        // ,"OfflineStatus" => ["db_col" => "offline_status", "db_type" => "integer"]
        ,
        "LaunchUrl" => ["db_col" => "launch_url", "db_type" => "text"]
        ,
        "AuthFetchUrl" => ["db_col" => "auth_fetch_url", "db_type" => "integer"]
        ,
        "LaunchMethod" => ["db_col" => "launch_method", "db_type" => "text"]
        ,
        "LaunchMode" => ["db_col" => "launch_mode", "db_type" => "text"]
        ,
        "MasteryScore" => ["db_col" => "mastery_score", "db_type" => "float"]
        ,
        "KeepLp" => ["db_col" => "keep_lp", "db_type" => "integer"]
        ,
        "PrivacyIdent" => ["db_col" => "privacy_ident", "db_type" => "integer"]
        ,
        "PrivacyName" => ["db_col" => "privacy_name", "db_type" => "integer"]
        ,
        "UsrPrivacyComment" => ["db_col" => "usr_privacy_comment", "db_type" => "text"]
        ,
        "ShowStatements" => ["db_col" => "show_statements", "db_type" => "integer"]
        ,
        "XmlManifest" => ["db_col" => "xml_manifest", "db_type" => "text"]
        ,
        "Version" => ["db_col" => "version", "db_type" => "integer"]
        ,
        "HighscoreEnabled" => ["db_col" => "highscore_enabled", "db_type" => "integer"]
        ,
        "HighscoreAchievedTs" => ["db_col" => "highscore_achieved_ts", "db_type" => "integer"]
        ,
        "HighscorePercentage" => ["db_col" => "highscore_percentage", "db_type" => "integer"]
        ,
        "HighscoreWtime" => ["db_col" => "highscore_wtime", "db_type" => "integer"]
        ,
        "HighscoreOwnTable" => ["db_col" => "highscore_own_table", "db_type" => "integer"]
        ,
        "HighscoreTopTable" => ["db_col" => "highscore_top_table", "db_type" => "integer"]
        ,
        "HighscoreTopNum" => ["db_col" => "highscore_top_num", "db_type" => "integer"]
        ,
        "BypassProxy" => ["db_col" => "bypass_proxy", "db_type" => "integer"]
        ,
        "OnlyMoveon" => ["db_col" => "only_moveon", "db_type" => "integer"]
        ,
        "Achieved" => ["db_col" => "achieved", "db_type" => "integer"]
        ,
        "Answered" => ["db_col" => "answered", "db_type" => "integer"]
        ,
        "Completed" => ["db_col" => "completed", "db_type" => "integer"]
        ,
        "Failed" => ["db_col" => "failed", "db_type" => "integer"]
        ,
        "Initialized" => ["db_col" => "initialized", "db_type" => "integer"]
        ,
        "Passed" => ["db_col" => "passed", "db_type" => "integer"]
        ,
        "Progressed" => ["db_col" => "progressed", "db_type" => "integer"]
        ,
        "Satisfied" => ["db_col" => "satisfied", "db_type" => "integer"]
        ,
        "Terminated" => ["db_col" => "c_terminated", "db_type" => "integer"]
        ,
        "HideData" => ["db_col" => "hide_data", "db_type" => "integer"]
        ,
        "Timestamp" => ["db_col" => "c_timestamp", "db_type" => "integer"]
        ,
        "Duration" => ["db_col" => "duration", "db_type" => "integer"]
        ,
        "NoSubstatements" => ["db_col" => "no_substatements", "db_type" => "integer"]
        ,
        "PublisherId" => ["db_col" => "publisher_id", "db_type" => "text"]
        ,
        "AnonymousHomepage" => ["db_col" => "anonymous_homepage", "db_type" => "integer"]
        ,
        "MoveOn" => ["db_col" => "moveon", "db_type" => "text"]
        ,
        "LaunchParameters" => ["db_col" => "launch_parameters", "db_type" => "text"]
        ,
        "EntitlementKey" => ["db_col" => "entitlement_key", "db_type" => "text"]
        ,
        "SwitchToReview" => ["db_col" => "switch_to_review", "db_type" => "integer"]
    ];

    /**
     * @param int  $a_id
     * @param bool $a_reference
     */
    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        global $DIC;
        /** @var \ILIAS\DI\Container $DIC */

//        $this->_main_object_id = $a_id;
//        $this->_dataSetMapping = ilObjCmiXapi::getInstance($a_id, $a_reference)->getDataSetMapping();

        //var_dump($this->_dataSetMapping); exit;
        parent::__construct();

        foreach ($this->_cmixSettingsProperties as $key => $value) {
            $this->_element_db_mapping [$value["db_col"]] = $key;
        }
    }

    /**
     * Determine the dependent sets of data
     * @return mixed[]
     */
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ): array {
        return [];
    }
    public function getCmiXapiXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        array $a_ids,
        string $a_field = "",
        bool $a_omit_header = false,
        bool $a_omit_types = false
    ): string {
        global $DIC;
        /** @var \ILIAS\DI\Container $DIC */

        $GLOBALS["ilLog"]->write(json_encode($this->getTypes("cmix", "5.1.0"), JSON_PRETTY_PRINT));

        $this->dircnt = 1;

        $this->readData($a_entity, $a_schema_version, $a_ids);

        //var_dump($this->data); exit;
        $id = (int) $this->data["Id"];

        // prepare archive skeleton
        $objTypeAndId = "cmix_" . $id;
        $this->_archive['directories'] = [
            "exportDir" => ilExport::_getExportDirectory($id)
            ,
            "tempDir" => ilExport::_getExportDirectory($id) . "/temp"
            ,
            "archiveDir" => time() . "__" . IL_INST_ID . "__" . $objTypeAndId
            ,
            "moduleDir" => "cmix_" . $id
        ];

        $this->_archive['files'] = [
            "properties" => "properties.xml",
            "metadata" => "metadata.xml",
            "manifest" => 'manifest.xml',
        ];
        if (false !== strpos($this->data['SourceType'], 'local')) {
            $this->_archive['files']['content'] = "content.zip";
        }

        //var_dump([$this->_archive, $this->buildManifest()]); exit;

        // Prepare temp storage on the local filesystem
        if (!file_exists($this->_archive['directories']['exportDir'])) {
            mkdir($this->_archive['directories']['exportDir'], 0755, true);
            //$DIC->filesystem()->storage()->createDir($this->_archive['directories']['tempDir']);
        }
        if (!file_exists($this->_archive['directories']['tempDir'])) {
            mkdir($this->_archive['directories']['tempDir'], 0755, true);
            //$DIC->filesystem()->storage()->createDir($this->_archive['directories']['tempDir']);
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
        if (isset($this->_archive['files']['content'])) {
            $lmDir = ilFileUtils::getWebspaceDir("filesystem") . "/lm_data/lm_" . $id;
            ilFileUtils::zip(
                $lmDir,
                $this->_archive['directories']['tempDir'] . "/" . substr($this->_archive['files']['content'], 0, -4),
                true
            );
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
        if (isset($this->_archive['files']['content'])) {
            $zArchive->addFile(
                $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['content'],
                $this->_archive['directories']['archiveDir'] . '/content.zip'
            );
        }
        //var_dump($zArchive); exit;
        $zArchive->close();

        /*
        ilUtil::zip(
           $this->_archive['directories']['tempDir'],
           $this->_archive['directories']['archiveDir'] . ".zip"
        );
        */

        // unlink tempDir and its content
        unlink($this->_archive['directories']['tempDir'] . "/metadata.xml");
        unlink($this->_archive['directories']['tempDir'] . "/manifest.xml");
        unlink($this->_archive['directories']['tempDir'] . "/properties.xml");
        if (isset($this->_archive['files']['content'])) {
            unlink($this->_archive['directories']['tempDir'] . "/content.zip");
        }
        //unlink($this->_archive['directories']['tempDir']);
        //$DIC->filesystem()->storage()->readAndDelete($this->_archive['directories']['tempDir']);
        //$DIC->filesystem()->storage()->deleteDir($this->_archive['directories']['tempDir']);

        //var_dump($this->_archive); exit;

        return $fileName;
    }

    /**
     * Get field types for entity
     * @param string $a_entity  entity
     * @param string $a_version version number
     * @return array types array
     */
    protected function getTypes(string $a_entity, string $a_version): array
    {
        $types = [];
        foreach ($this->_cmixSettingsProperties as $key => $value) {
            $types[$key] = $value["db_type"];
        }
        //var_dump($types); exit;
        //return $types;

        if ($a_entity == "cmix") {
            switch ($a_version) {
                case "5.1.0":
                    $types = [];
                    foreach ($this->_cmixSettingsProperties as $key => $value) {
                        $types[$key] = $value["db_type"];
                    }
                    //return $types;
                    break;
            }
        }
        return $types;
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        global $DIC;
        /** @var $DIC \ILIAS\DI\Container */

        //$a_ids = [];
        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }

        //var_dump([$a_entity, $a_version, $a_id]); exit;
        if ($a_entity == "cmix") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT obj_id id, title, description " .
                        " FROM object_data " .
                        "WHERE " .
                        $DIC->database()->in("obj_id", $a_ids, false, "integer"));
                    break;
            } // EOF switch
        } // EOF if( $a_entity == "cmix" )

        foreach ($this->data as $key => $data) {
            $query = "SELECT " . implode(",", array_keys($this->_element_db_mapping)) . " ";
            $query .= "FROM `cmix_settings` ";
            $query .= "WHERE " . $DIC->database()->in("obj_id", $a_ids, false, "integer");
            $result = $DIC->database()->query($query);
            //$this->data = [];
            if ($dataset = $DIC->database()->fetchAssoc($result)) {
                $this->_data = $dataset;
            }

            //foreach( $this->_data AS $key => $data ) {
            foreach ($this->_data as $dbColName => $value) {
                $attr = $this->_element_db_mapping[$dbColName];
                $this->data[$key][$attr] = $value;
                //$this->data[$key][$dbColName] = $value;
            } // EOF foreach ($this->_dataSetMapping as $dbColName => $value)
        } // EOF foreach( $this->_data AS $key => $data )
        $this->data = $this->data[0];
        //var_dump($this->data); exit;
    }

    public function buildMetaData(int $id): string
    {
        $md2xml = new ilMD2XML($id, $id, "cmix");
        $md2xml->startExport();
        return $md2xml->getXML();
    }

    private function buildManifest(): string
    {
        $manWriter = new ilXmlWriter();
        $manWriter->xmlHeader();
        foreach ($this->_archive['files'] as $key => $value) {
            $manWriter->xmlElement($key, null, $value, true, true);
        }
        #$manWriter->appendXML ("</content>\n");
        return $manWriter->xmlDumpMem(true);
    }

    /**
     * @param mixed $a_entity
     */
    private function buildProperties($a_entity, bool $a_omit_header = false): string
    {
        $atts = array(
            "InstallationId" => IL_INST_ID,
            "InstallationUrl" => ILIAS_HTTP_PATH,
            "TopEntity" => $a_entity
        );

        $writer = new ilXmlWriter();

        $writer->xmlStartTag('DataSet', $atts);

        if (!$a_omit_header) {
            $writer->xmlHeader();
        }

        foreach ($this->data as $key => $value) {
            $writer->xmlElement($key, null, $value, true, true);
        }

        $writer->xmlEndTag("DataSet");

        return $writer->xmlDumpMem(true);
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        //var_dump( [$a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version] ); exit;
        switch ($a_entity) {
            case "cmix":

                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
                } else {
                    $newObj = new ilObjCmiXapi();
                    $newObj->setType("cmix");
                    $newObj->create(true);
                }

                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->update();

                //$this->current_obj = $newObj;
                $a_mapping->addMapping("Modules/CmiXapi", "cmix", $a_rec["Id"], (string) $newObj->getId());
                break;
        }
    }

    /**
     * Get supported versions
     * @return string[]
     */
    public function getSupportedVersions(): array
    {
        return array("5.1.0");
    }

    /**
     * Get xml namespace
     */
    public function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "http://www.ilias.de/xml/Modules/CmiXapi/" . $a_entity;
    }
}
