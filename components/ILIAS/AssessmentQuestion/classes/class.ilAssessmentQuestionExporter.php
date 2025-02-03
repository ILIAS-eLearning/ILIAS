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
 * Class ilAssessmentQuestionExporter
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components/ILIAS/AssessmentQuestion
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
