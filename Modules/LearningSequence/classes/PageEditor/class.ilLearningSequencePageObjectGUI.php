<?php declare(strict_types=1);

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

/**
 * There are two pages for Learning Sequences: intro and extro.
 * On both pages, the same objects should be available.
 */
abstract class ilLearningSequencePageObjectGUI extends ilPageObjectGUI
{
    public function getTabs($a_activate = "") : void
    {
        $this->tabs_gui->activateTab(ilObjLearningSequenceGUI::TAB_CONTENT_MAIN);
    }

    public function getPageConfig() : ilPageConfig
    {
        $this->page_config->setEnablePCType(ilPCCurriculum::PCELEMENT, true);
        $this->page_config->setEnablePCType(ilPCLauncher::PCELEMENT, true);
        return $this->page_config;
    }
}
