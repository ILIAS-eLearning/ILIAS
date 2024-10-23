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

declare(strict_types=1);

class ilScormAiccExporter extends ilXmlExporter
{
    private ilScormAiccDataSet $dataset;

    public function __construct()
    {
        $this->dataset = new ilScormAiccDataSet();
    }

    public function init(): void
    {
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        $this->dataset->initByExporter($this);
        //using own getXmlRepresentation function in ilScormAiccDataSet
        return $this->dataset->getExtendedXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", false, true);
    }
    //todo:check if xsd files must be provided

    /**
     * @return array<string, array<string, string|bool>>
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
            "5.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/ScormAicc/sahs/5_1",
                "xsd_file" => "xml/ilias_sahs_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => "")
        );
    }

    //        public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    //        {
    //            $md_ids = array();
    //            $md_ids[0] = "0:".$mob_id.":mob";
    //
    //            return array (
    //                array(
    //                    "component" => "components/ILIAS/ScormAicc",
    //                    "entity" => "md",
    //                    "ids" => $md_ids)
    //                );
    //        }
}
