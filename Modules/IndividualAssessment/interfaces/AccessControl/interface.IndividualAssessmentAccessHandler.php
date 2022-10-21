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
 * Mechanic regarding the access control and roles of an objet goes here.
 */
interface IndividualAssessmentAccessHandler
{
    /**
     * Can a user perform an operation on some Individual assessment?
     */
    public function checkAccessToObj(string $operation): bool;

    /**
     * Create default roles at an object
     */
    public function initDefaultRolesForObject(ilObjIndividualAssessment $iass): void;

    /**
     * Assign a user to the member role at an Individual assessment
     */
    public function assignUserToMemberRole(ilObjUser $usr, ilObjIndividualAssessment $iass): bool;

    /**
     * Deasign a user from the member role at an Individual assessment
     */
    public function deassignUserFromMemberRole(ilObjUser $usr, ilObjIndividualAssessment $iass): bool;

    /**
     * Check whether user is system admin.
     */
    public function isSystemAdmin(): bool;
}
