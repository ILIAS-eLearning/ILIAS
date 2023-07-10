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


    public function mayReadObject(): bool;
    public function mayEditObject(): bool;
    public function mayEditPermissions(): bool;
    public function mayEditMembers(): bool;
    public function mayViewAnyUser(): bool;
    public function mayViewAllUsers(): bool;
    public function mayGradeAnyUser(): bool;
    public function mayGradeAllUsers(): bool;
    public function mayGradeUser(int $user_id): bool;
    public function mayViewUser(int $user_id): bool;
    public function mayAmendAllUsers(): bool;
    public function isSystemAdmin(): bool;
}
