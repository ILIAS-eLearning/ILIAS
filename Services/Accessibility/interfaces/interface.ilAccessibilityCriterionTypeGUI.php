<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Interface ilAccessibilityCriterionTypeGUI
 */
interface ilAccessibilityCriterionTypeGUI
{
    /**
     * @param ilRadioGroupInputGUI            $option
     * @param ilAccessibilityCriterionConfig $config
     */
    public function appendOption(ilRadioGroupInputGUI $option, ilAccessibilityCriterionConfig $config) : void;

    /**
     * @param ilAccessibilityCriterionConfig $config
     *
     * @return ilSelectInputGUI
     */
    public function getSelection(ilAccessibilityCriterionConfig $config) : ilSelectInputGUI;

    /**
     * @param ilPropertyFormGUI $form
     * @return ilAccessibilityCriterionConfig
     */
    public function getConfigByForm(ilPropertyFormGUI $form) : ilAccessibilityCriterionConfig;

    /**
     * @return string
     */
    public function getIdentPresentation() : string;

    /**
     * @param ilAccessibilityCriterionConfig $config
     * @param Factory                         $uiFactory
     * @return Component
     */
    public function getValuePresentation(ilAccessibilityCriterionConfig $config, Factory $uiFactory) : Component;
}
