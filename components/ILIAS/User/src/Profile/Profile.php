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

namespace ILIAS\User\Profile;

use ILIAS\User\Context;
use ILIAS\User\Profile\Fields\Field as ProfileField;

interface Profile
{
    /**
     * @param list<\ILIAS\User\Profile\Fields\AvailableSections> $fields_to_skip
     * @param list<class-string<\ILIAS\User\Profile\Fields\FieldDefinition>> $fields_to_skip
     * @return array<string, ProfileField>
     */
    public function getFields(
        array $sections_to_skip = [],
        array $fields_to_skip = []
    ): array;

    /**
     * @param list<\ILIAS\User\Profile\Fields\AvailableSections> $fields_to_skip
     * @param list<class-string<\ILIAS\User\Profile\Fields\FieldDefinition>> $fields_to_skip
     * @return array<int, ProfileField>
     */
    public function getVisibleFields(
        Context $context,
        ?\ilObjUser $user = null,
        array $sections_to_skip = [],
        array $fields_to_skip = []
    ): array;

    public function getFieldByIdentifier(string $identifier): ?ProfileField;

    /**
     * @param list<class-string<\ILIAS\User\Profile\Fields\FieldDefinition>> $fields_to_skip
     */
    public function addFieldsToForm(
        \ilPropertyFormGUI $form,
        Context $context,
        bool $do_require,
        ?\ilObjUser $current_user,
        array $fields_to_skip = []
    ): \ilPropertyFormGUI;

    public function addFormValuesToUser(
        \ilPropertyFormGUI $form,
        Context $context,
        \ilObjUser $current_user
    ): \ilObjUser;

    public function getDataFor(
        int $usr_id
    ): Data;

    /**
     *
     * @param array $usr_ids
     * @return \Generator<int, Data>
     */
    public function getDataForMultiple(
        array $usr_ids
    ): \Generator;

    public function isProfileIncomplete(\ilObjUser $user): bool;

    public function userFieldVisibleToUser(
        string $definition_class
    ): bool;

    public function userFieldEditableByUser(string $definition_class): bool;

    /**
     * @return list<ProfileField>
     */
    public function getIgnorableRequiredFields(): array;

    /**
     * @deprecated since version 11 will be removed with 13
     * @return array<string, ProfileField>
     */
    public function getAllUserDefinedFields(): array;
    /**
     * @deprecated since version 11 will be removed with 13
     * @return array<string, ProfileField>
     */
    public function getVisibleUserDefinedFields(
        Context $context
    ): array;

    /**
     * @deprecated since version 11 will be removed asap
     */
    public function tempStorePicture(
        \ilPropertyFormGUI $form
    ): \ilPropertyFormGUI;
}
