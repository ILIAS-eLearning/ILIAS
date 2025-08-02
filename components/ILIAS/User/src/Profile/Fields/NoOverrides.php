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

namespace ILIAS\User\Profile\Fields;

trait NoOverrides
{
    public function hiddenInLists(): bool
    {
        return null;
    }

    public function visibleToUserForcedTo(): ?bool
    {
        return null;
    }

    public function visibleInLocalUserAdministrationForcedTo(): ?bool
    {
        return null;
    }

    public function visibleInCoursesForcedTo(): ?bool
    {
        return null;
    }

    public function visibleInGroupsForcedTo(): ?bool
    {
        return null;
    }

    public function visibleInStudyProgrammesForcedTo(): ?bool
    {
        return null;
    }

    public function changeableByUserForcedTo(): ?bool
    {
        return null;
    }

    public function changeableInLocalUserAdministrationForcedTo(): ?bool
    {
        return null;
    }

    public function requiredForcedTo(): ?bool
    {
        return null;
    }

    public function exportForcedTo(): ?bool
    {
        return null;
    }

    public function searchableForcedTo(): ?bool
    {
        return null;
    }

    public function availableInCertificatesForcedTo(): ?bool
    {
        return null;
    }
}
