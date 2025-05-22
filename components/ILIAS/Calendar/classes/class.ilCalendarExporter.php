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
/**
 * Exporter class for calendar data (xml)
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarExporter extends ilXmlExporter
{
    private ilCalendarDataSet $ds;

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        $this->ds = new ilCalendarDataSet();
        $this->ds->initByExporter($this);
        $this->ds->setDSPrefix("ds");
    }

    /**
     * @inheritDoc
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        $this->ds->initByExporter($this);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    /**
     * @inheritDoc
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
            "4.3.0" => array(
                "namespace" => "http://www.ilias.de/Services/Calendar/cal/4_3",
                "xsd_file" => "ilias_cal_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => ""
            )
        );
    }
}
