<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Importer class for calendar data
 * @author  Alex Killing <alex.killing@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilCalendarImporter extends ilXmlImporter
{
    protected ilCalendarDataSet $ds;

    /**
     * @inheritDoc
     */
    public function init() : void
    {
        $this->ds = new ilCalendarDataSet();
        $this->ds->setDSPrefix("ds");
    }

    /**
     * @inheritDoc
     */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
}
