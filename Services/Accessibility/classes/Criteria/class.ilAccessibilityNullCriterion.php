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

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilAccessibilityNullCriterion
 */
class ilAccessibilityNullCriterion implements ilAccessibilityCriterionType
{
    public function getTypeIdent(): string
    {
        return 'null';
    }

    public function evaluate(ilObjUser $user, ilAccessibilityCriterionConfig $config): bool
    {
        return true;
    }

    public function hasUniqueNature(): bool
    {
        return false;
    }

    public function ui(ilLanguage $lng): ilAccessibilityCriterionTypeGUI
    {
        return new class ($lng) implements ilAccessibilityCriterionTypeGUI {
            protected ilLanguage $lng;

            public function __construct(ilLanguage $lng)
            {
                $this->lng = $lng;
            }

            public function appendOption(ilRadioGroupInputGUI $option, ilAccessibilityCriterionConfig $config): void
            {
            }

            public function getConfigByForm(ilPropertyFormGUI $form): ilAccessibilityCriterionConfig
            {
                return new ilAccessibilityCriterionConfig();
            }

            public function getIdentPresentation(): string
            {
                return $this->lng->txt('deleted');
            }

            public function getValuePresentation(
                ilAccessibilityCriterionConfig $config,
                Factory $uiFactory
            ): Component {
                return $uiFactory->legacy('-');
            }

            public function getSelection(ilAccessibilityCriterionConfig $config): ilSelectInputGUI
            {
                return new ilSelectInputGUI("", "");
            }
        };
    }
}
