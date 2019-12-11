<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Interface ilTermsOfServiceCriterionTypeGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceCriterionTypeGUI
{
    /**
     * @param ilRadioGroupInputGUI $option
     * @param \ilTermsOfServiceCriterionConfig $config
     */
    public function appendOption(\ilRadioGroupInputGUI $option, \ilTermsOfServiceCriterionConfig $config);

    /**
     * @param ilPropertyFormGUI $form
     * @return \ilTermsOfServiceCriterionConfig
     */
    public function getConfigByForm(\ilPropertyFormGUI $form) : \ilTermsOfServiceCriterionConfig;

    /**
     * @return string
     */
    public function getIdentPresentation() : string;

    /**
     * @param \ilTermsOfServiceCriterionConfig $config
     * @param Factory $uiFactory
     * @return Component
     */
    public function getValuePresentation(\ilTermsOfServiceCriterionConfig $config, Factory $uiFactory) : Component;
}
