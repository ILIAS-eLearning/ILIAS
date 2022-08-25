<?php

declare(strict_types=1);

/* Copyright (c) 2021 - Denis Klöpfer <denis.kleofer@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Member related storage mechanism.
 */
interface ilIndividualAssessmentMembersStorage
{
    /**
     * Get ilIndividualAssessmentMembers-object containing member info
     * associated with $obj.
     */
    public function loadMembers(ilObjIndividualAssessment $obj): ilIndividualAssessmentMembers;

    /**
     * Get ilIndividualAssessmentMember-object for each obj member
     * associated with $obj.
     *
     * @return	ilIndividualAssessmentMember[]
     */
    public function loadMembersAsSingleObjects(
        ilObjIndividualAssessment $obj,
        string $filter = null,
        string $sort = null
    ): array;

    /**
     * Get ilIndividualAssessmentMember-object containing member info
     * associated with $obj and $usr.
     */
    public function loadMember(ilObjIndividualAssessment $obj, ilObjUser $usr): ilIndividualAssessmentMember;

    /**
     * Create a new storage entry for member-object.
     */
    public function updateMember(ilIndividualAssessmentMember $member): void;

    /**
     * Delete entries associated with members-object.
     */
    public function deleteMembers(ilObjIndividualAssessment $obj): void;

    /**
     * Create a membership inside storage.
     *
     * @param	string[]|int[]	$record
     */
    public function insertMembersRecord(ilObjIndividualAssessment $iass, array $record): void;

    /**
     * Remove a membership associated with a IndividualAssessment object
     * inside storage.
     *
     * @param	string[]|int[]	$record
     */
    public function removeMembersRecord(ilObjIndividualAssessment $iass, array $record): void;
}
