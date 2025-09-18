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

    public function visibleInCoursesForcedTo(): ?bool
    {
        return true;
    }

    public function visibleInGroupsForcedTo(): ?bool
    {
        return true;
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
        $input = new \ilNonEditableValueGUI($this->getLabel($lng));
        if ($user === null) {
            return $input;
        }
        $input->setValue(
            $this->retrieveValueFromUser($user)
        );
        return $input;
    }

    public function addValueToUserObject(
        \ilObjUser $user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): string
    {
        $rbac_review = new \ilRbacReview();
        $assinged_roles = $rbac_review->assignedRoles($user->getId());
        return substr(
            array_reduce(
                $rbac_review->getGlobalRolesArray(),
                static fn(string $c, array $v) => in_array($v['obj_id'], $assinged_roles)
                    ? $c . \ilObjectFactory::getInstanceByObjId($v['obj_id'])->getTitle() . ', '
                    : $c,
                ''
            ),
            0,
            -2
        );
    }
}
