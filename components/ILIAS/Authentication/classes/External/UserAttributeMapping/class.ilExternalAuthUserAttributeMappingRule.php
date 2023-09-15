<?php

declare(strict_types=1);

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

/**
 * Class ilExternalAuthUserAttributeMappingRule
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilExternalAuthUserAttributeMappingRule
{
    protected string $attribute = '';
    protected string $external_attribute = '';
    protected bool $update_automatically = false;

    public function getExternalAttribute(): string
    {
        return $this->external_attribute;
    }

    public function setExternalAttribute(string $external_attribute): void
    {
        $this->external_attribute = $external_attribute;
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }

    public function setAttribute(string $attribute): void
    {
        $this->attribute = $attribute;
    }

    public function isAutomaticallyUpdated(): bool
    {
        return $this->update_automatically;
    }

    public function updateAutomatically(bool $update_automatically): void
    {
        $this->update_automatically = $update_automatically;
    }
}
