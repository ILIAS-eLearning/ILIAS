<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Style export definition
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilStyleExporter extends ilXmlExporter
{
    protected ilStyleDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilStyleDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }
    
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        if ($a_schema_version == "5.1.0") {
            ilFileUtils::makeDirParents($this->getAbsoluteExportDirectory());
            $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
            return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
        }
        if ($a_schema_version == "5.0.0") {
            if ($a_entity == "sty") {
                $style = new ilObjStyleSheet($a_id, false);

                // images
                $target = $this->getAbsoluteExportDirectory();
                if ($target && !is_dir($target)) {
                    ilFileUtils::makeDirParents($target);
                }
                ilFileUtils::rCopy($style->getImagesDirectory(), $target);

                return "<StyleSheetExport>" .
                    "<ImagePath>" . $this->getRelativeExportDirectory() . "</ImagePath>" .
                    $style->getXML() .
                    "</StyleSheetExport>";
            }
        }
    }

    public function getValidSchemaVersions(string $a_entity) : array
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
