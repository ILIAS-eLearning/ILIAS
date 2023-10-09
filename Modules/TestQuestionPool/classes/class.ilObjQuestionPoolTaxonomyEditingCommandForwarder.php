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

/**
 * class can be used as forwarder for taxonomy editing context
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/TestQuestionPool
 */
class ilObjQuestionPoolTaxonomyEditingCommandForwarder
{
    protected Service $taxonomy;
    protected ilObjQuestionPool $poolOBJ;
    protected ilDBInterface $db;
    protected ilComponentRepository $component_repository;
    protected ilCtrlInterface $ctrl;
    protected ilTabsGUI $tabs ;
    protected ilLanguage $lng;

    public function __construct(
        ilObjQuestionPool $poolOBJ,
        ilDBInterface $db,
        ilComponentRepository $component_repository,
        ilCtrl $ctrl,
        ilTabsGUI $tabs,
        ilLanguage $lng,
        Service $taxonomy
    ) {
        $this->poolOBJ = $poolOBJ;
        $this->db = $db;
        $this->component_repository = $component_repository;
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->lng = $lng;
        $this->taxonomy = $taxonomy;
    }

    public function forward(): void
    {
        $this->tabs->setTabActive('settings');
        $this->lng->loadLanguageModule('tax');

        $questionList = new ilAssQuestionList($this->db, $this->lng, $this->component_repository);

        $questionList->setParentObjId($this->poolOBJ->getId());

        $questionList->load();

        $tax_gui = $this->taxonomy->gui()->getSettingsGUI(
            $this->poolOBJ->getId(),
            "",
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
