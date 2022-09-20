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
 * GUI class that manages the question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilObjTestDynamicQuestionSetConfigGUI: ilPropertyFormGUI
 */
class ilObjTestDynamicQuestionSetConfigGUI
{
    public const CMD_SHOW_FORM = 'showForm';
    public const CMD_SAVE_FORM = 'saveForm';
    public const CMD_GET_TAXONOMY_OPTIONS_ASYNC = 'getTaxonomyOptionsAsync';

    public const QUESTION_ORDERING_TYPE_UPDATE_DATE = 'ordering_by_date';
    public const QUESTION_ORDERING_TYPE_TAXONOMY = 'ordering_by_tax';

    protected ilCtrlInterface $ctrl;
    protected ilAccessHandler $access;
    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected ilObjTest $testOBJ;
    protected ilObjTestDynamicQuestionSetConfig $questionSetConfig;

    public function __construct(
        ilCtrl $ctrl,
        ilAccessHandler $access,
        ilTabsGUI $tabs,
        ilLanguage $lng,
        ilGlobalTemplateInterface $tpl,
        ilDBInterface $db,
        ilTree $tree,
        ilComponentRepository $component_repository,
        ilObjTest $testOBJ
    ) {
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tabs = $tabs;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->db = $db;
        $this->tree = $tree;
        $this->testOBJ = $testOBJ;

        $this->questionSetConfig = new ilObjTestDynamicQuestionSetConfig(
            $this->tree,
            $this->db,
            $component_repository,
            $this->testOBJ
        );
    }

    public function executeCommand()
    {
        // allow only write access

        if (!$this->access->checkAccess("write", "", $this->testOBJ->getRefId())) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirectByClass('ilObjTestGUI', "infoScreen");
        }

        // activate corresponding tab (auto activation does not work in ilObjTestGUI-Tabs-Salad)

        $this->tabs->activateTab('assQuestions');
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("ctm_cannot_be_changed"));
    }
}
