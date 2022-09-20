<?php

declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

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
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    /**
     * @inheritDoc
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
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
