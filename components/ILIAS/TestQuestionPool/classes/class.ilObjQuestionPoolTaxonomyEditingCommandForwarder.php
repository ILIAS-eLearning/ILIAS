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

use ILIAS\Taxonomy\Service;
use ILIAS\Refinery\Factory as Refinery;

/**
 * class can be used as forwarder for taxonomy editing context
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/TestQuestionPool
 */
class ilObjQuestionPoolTaxonomyEditingCommandForwarder
{
    public function __construct(
        private readonly ilObjQuestionPool $poolOBJ,
        private readonly ilDBInterface $db,
        private readonly Refinery $refinery,
        private readonly ilComponentRepository $component_repository,
        private readonly ilComponentFactory $component_factory,
        private readonly ilCtrl $ctrl,
        private readonly ilTabsGUI $tabs,
        private readonly ilLanguage $lng,
        private readonly Service $taxonomy
    ) {
    }

    public function forward(): void
    {
        $this->tabs->setTabActive('settings');
        $this->lng->loadLanguageModule('tax');

        $questionList = new ilAssQuestionList(
            $this->db,
            $this->lng,
            $this->refinery,
            $this->component_repository,
            $this->component_factory
        );

        $questionList->setParentObjId($this->poolOBJ->getId());

        $questionList->load();

        $tax_gui = $this->taxonomy->gui()->getSettingsGUI(
            $this->poolOBJ->getId(),
            $this->lng->txt('qpl_taxonomy_tab_info_message'),
            true
        )->withAssignedItemSorting(
            $questionList,
            'qpl',
            $this->poolOBJ->getId(),
            'quest'
        );

        $this->ctrl->forwardCommand($tax_gui);
    }
}
