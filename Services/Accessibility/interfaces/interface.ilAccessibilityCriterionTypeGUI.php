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
