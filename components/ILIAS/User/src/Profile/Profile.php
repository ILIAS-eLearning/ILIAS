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
     * @return array<\ILIAS\User\Profile\Fields\Field>
     */
    public function getFields(
        array $groups_to_skip = [],
        array $fields_to_skip = []
    ): array;

    /**
     * @return array<\ILIAS\User\Profile\Fields\Field>
     */
    public function getVisibleFields(
        Context $context,
        ?\ilObjUser $user = null,
        array $groups_to_skip = [],
        array $fields_to_skip = []
    ): array;

    public function getFieldByIdentifier(string $identifier): ?ProfileField;

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
     * @return \Generator<ILIAS\User\Profile\Data>
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
     * @return array<\ILIAS\User\Profile\Fields\Field>
     */
    public function getIgnorableRequiredFields(): array;

    /**
     * @deprecated since version 11 will be removed with 13
     * @return array<string, \ILIAS\User\Profile\Field>
     */
    public function getAllUserDefinedFields(): array;
    /**
     * @deprecated since version 11 will be removed with 13
     * @return array<string, \ILIAS\User\Profile\Field>
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
