<?php
declare(strict_types=1);

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssessmentQuestionImporter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAssessmentQuestionImporter extends ilXmlImporter
{

    /**
     * @param string $a_entity
     * @param int    $a_id
     * @param string $a_xml
     * @param array  $a_mapping
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        // TODO:
        // - parse the given xml to ilQtiItems
        // - get parent container corresponding authoring service from DIC
        // - import the ilQtiItems
    }
}