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

use ILIAS\Questions\Administration\ConfigurationGUI;
use ILIAS\Questions\Administration\ConfigurationRepository;
use ILIAS\Questions\AnswerForm\Capabilities\Feedback\Feedback;
use ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent\SuggestedLearningContent;
use ILIAS\Questions\AnswerForm\Capabilities\Marking\Marking;
use ILIAS\Questions\Legacy\LocalDIC;
use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Questions\PublicInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\URI;

/**
 * @ilCtrl_isCalledBy ilObjQuestionsGUI: ilAdministrationGUI
 * @ilCtrl_Calls ilObjQuestionsGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjQuestionsGUI: ILIAS\Questions\Administration\ConfigurationGUI
 * @ilCtrl_Calls ilObjQuestionsGUI: QstsQuestionPageGUI
 */
class ilObjQuestionsGUI extends ilObjectGUI
{
    private const string TAB_IDENTIFIER_QUESTIONS = 'questions';
    private const string TAB_IDENTIFIER_SETTINGS = 'settings';
    private const string TAB_IDENTIFIER_UNITS = 'units';
    private const string TAB_IDENTIFIER_PERMISSIONS = 'perm_settings';

    private const string SUB_TAB_IDENTIFIER_EDIT_QUESTIONS = 'edit';
    private const string SUB_TAB_IDENTIFIER_PREVIEW_QUESTIONS = 'preview';

    private readonly UnitsRepository $units_repository;
    private readonly Edit $edit_view;
    private readonly ConfigurationRepository $configuration_repository;

    private readonly ilHelpGUI $help;
    private readonly DataFactory $data_factory;

    public function __construct(
        $a_data,
        int $a_id = 0,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;
        $this->help = $DIC['ilHelp'];
        $this->data_factory = new DataFactory();

        $this->type = 'qsts';

        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $local_dic = LocalDIC::dic();
        $this->edit_view = $local_dic[PublicInterface::class]
            ->getEditView($this->object->getId())
            ->withRequiredCapabilities([
                Feedback::class,
                SuggestedLearningContent::class,
                Marking::class
            ]);
        $this->configuration_repository = $local_dic[ConfigurationRepository::class];

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
            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->activateTab(self::TAB_IDENTIFIER_PERMISSIONS);
                $this->ctrl->forwardCommand(new \ilPermissionGUI($this));
                break;

            case strtolower(QstsQuestionPageGUI::class):
                $this->edit_view->forwardPageCmds(
                    $this->buildEditQuestionsBaseUri(),
                    $this->ref_id
                );
                break;

            case strtolower(ConfigurationGUI::class):
                $this->tabs_gui->activateTab(self::TAB_IDENTIFIER_SETTINGS);
                $this->ctrl->forwardCommand(
                    new ConfigurationGUI(
                        $this->ctrl,
                        $this->http,
                        $this->lng,
                        $this->refinery,
                        $this->tpl,
                        $this->ui_factory,
                        $this->ui_renderer,
                        $this->configuration_repository
                    )
                );
                break;

            case strtolower(GlobalConfigurationGUI::class):
                $this->tabs_gui->activateTab(self::TAB_IDENTIFIER_UNITS);
                $this->ctrl->forwardCommand(
                    new GlobalConfigurationGUI(
                        $this->units_repository,
                        $this->lng,
                        $this->ctrl,
                        $this->rbac_system,
                        $this->tpl,
                        $this->toolbar,
                        $this->tabs_gui,
                        $this->help
                    )
                );
                break;

            case strtolower(ilObjQuestionPreviewGUI::class):
                $this->addSubTabs();
                $this->tabs_gui->activateTab(self::TAB_IDENTIFIER_QUESTIONS);
                $this->tabs_gui->activateSubTab(self::SUB_TAB_IDENTIFIER_PREVIEW_QUESTIONS);
                $this->ctrl->forwardCommand(
                    new ilObjQuestionPreviewGUI(
                        $this->object->getId()
                    )
                );

                break;
            default:
                $this->addSubTabs();
                if ($cmd === null || $cmd === '' || $cmd === 'view') {
                    $cmd = 'editQuestions';
                }
                $cmd .= 'Cmd';
                $this->$cmd();
                break;
        }
    }

    private function editQuestionsCmd(): void
    {
        $this->tabs_gui->activateTab(self::TAB_IDENTIFIER_QUESTIONS);
        $this->tabs_gui->activateSubTab(self::SUB_TAB_IDENTIFIER_EDIT_QUESTIONS);

        $this->tpl->setContent(
            $this->ui_renderer->render(
                $this->edit_view->getUI(
                    $this->buildEditQuestionsBaseUri(),
                    $this->object->getRefId()
                )
            )
        );
    }

    public function getAdminTabs(): void
    {
        $this->addTabs();
    }

    protected function addTabs(): void
    {
        if ($this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                self::TAB_IDENTIFIER_QUESTIONS,
                $this->lng->txt(self::TAB_IDENTIFIER_QUESTIONS),
                $this->ctrl->getLinkTargetByClass(self::class, 'editQuestions')
            );

            $this->tabs_gui->addTab(
                self::TAB_IDENTIFIER_SETTINGS,
                $this->lng->txt(self::TAB_IDENTIFIER_SETTINGS),
                $this->ctrl->getLinkTargetByClass(ConfigurationGUI::class, ''),
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                self::TAB_IDENTIFIER_PERMISSIONS,
                $this->lng->txt(self::TAB_IDENTIFIER_PERMISSIONS),
                $this->ctrl->getLinkTargetByClass([self::class, ilPermissionGUI::class], 'perm'),
            );
        }
    }

    private function addSubTabs(): void
    {
        if (!$this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            return;
        }

        $this->tabs_gui->addSubTab(
            self::SUB_TAB_IDENTIFIER_EDIT_QUESTIONS,
            $this->lng->txt('edit'),
            $this->ctrl->getLinkTargetByClass(self::class, 'editQuestions')
        );

        $this->tabs_gui->addSubTab(
            self::SUB_TAB_IDENTIFIER_PREVIEW_QUESTIONS,
            $this->lng->txt('preview'),
            $this->ctrl->getLinkTargetByClass([self::class, ilObjQuestionPreviewGUI::class])
        );
    }

    private function buildEditQuestionsBaseUri(): URI
    {
        return $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getFormActionByClass(self::class, 'editQuestions')
        );
    }
}
