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

use ILIAS\DI\Container;
use ilLanguage;

abstract class UserFieldAttributesChangeListener
{
    protected ilLanguage $lng;
    protected Container $dic;

    public function __construct(Container $dic)
    {
        $this->dic = $dic;
        $this->lng = $dic->language();
    }

    /**
     * Should return a description for a user profile field if the listener is interested in a change of a field attribute.
     * Returning null or an empty string will skip the listener.
     * @param string $fieldName
     * @param string $attribute
     * @return string|null
     */
    abstract public function getDescriptionForField(string $fieldName, string $attribute): ?string;

    /**
     * Should return the component name like it would be used to raise an event
     * @return string
     * @example "components/ILIAS/Mail"
     */
    abstract public function getComponentName(): string;
}
