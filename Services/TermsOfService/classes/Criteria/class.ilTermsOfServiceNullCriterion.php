<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilTermsOfServiceNullCriterion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceNullCriterion implements ilTermsOfServiceCriterionType
{
    public function getTypeIdent() : string
    {
        return 'null';
    }

    public function evaluate(ilObjUser $user, ilTermsOfServiceCriterionConfig $config) : bool
    {
        return true;
    }

    public function hasUniqueNature() : bool
    {
        return false;
    }

    public function ui(ilLanguage $lng) : ilTermsOfServiceCriterionTypeGUI
    {
        return new class($lng) implements ilTermsOfServiceCriterionTypeGUI {
            protected ilLanguage $lng;

            public function __construct(ilLanguage $lng)
            {
                $this->lng = $lng;
            }
            // PHP8-Review: Parameter's name changed during inheritance from 'option' to 'group'
            public function appendOption(ilRadioGroupInputGUI $group, ilTermsOfServiceCriterionConfig $config) : void
            {
            }

            public function getConfigByForm(ilPropertyFormGUI $form) : ilTermsOfServiceCriterionConfig
            {
                return new ilTermsOfServiceCriterionConfig();
            }

            public function getIdentPresentation() : string
            {
                return $this->lng->txt('deleted');
            }

            public function getValuePresentation(
                ilTermsOfServiceCriterionConfig $config,
                Factory $uiFactory
            ) : Component {
                return $uiFactory->legacy('-');
            }
        };
    }
}
