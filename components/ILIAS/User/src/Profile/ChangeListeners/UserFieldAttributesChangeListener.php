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

namespace ILIAS\User\Profile\ChangeListeners;

use ILIAS\User\PropertyAttributes;
use ILIAS\Language\Language;

interface UserFieldAttributesChangeListener
{
    /**
     * MUST return the fully qualified class name of the profile field the
     * component is interested in.
     */
    public function isInterestedInField(): string;
    public function isInterestedInAttribute(): PropertyAttributes;

    /**
     * MUST return a description for a user profile field.
     */
    public function getDescriptionForField(
        Language $lng,
        string $translated_field_name,
        string $translated_attribute_name
    ): string;

    /**
     * MUST return the component name like it would be used to raise an event
     * @example "components/ILIAS/Mail"
     */
    public function getComponentName(): string;
}
