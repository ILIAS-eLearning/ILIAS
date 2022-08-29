<?php

declare(strict_types=1);

/* Copyright (c) 2021 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

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
