<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Export/classes/class.ilXmlExporter.php';

/**
 * Style export definition
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesStyle
 */
class ilStyleExporter extends ilXmlExporter
{
    public function init()
    {
        include_once("./Services/Style/classes/class.ilStyleDataSet.php");
        $this->ds = new ilStyleDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }
    
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        if ($a_schema_version == "5.1.0") {
            ilUtil::makeDirParents($this->getAbsoluteExportDirectory());
            $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
            return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, "", true, true);
        }
        if ($a_schema_version == "5.0.0") {
            if ($a_entity == "sty") {
                include_once "Services/Style/Content/classes/class.ilObjStyleSheet.php";
                $style = new ilObjStyleSheet($a_id, false);

                // images
                $target = $this->getAbsoluteExportDirectory();
                if ($target && !is_dir($target)) {
                    ilUtil::makeDirParents($target);
                }
                ilUtil::rCopy($style->getImagesDirectory(), $target);

                return "<StyleSheetExport>" .
                    "<ImagePath>" . $this->getRelativeExportDirectory() . "</ImagePath>" .
                    $style->getXML() .
                    "</StyleSheetExport>";
            }
        }
    }

    public function getValidSchemaVersions($a_entity)
    {
        return array(
            "5.1.0" => array(
                "namespace" => "http://www.ilias.de/Services/Style/5_1",
                "xsd_file" => "ilias_style_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => ""),
            "5.0.0" => array(
                "namespace" => "http://www.ilias.de/Services/Style/5_0",
                "xsd_file" => "ilias_style_5_0.xsd",
                "uses_dataset" => false,
                "min" => "5.0.0",
                "max" => "")
        );
    }
}
