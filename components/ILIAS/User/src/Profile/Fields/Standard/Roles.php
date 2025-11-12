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

declare(strict_types=1);

namespace ILIAS\User\Profile\Fields\Standard;

use ILIAS\User\Context;
use ILIAS\User\Profile\Fields\NoOverrides;
use ILIAS\User\Profile\Fields\FieldDefinition;
use ILIAS\User\Profile\Fields\AvailableSections;
use ILIAS\Language\Language;

class Roles implements FieldDefinition
{
    use NoOverrides;

    public function __construct(
        private readonly \ilObjectDataCache $object_cache
    ) {
    }

    public function getIdentifier(): string
    {
        return 'roles';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt($this->getIdentifier());
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Access;
    }

    public function hiddenInLists(): bool
    {
        return true;
    }

    public function visibleInRegistrationForcedTo(): ?bool
    {
        return false;
    }

    public function visibleInCoursesForcedTo(): ?bool
    {
        return false;
    }

    public function visibleInGroupsForcedTo(): ?bool
    {
        return false;
    }

    public function visibleInStudyProgrammesForcedTo(): ?bool
    {
        return false;
    }

    public function exportForcedTo(): ?bool
    {
        return false;
    }

    public function changeableByUserForcedTo(): ?bool
    {
        return false;
    }

    public function requiredForcedTo(): ?bool
    {
        return false;
    }

    public function searchableForcedTo(): ?bool
    {
        return false;
    }

    public function availableInCertificatesForcedTo(): ?bool
    {
        return false;
    }

    public function getLegacyInput(
        Language $lng,
        Context $context,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        if ($user !== null) {
            return $this->buildNonEditableInput($lng, $user);
        }
        return $this->buildMultiselect($lng);
    }

    public function addValueToUserObject(
        \ilObjUser $user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        if (!is_array($input)) {
            return $user;
        }

        $rbac_admin = new \ilRbacAdmin();
        foreach ($input as $role_id) {
            $rbac_admin->assignUser((int) $role_id, $user->getId());
        }

        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): string
    {
        $rbac_review = new \ilRbacReview();
        $assigned_roles = $rbac_review->assignedRoles($user->getId());
        return substr(
            array_reduce(
                $rbac_review->getGlobalRolesArray(),
                fn(string $c, array $v) => in_array($v['obj_id'], $assigned_roles)
                    ? $c . $this->object_cache->lookupTitle($v['obj_id']) . ', '
                    : $c,
                ''
            ),
            0,
            -2
        );
    }

    private function buildNonEditableInput(
        Language $lng,
        \ilObjUser $user
    ): \ilFormPropertyGUI {
        $input = new \ilNonEditableValueGUI($this->getLabel($lng));
        $input->setValue(
            $this->retrieveValueFromUser($user)
        );
        return $input;
    }

    private function buildMultiSelect(Language $lng): \ilFormPropertyGUI
    {
        $rbac_review = new \ilRbacReview();
        $input = new \ilMultiSelectInputGUI($this->getLabel($lng));
        $input->setOptions(
            array_reduce(
                $rbac_review->getGlobalRolesArray(),
                function (array $c, array $v): array {
                    if ($v['obj_id'] === ANONYMOUS_ROLE_ID) {
                        return $c;
                    }
                    $c[$v['obj_id']] = $this->object_cache->lookupTitle($v['obj_id']);
                    return $c;
                },
                []
            ),
        );
        $input->setRequired(true);

        return $input;
    }
}
