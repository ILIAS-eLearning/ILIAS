<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilCmiXapiDataSet
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiDataSet extends ilDataSet
{
    private $_data = [];
    public $data = [];
    private $_archive = [];
    private $_dataSetMapping = null;
    private $_main_object_id = null;
    private $_element_db_mapping = [];
    public $_cmixSettingsProperties = [
         "LrsTypeId" => ["db_col" => "lrs_type_id", "db_type" => "integer"]
        ,"ContentType" => ["db_col" => "content_type", "db_type" => "text"]
        ,"SourceType" => ["db_col" => "source_type", "db_type" => "text"]
        ,"ActivityId" => ["db_col" => "activity_id", "db_type" => "text"]
        ,"Instructions" => ["db_col" => "instructions", "db_type" => "text"]
        ,"OfflineStatus" => ["db_col" => "offline_status", "db_type" => "integer"]
        ,"LaunchUrl" => ["db_col" => "launch_url", "db_type" => "text"]
        ,"AuthFetchUrl" => ["db_col" => "auth_fetch_url", "db_type" => "integer"]
        ,"LaunchMethod" => ["db_col" => "launch_method", "db_type" => "text"]
        ,"LaunchMode" => ["db_col" => "launch_mode", "db_type" => "text"]
        ,"MasteryScore" => ["db_col" => "mastery_score", "db_type" => "float"]
        ,"KeepLp" => ["db_col" => "keep_lp", "db_type" => "integer"]
        ,"PrivacyIdent" => ["db_col" => "privacy_ident", "db_type" => "integer"]
        ,"PrivacyName" => ["db_col" => "privacy_name", "db_type" => "integer"]
        ,"UsrPrivacyComment" => ["db_col" => "usr_privacy_comment", "db_type" => "text"]
        ,"ShowStatements" => ["db_col" => "show_statements", "db_type" => "integer"]
        ,"XmlManifest" => ["db_col" => "xml_manifest", "db_type" => "text"]
        ,"Version" => ["db_col" => "version", "db_type" => "integer"]
        ,"HighscoreEnabled" => ["db_col" => "highscore_enabled", "db_type" => "integer"]
        ,"HighscoreAchievedTs" => ["db_col" => "highscore_achieved_ts", "db_type" => "integer"]
        ,"HighscorePercentage" => ["db_col" => "highscore_percentage", "db_type" => "integer"]
        ,"HighscoreWtime" => ["db_col" => "highscore_wtime", "db_type" => "integer"]
        ,"HighscoreOwnTable" => ["db_col" => "highscore_own_table", "db_type" => "integer"]
        ,"HighscoreTopTable" => ["db_col" => "highscore_top_table", "db_type" => "integer"]
        ,"HighscoreTopNum" => ["db_col" => "highscore_top_num", "db_type" => "integer"]
        ,"BypassProxy" => ["db_col" => "bypass_proxy", "db_type" => "integer"]
    ];




    public function __construct($a_id = 0, $a_reference = true)
    {
        global $DIC; /** @var \ILIAS\DI\Container $DIC */

        $this->_main_object_id = $a_id;
        $this->_dataSetMapping = ilObjCmiXapi::getInstance($a_id, $a_reference)->getDataSetMapping();

        //var_dump($this->_dataSetMapping); exit;
        parent::__construct();

        foreach ($this->_cmixSettingsProperties as $key => $value) {
            $this->_element_db_mapping [$value["db_col"]] = $key;
        }
    }


    /**
     * @param $a_entity
     * @param $a_version
     * @param $a_id
     */
    public function readData($a_entity, $a_version, $a_ids)
    {
        global $DIC; /** @var $DIC \ILIAS\DI\Container */

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
    } // EOf function readData



    /**
     * Get field types for entity
     *
     * @param string$a_entity entity
     * @param string$a_version version number
     * @return array types array
     */
    protected function getTypes($a_entity, $a_version)
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




    public function getCmiXapiXmlRepresentation($a_entity, $a_schema_version, $a_ids, $a_field = "", $a_omit_header = false, $a_omit_types = false)
    {
        global $DIC; /** @var \ILIAS\DI\Container $DIC */

        $GLOBALS["ilLog"]->write(json_encode($this->getTypes("cmix", "5.1.0"), JSON_PRETTY_PRINT));

        $this->dircnt = 1;

        $this->readData($a_entity, $a_schema_version, $a_ids);

        //var_dump($this->data); exit;
        $id = $this->data["Id"];

        // requirements
        require_once("./Services/Export/classes/class.ilExport.php");
        require_once("./Services/Xml/classes/class.ilXmlWriter.php");

        // prepare archive skeleton
        $objTypeAndId = "cmix_" . $id;
        $this->_archive['directories'] = [
            "exportDir" => ilExport::_getExportDirectory($id)
            ,"tempDir" => ilExport::_getExportDirectory($id) . "/temp"
            ,"archiveDir" => time() . "__" . IL_INST_ID . "__" . $objTypeAndId
            ,"moduleDir" => "cmix_" . $id
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
            $lmDir = ilUtil::getWebspaceDir("filesystem") . "/lm_data/lm_" . $id;
            ilUtil::zip($lmDir, $this->_archive['directories']['tempDir'] . "/" . substr($this->_archive['files']['content'], 0, -4), true);
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
     * @param $id
     * @return string
     */
    public function buildMetaData($id)
    {
        $md2xml = new ilMD2XML($id, $id, "cmix");
        $md2xml->startExport();
        return $md2xml->getXML();
    }

    /**
     * @return string
     */
    private function buildManifest()
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
     * @param $a_entity
     * @param bool $a_omit_header
     * @return string
     */
    private function buildProperties($a_entity, $a_omit_header = false)
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




















    /**
     * @param $a_entity
     * @param $a_types
     * @param $a_rec
     * @param $a_mapping
     * @param $a_schema_version
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
        //var_dump( [$a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version] ); exit;
        switch ($a_entity) {
            case "cmix":

                include_once("./Modules/CmiXapi/classes/class.ilObjCmiXapi.php");

                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjCmiXapi();
                    $newObj->setType("cmix");
                    $newObj->create(true);
                }

                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->update();


                //$this->current_obj = $newObj;
                $a_mapping->addMapping("Modules/CmiXapi", "cmix", $a_rec["Id"], $newObj->getId());
                break;
        }
    }
















    /**
     * Get supported versions
     *
     * @param
     * @return
     */
    public function getSupportedVersions()
    {
        return array("5.1.0");
    }

    /**
     * Get xml namespace
     *
     * @param
     * @return
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Modules/CmiXapi/" . $a_entity;
    }

    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        return false;
    }
}
