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

namespace ILIAS\User\Profile\Fields\Custom;

use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Factory as Refinery;

class Text implements Type
{
    public function getAdditionalEditFormInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery
    ): ?FormInput {
        return null;
    }

    public function storeAdditionalEditFormInputs(mixed $value): void
    {
        throw new \Exception('We cannot store anything here');
    }

    public function getInput(
        Language $lng,
        \ilObjUser $current_user
    ): \ilFormPropertyGUI {
        $input = new \ilTextInputGUI($lng->txt($this->getLanguageVariable()));
        $input->setValue(
            $this->getValueForUser($current_user)
        );
        return $input;
    }

    public function storeUserInput(
        \ilObjUser $current_user,
        mixed $input
    ): void {
        ;
    }

    public function getValueForUser(\ilObjUser $current_user): string
    {
        ;
    }
}
