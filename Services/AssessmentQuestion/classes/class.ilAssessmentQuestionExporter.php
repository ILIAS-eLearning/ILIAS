<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssessmentQuestionExporter
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Services/AssessmentQuestion
 */
class ilAssessmentQuestionExporter extends ilXmlExporter
{
    public function getValidSchemaVersions(string $a_entity): array
    {
        /* export schema versions code */
    }

    public function init(): void
    {
        /* assessment question init code */
    }

    /**
     * @param string $a_entity
     * @param string $a_schema_version
     * @param string $a_id
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        /**
         * the assessment question export does simply get the id an returns
         * the qti xml representation of the question.
         */

        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $questionInstance = $DIC->question()->getQuestionInstance($a_id);

        return $questionInstance->toQtiXML();
    }
}
