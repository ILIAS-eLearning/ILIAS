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

/**
 * Class ChangedUserFieldAttribute
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ChangedUserFieldAttribute
{
    public function __construct(
        private readonly PropertyAttributes $attribute_name,
        private readonly bool $old_value,
        private readonly bool $new_value
    ) {
    }

    public function getAttribute(): PropertyAttributes
    {
        return $this->attribute_name;
    }

    public function getOldValue(): bool
    {
        return $this->old_value;
    }

    public function getNewValue(): bool
    {
        return $this->new_value;
    }
}
