<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Interface ilTermsOfServiceCriterionTypeGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceCriterionTypeGUI
{
    public function appendOption(ilRadioGroupInputGUI $group, ilTermsOfServiceCriterionConfig $config) : void;

    public function getConfigByForm(ilPropertyFormGUI $form) : ilTermsOfServiceCriterionConfig;

    public function getIdentPresentation() : string;

    public function getValuePresentation(ilTermsOfServiceCriterionConfig $config, Factory $uiFactory) : Component;
}
