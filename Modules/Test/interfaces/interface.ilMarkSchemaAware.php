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
 * Interface ilMarkSchemaAware
 * @author Michael Jansen <mjansen@databay.de>
 * @package ModulesTest
 */
interface ilMarkSchemaAware
{
    /**
     * @return ASS_MarkSchema
     */
    public function getMarkSchema(): ASS_MarkSchema;

    /**
     * @return boolean|string True or an error string which can be used for display purposes
     */
    public function checkMarks();

    /**
     * @return boolean
     */
    public function canEditMarks(): bool;

    /**
     * @return int
     */
    public function getMarkSchemaForeignId(): int;

    /**
     *
     */
    public function onMarkSchemaSaved();
}
