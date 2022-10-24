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

/**
 * class can be used as forwarder for taxonomy editing context
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/TestQuestionPool
 */
class ilObjQuestionPoolTaxonomyEditingCommandForwarder
{
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
        ilLanguage $lng
    ) {
        $this->poolOBJ = $poolOBJ;
        $this->db = $db;
        $this->component_repository = $component_repository;
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->lng = $lng;
    }

    public function forward(): void
    {
        $this->tabs->setTabActive('settings');
        $this->lng->loadLanguageModule('tax');

        $questionList = new ilAssQuestionList($this->db, $this->lng, $this->component_repository);

        $questionList->setParentObjId($this->poolOBJ->getId());

        $questionList->load();

        $taxGUI = new ilObjTaxonomyGUI();

        $taxGUI->setAssignedObject($this->poolOBJ->getId());
        $taxGUI->setMultiple(true);

        $taxGUI->activateAssignedItemSorting($questionList, 'qpl', $this->poolOBJ->getId(), 'quest');

        $this->ctrl->forwardCommand($taxGUI);
    }
}
