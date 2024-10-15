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

use ILIAS\Refinery\Factory;
use ILIAS\Test\Table\TestPersonalDefaultSettingsTable;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\Services as HTTPServices;

class TestPersonalDefaultSettingsGUI
{
    public const DELETE_CMD = 'deleteDefaults';
    public const ADD_CMD = 'addDefaults';
    public const APPLY_CMD = 'applyDefaults';
    private array $row_data = [];

    public function __construct(
        private readonly ilLanguage $lng,
        private readonly UIFactory $ui_factory,
        private readonly int $parent_obj_id,
        private readonly UIRenderer $ui_renderer,
        private readonly GlobalHttpState $http_state,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilCtrl $ctrl,
        private readonly ilToolbarGUI $toolbar,
        private readonly ?ilObjTest $object,
        private readonly HTTPServices $http,
        private readonly Factory $refinery,
        private readonly ilTestQuestionSetConfigFactory $test_question_set_config_factory
    ) {
    }

    /**
     * @throws ilCtrlException
     */
    public function getTable(): string
    {
        $table = new TestPersonalDefaultSettingsTable(
            $this->lng,
            $this->ui_factory,
            $this->ctrl,
            $this,
            $this->parent_obj_id,
            $this->row_data
        );


        return $this->ui_renderer->render([
            $table->getComponent()->withRequest($this->http_state->request())
        ]);
    }

    public function setData(array $log): void
    {
        $this->row_data = $log;
    }

    public function getData(): array
    {
        return $this->row_data;
    }

    /**
     * @throws ilCtrlException|Exception
     */
    public function executeCommand(): bool
    {
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'addDefaults'));
        $this->toolbar->addFormButton($this->lng->txt('add'), 'addDefaults');
        $this->toolbar->addInputItem(new ilTextInputGUI($this->lng->txt('tst_defaults_defaults_of_test'), 'name'), true);


        switch (strtolower((string) $this->ctrl->getNextClass($this))) {
            case strtolower(__CLASS__):
            case '':
                $cmd = $this->ctrl->getCmd() . 'Cmd';
                return $this->$cmd();
            default:
                $this->defaultsCmd();
                return false;
        }
    }

    /**
     * @throws ilCtrlException
     */
    private function defaultsCmd(): bool
    {
        $defaults = $this->object->getAvailableDefaults();
        $this->setData($defaults);

        $this->tpl->setContent($this->getTable());
        return true;
    }

    /**
     * @throws ilCtrlException
     */
    private function deleteDefaultsCmd(): bool
    {
        if($this->http->wrapper()->query()->has('test_defaults_ids')) {
            $ids = $this->http->wrapper()->query()->retrieve(
                'test_defaults_ids',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->byTrying([$this->refinery->kindlyTo()->int(), $this->refinery->kindlyTo()->string()])
                )
            );
            if($ids[0] === 'ALL_OBJECTS') {
                $this->object->deleteAllDefaults();
            } else {
                foreach ($ids as $test_default_id) {
                    $this->object->deleteDefaults($test_default_id);
                }
            }
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
        }

        return $this->defaultsCmd();
    }

    /**
     * @throws ilCtrlException
     */
    private function addDefaultsCmd(): bool
    {
        $name = $this->http->wrapper()->post()->retrieve(
            'name',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );

        if($name === '') {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('tst_defaults_enter_name'));
        } else {
            $this->object->addDefaults($name);
        }

        return $this->defaultsCmd();
    }

    /**
     * @throws ilCtrlException
     */
    public function confirmedApplyDefaultsCmd(): bool
    {
        $id = $this->http->wrapper()->post()->retrieve(
            'confirmed_test_defaults_id',
            $this->refinery->kindlyTo()->int()
        );
        return $this->applyDefaultsCmd(true, $id);
    }

    /**
     * @throws ilCtrlException
     */
    public function applyDefaultsCmd(bool $confirmed = false, ?int $id = null): bool
    {
        if($id === null) {
            if($this->http->wrapper()->query()->has('test_defaults_ids')) {
                $ids = $this->http->wrapper()->query()->retrieve(
                    'test_defaults_ids',
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->byTrying([$this->refinery->kindlyTo()->int(), $this->refinery->kindlyTo()->string()])
                    )
                );
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('tst_defaults_apply_select_one'));

                return $this->defaultsCmd();
            }
            if (count($ids) !== 1) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('tst_defaults_apply_select_one'));

                return $this->defaultsCmd();
            }
            $id = $ids[0];
        }


        // do not apply if user datasets exist
        if ($this->object->evalTotalPersons() > 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('tst_defaults_apply_not_possible'));

            return $this->defaultsCmd();
        }

        $defaults = $this->object->getTestDefaults($id);
        $defaultSettings = unserialize($defaults['defaults']);

        if (isset($defaultSettings['isRandomTest'])) {
            if ($defaultSettings['isRandomTest']) {
                $newQuestionSetType = ilObjTest::QUESTION_SET_TYPE_RANDOM;
                $this->object->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_RANDOM);
            } else {
                $newQuestionSetType = ilObjTest::QUESTION_SET_TYPE_FIXED;
                $this->object->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_FIXED);
            }
        } elseif (isset($defaultSettings['questionSetType'])) {
            $newQuestionSetType = $defaultSettings['questionSetType'];
        }
        $oldQuestionSetType = $this->object->getQuestionSetType();
        $questionSetTypeSettingSwitched = ($oldQuestionSetType !== $newQuestionSetType);

        $oldQuestionSetConfig = $this->test_question_set_config_factory->getQuestionSetConfig();

        if(!$confirmed && $questionSetTypeSettingSwitched && $oldQuestionSetConfig->doesQuestionSetRelatedDataExist()) {
            $confirmation = new ilTestSettingsChangeConfirmationGUI($this->object);

            $confirmation->setFormAction($this->ctrl->getFormAction($this));
            $confirmation->setCancel($this->lng->txt('cancel'), 'defaults');
            $confirmation->setConfirm($this->lng->txt('confirm'), 'confirmedApplyDefaults');

            $confirmation->setOldQuestionSetType($this->object->getQuestionSetType());
            $confirmation->setNewQuestionSetType($newQuestionSetType);
            $confirmation->setQuestionLossInfoEnabled(false);
            $confirmation->build();

            $confirmation->addHiddenItem('confirmed_test_defaults_id', $ids[0]);
            //$confirmation->populateParametersFromPost(); //@todo outdated

            $this->tpl->setContent($this->ctrl->getHTML($confirmation));

            return true;
        }

        if ($questionSetTypeSettingSwitched && !$this->object->getOfflineStatus()) {
            $this->object->setOfflineStatus(true);

            $info = $this->lng->txt('tst_set_offline_due_to_switched_question_set_type_setting');

            $this->tpl->setOnScreenMessage('info', $info, true);
        }

        $this->object->applyDefaults($defaults);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_defaults_applied'), true);

        if ($questionSetTypeSettingSwitched && $oldQuestionSetConfig->doesQuestionSetRelatedDataExist()) {
            $oldQuestionSetConfig->removeQuestionSetRelatedData();
        }

        $this->ctrl->redirect($this, 'defaults');

        return true;
    }
}
