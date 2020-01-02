<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Style Data set class
 *
 * This class implements the following entities:
 * - sty: table object_data
 * - sty_setting: table style_setting
 * - sty_char: table style classes
 * - sty_parameter: table style_parameter
 * - sty_color: table style colors
 * - sty_template: table style_template
 * - sty_template_class: table style_template_class
 * - sty_media_query: table sty_media_query
 * - sty_usage: table style_usage
 *
 * - object_style: this is a special entity which allows to export using the ID of the consuming object (e.g. wiki)
 *                 the "sty" entity will be detemined and exported afterwards (if a non global style has been assigned)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesStyle
 */
class ilStyleDataSet extends ilDataSet
{
    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * constructor
     *
     * @param
     * @return
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        parent::__construct();
        $this->log = ilLoggerFactory::getLogger('styl');
        $this->log->debug("constructed");
    }


    /**
     * Get supported versions
     *
     * @return string version
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
        return "http://www.ilias.de/xml/Services/Style/" . $a_entity;
    }

    /**
     * Get field types for entity
     *
     * @param string $a_entity entity
     * @param string $a_version version number
     * @return array types array
     */
    protected function getTypes($a_entity, $a_version)
    {
        if ($a_entity == "sty") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "ImagesDir" => "directory"
                    );
            }
        }

        if ($a_entity == "object_style") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "ObjectId" => "integer"
                    );
            }
        }

        if ($a_entity == "sty_setting") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "StyleId" => "integer",
                        "Name" => "test",
                        "Value" => "text"
                    );
            }
        }

        if ($a_entity == "sty_char") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "StyleId" => "integer",
                        "Type" => "text",
                        "Characteristic" => "text",
                        "Hide" => "integer"
                    );
            }
        }

        if ($a_entity == "sty_parameter") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "StyleId" => "integer",
                        "Tag" => "text",
                        "Class" => "text",
                        "Parameter" => "text",
                        "Value" => "text",
                        "Type" => "text",
                        "MqId" => "integer",
                        "Custom" => "integer"
                    );
            }
        }

        if ($a_entity == "sty_color") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "StyleId" => "integer",
                        "ColorName" => "text",
                        "ColorCode" => "text"
                    );
            }
        }

        if ($a_entity == "sty_media_query") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "StyleId" => "integer",
                        "OrderNr" => "integer",
                        "MQuery" => "text"
                    );
            }
        }

        if ($a_entity == "sty_template") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "StyleId" => "integer",
                        "Name" => "text",
                        "Preview" => "text",
                        "TempType" => "text"
                    );
            }
        }

        if ($a_entity == "sty_template_class") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "TemplateId" => "integer",
                        "ClassType" => "text",
                        "Class" => "text"
                    );
            }
        }

        if ($a_entity == "sty_usage") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "ObjId" => "integer",
                        "StyleId" => "integer"
                    );
            }
        }
    }

    /**
     * Get xml record
     *
     * @param
     * @return
     */
    public function getXmlRecord($a_entity, $a_version, $a_set)
    {
        if ($a_entity == "sty") {
            include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
            $dir = ilObjStyleSheet::_getImagesDirectory($a_set["Id"]);
            $a_set["ImagesDir"] = $dir;
        }

        return $a_set;
    }

    /**
     * Read data
     *
     * @param
     * @return
     */
    public function readData($a_entity, $a_version, $a_ids, $a_field = "")
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }

        if ($a_entity == "object_style") {
            switch ($a_version) {
                case "5.1.0":
                    foreach ($a_ids as $id) {
                        $this->data[] = array("ObjectId" => $id);
                    }
                    break;
            }
        }

        if ($a_entity == "sty") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT o.title, o.description, o.obj_id id" .
                        " FROM object_data o " .
                        " WHERE " . $ilDB->in("o.obj_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "sty_setting") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT style_id, name, value" .
                        " FROM style_setting " .
                        " WHERE " . $ilDB->in("style_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "sty_char") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT style_id, type, characteristic, hide" .
                        " FROM style_char " .
                        " WHERE " . $ilDB->in("style_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "sty_parameter") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT style_id, tag, class, parameter, value, type, mq_id, custom" .
                        " FROM style_parameter " .
                        " WHERE " . $ilDB->in("style_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "sty_color") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT style_id, color_name, color_code" .
                        " FROM style_color " .
                        " WHERE " . $ilDB->in("style_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "sty_media_query") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT id, style_id, order_nr, mquery m_query" .
                        " FROM sty_media_query " .
                        " WHERE " . $ilDB->in("style_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "sty_template") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT id, style_id, name, preview, temp_type" .
                        " FROM style_template " .
                        " WHERE " . $ilDB->in("style_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "sty_template_class") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT template_id, class_type, class" .
                        " FROM style_template_class " .
                        " WHERE " . $ilDB->in("template_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "sty_usage") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT obj_id, style_id" .
                        " FROM style_usage " .
                        " WHERE " . $ilDB->in("style_id", $a_ids, false, "integer"));
                    break;
            }
        }
    }

    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        $this->ds_log->debug("entity: " . $a_entity . ", rec: " . print_r($a_rec, true));
        switch ($a_entity) {
            case "object_style":
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                $this->ds_log->debug("object id: " . $a_rec["ObjectId"]);
                $style_id = ilObjStyleSheet::lookupObjectStyle($a_rec["ObjectId"]);
                $this->ds_log->debug("style id: " . $style_id);
                //if ($style_id > 0 && !ilObjStyleSheet::_lookupStandard($style_id))
                if ($style_id > 0 && ilObject::_lookupType($style_id) == "sty") {			// #0019337 always export style, if valid
                    return array(
                        "sty" => array("ids" => $style_id));
                }
                return array();
                break;

            case "sty":
                return array(
                    "sty_setting" => array("ids" => $a_rec["Id"]),
                    "sty_media_query" => array("ids" => $a_rec["Id"]),
                    "sty_char" => array("ids" => $a_rec["Id"]),
                    "sty_color" => array("ids" => $a_rec["Id"]),
                    "sty_parameter" => array("ids" => $a_rec["Id"]),
                    "sty_template" => array("ids" => $a_rec["Id"]),
                    "sty_usage" => array("ids" => $a_rec["Id"])
                );

            case "sty_template":
                return array(
                    "sty_template_class" => array("ids" => $a_rec["Id"])
                );
        }

        return false;
    }


    /**
     * Import record
     *
     * @param
     * @return
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
        switch ($a_entity) {
            case "sty":
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                $this->log->debug("Entity: " . $a_entity);
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjStyleSheet();
                    $newObj->create(0, true);
                }

                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->update(true);

                $this->current_obj = $newObj;
                $a_mapping->addMapping("Services/Style", "sty", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping("Services/Object", "obj", $a_rec["Id"], $newObj->getId());
                $this->log->debug("Added mapping Services/Style sty  " . $a_rec["Id"] . " > " . $newObj->getId());

                $dir = str_replace("..", "", $a_rec["ImagesDir"]);
                if ($dir != "" && $this->getImportDirectory() != "") {
                    $source_dir = $this->getImportDirectory() . "/" . $dir;
                    $target_dir = $dir = ilObjStyleSheet::_getImagesDirectory($newObj->getId());
                    ilUtil::rCopy($source_dir, $target_dir);
                }
                break;

            case "sty_setting":
                $this->current_obj->writeStyleSetting($a_rec["Name"], $a_rec["Value"]);
                break;

            case "sty_char":
                $this->current_obj->addCharacteristic($a_rec["Type"], $a_rec["Characteristic"], $a_rec["Hide"]);
                break;

            case "sty_parameter":
                $mq_id = (int) $a_mapping->getMapping("Services/Style", "media_query", $a_rec["MqId"]);
                $this->current_obj->replaceStylePar($a_rec["Tag"], $a_rec["Class"], $a_rec["Parameter"], $a_rec["Value"], $a_rec["Type"], $mq_id, $a_rec["Custom"]);
                break;

            case "sty_color":
                $this->current_obj->addColor($a_rec["ColorName"], $a_rec["ColorCode"]);
                break;

            case "sty_media_query":
                $mq_id = $this->current_obj->addMediaQuery($a_rec["MQuery"], $a_rec["OrderNr"]);
                $a_mapping->addMapping("Services/Style", "media_query", $a_rec["Id"], $mq_id);
                break;

            case "sty_template":
                $tid = $this->current_obj->addTemplate($a_rec["TempType"], $a_rec["Name"], array());
                $a_mapping->addMapping("Services/Style", "template", $a_rec["Id"], $tid);
                break;

            case "sty_template_class":
                $tid = (int) $a_mapping->getMapping("Services/Style", "template", $a_rec["TemplateId"]);
                $this->current_obj->addTemplateClass($tid, $a_rec["ClassType"], $a_rec["Class"]);
                break;

            case "sty_usage":
                $obj_id = (int) $a_mapping->getMapping("Services/Object", "obj", $a_rec["ObjId"]);
                $style_id = (int) $a_mapping->getMapping("Services/Style", "sty", $a_rec["StyleId"]);
                if ($obj_id > 0 && $style_id > 0) {
                    include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                    ilObjStyleSheet::writeStyleUsage($obj_id, $style_id);
                }
                break;
        }
    }
}
