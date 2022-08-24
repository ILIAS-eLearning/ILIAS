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

namespace ILIAS\Services\User;

/**
 * Class ChangedUserFieldAttribute
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ChangedUserFieldAttribute
{
    private string $attributeName;
    private string $oldValue;
    private string $newValue;

    public function __construct(string $attributeName, string $oldValue, string $newValue)
    {
        $this->attributeName = $attributeName;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    public function getOldValue(): string
    {
        return $this->oldValue;
    }

    public function getNewValue(): string
    {
        return $this->newValue;
    }
}
