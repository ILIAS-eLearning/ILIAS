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

declare(strict_types=1);

use ILIAS\Questions\Legacy\LocalDIC;
use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\UploadAnswerOptionsGUI;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\URI;

/**
 * @ilCtrl_isCalledBy ilObjQuestionsGUI: ilAdministrationGUI
 * @ilCtrl_Calls ilObjQuestionsGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjQuestionsGUI: QstsQuestionPageGUI
 */
class ilObjQuestionsGUI extends ilObjectGUI
{
    private Edit $edit_view;

    private DataFactory $data_factory;

    public function __construct(
        $a_data,
        int $a_id = 0,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;
        $this->data_factory = new DataFactory();

        $this->edit_view = LocalDIC::dic()[Edit::class];

        $this->type = 'qsts';

        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->lng->loadLanguageModule('assessment');
        $this->lng->loadLanguageModule('qsts');

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read_assf"), $this->ilias->error_obj->WARNING);
        }
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case strtolower(UploadAnswerOptionsGUI::class):
                $this->ctrl->forwardCommand(new UploadAnswerOptionsGUI());
                break;

            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->activateTab('perm_settings');
                $this->ctrl->forwardCommand(new \ilPermissionGUI($this));
                break;

            case strtolower(QstsQuestionPageGUI::class):
                $this->edit_view->forwardPageCmds(
                    $this->tpl,
                    $this->buildEditQuestionsBaseUri()
                );
                break;

            default:
                if ($cmd === null || $cmd === '' || $cmd === 'view') {
                    $cmd = 'viewQuestions';
                }
                $cmd .= 'Object';
                $this->$cmd();
                break;
        }
    }

    public function viewQuestionsObject(): void
    {
        $this->tabs_gui->activateTab('questions');

        $this->tpl->setContent(
            $this->edit_view->view(
                $this->toolbar,
                $this->buildEditQuestionsBaseUri()
            )->render($this->ui_renderer)
        );
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    protected function getTabs(): void
    {
        if ($this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'questions',
                $this->lng->txt('questions'),
                $this->ctrl->getLinkTargetByClass(self::class, 'viewQuestions')
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'perm_settings',
                $this->lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass([self::class, ilPermissionGUI::class], 'perm')
            );
        }
    }

    private function buildEditQuestionsBaseUri(): URI
    {
        return $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(self::class, 'viewQuestions')
        );
    }
}
