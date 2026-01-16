<?php

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
 * Style export definition
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilStyleExporter extends ilXmlExporter
{
    protected ilStyleDataSet $ds;

    public function init(): void
    {
        $this->ds = new ilStyleDataSet();
        $this->ds->initByExporter($this);
        $this->ds->setDSPrefix("ds");
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        ilFileUtils::makeDirParents($this->getAbsoluteExportDirectory());
        $this->ds->initByExporter($this);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
            "10.0" => array(
                "namespace" => "http://www.ilias.de/Services/Style/10_0",
                "xsd_file" => "ilias_style_10.xsd",
                "uses_dataset" => true,
                "min" => "10.0"),
            "8.0" => array(
                "namespace" => "http://www.ilias.de/Services/Style/8",
                "xsd_file" => "ilias_style_8.xsd",
                "uses_dataset" => true,
                "min" => "8.0",
                "max" => "9.99"),
            "5.1.0" => array(
                "namespace" => "http://www.ilias.de/Services/Style/5_1",
                "xsd_file" => "ilias_style_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => "7.99")
        );
    }
}
