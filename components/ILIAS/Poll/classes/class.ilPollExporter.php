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
 ********************************************************************
 */

/**
 * Poll export definition
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPollExporter extends ilXmlExporter
{
    protected ilPollDataSet $ds;

    public function init(): void
    {
        $this->ds = new ilPollDataSet();
        $this->ds->setDSPrefix("ds");
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
                "4.3.0" => array(
                    "namespace" => "http://www.ilias.de/Services/Modules/Poll/4_3",
                    "xsd_file" => "ilias_poll_4_3.xsd",
                    "uses_dataset" => true,
                    "min" => "4.3.0",
                    "max" => "4.4.99"),
                "5.0.0" => array(
                    "namespace" => "http://www.ilias.de/Services/Modules/Poll/5_0",
                    "xsd_file" => "ilias_poll_5_0.xsd",
                    "uses_dataset" => true,
                    "min" => "5.0.0",
                    "max" => "")
        );
    }
}
