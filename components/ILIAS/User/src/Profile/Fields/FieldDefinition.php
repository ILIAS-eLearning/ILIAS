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

namespace ILIAS\User\Profile\Fields;

use ILIAS\User\Property;
use ILIAS\User\Context;
use ILIAS\Language\Language;

interface FieldDefinition extends Property
{
    public function visibleInRegistrationForcedTo(): ?bool;
    public function visibleToUserForcedTo(): ?bool;
    public function visibleInLocalUserAdministrationForcedTo(): ?bool;
    public function visibleInCoursesForcedTo(): ?bool;
    public function visibleInGroupsForcedTo(): ?bool;
    public function visibleInStudyProgrammesForcedTo(): ?bool;
    public function changeableByUserForcedTo(): ?bool;
    public function changeableInLocalUserAdministrationForcedTo(): ?bool;
    public function requiredForcedTo(): ?bool;
    public function exportForcedTo(): ?bool;
    public function searchableForcedTo(): ?bool;
    public function availableInCertificatesForcedTo(): ?bool;
    public function hiddenInLists(): bool;

    /**
     * You don't need to add a post_var to the input as the User will handle this
     * for you, thus you can also not rely on the post_var anywhere else, as it
     * will be changed.
     */
    public function getLegacyInput(
        Language $lng,
        Context $context,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI;

    public function addValueToUserObject(
        \ilObjUser $user,
        mixed $input,
        \ilPropertyFormGUI $form
    ): \ilObjUser;
}
