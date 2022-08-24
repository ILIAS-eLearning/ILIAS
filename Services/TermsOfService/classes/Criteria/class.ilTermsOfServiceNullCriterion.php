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

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilTermsOfServiceNullCriterion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceNullCriterion implements ilTermsOfServiceCriterionType
{
    public function getTypeIdent(): string
    {
        return 'null';
    }

    public function evaluate(ilObjUser $user, ilTermsOfServiceCriterionConfig $config): bool
    {
        return true;
    }

    public function hasUniqueNature(): bool
    {
        return false;
    }

    public function ui(ilLanguage $lng): ilTermsOfServiceCriterionTypeGUI
    {
        return new class ($lng) implements ilTermsOfServiceCriterionTypeGUI {
            protected ilLanguage $lng;

            public function __construct(ilLanguage $lng)
            {
                $this->lng = $lng;
            }

            public function appendOption(ilRadioGroupInputGUI $group, ilTermsOfServiceCriterionConfig $config): void
            {
            }

            public function getConfigByForm(ilPropertyFormGUI $form): ilTermsOfServiceCriterionConfig
            {
                return new ilTermsOfServiceCriterionConfig();
            }

            public function getIdentPresentation(): string
            {
                return $this->lng->txt('deleted');
            }

            public function getValuePresentation(
                ilTermsOfServiceCriterionConfig $config,
                Factory $uiFactory
            ): Component {
                return $uiFactory->legacy('-');
            }
        };
    }
}
