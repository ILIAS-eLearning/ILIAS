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
 * Manual Assessment exporter class
 */
class ilIndividualAssessmentExporter extends ilXmlExporter
{
    protected ilIndividualAssessmentDataSet $ds;

    /**
     * initialize the exporter
     */
    public function init(): void
    {
        $this->ds = new ilIndividualAssessmentDataSet();
    }

    /**
     * @inheritdoc
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        ilFileUtils::makeDirParents($this->getAbsoluteExportDirectory());
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);

        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], '', true, true);
    }

    /**
     * @inheritdoc
     */
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        $res = [];

        if ($a_entity == "iass") {
            // service settings
            $res[] = [
                "component" => "Services/Object",
                "entity" => "common",
                "ids" => $a_ids
            ];
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return [
            "5.2.0" => [
                "namespace" => "http://www.ilias.de/Services/User/iass/5_2",
                "xsd_file" => "ilias_iass_5_2.xsd",
                "uses_dataset" => true,
                "min" => "5.2.0",
                "max" => "5.2.99"
            ],
            "5.3.0" => [
                "namespace" => "http://www.ilias.de/Services/User/iass/5_3",
                "xsd_file" => "ilias_iass_5_3.xsd",
                "uses_dataset" => true,
                "min" => "5.3.0",
                "max" => ""
            ]
        ];
    }
}
