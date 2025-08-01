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

namespace ILIAS\Test\Settings\Templates;

use ILIAS\Language\Language;
use ILIAS\Test\Participants\ParticipantTableActions;
use ILIAS\Test\Settings\SettingsFactory;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UICore\GlobalTemplate;
use Psr\Http\Message\ServerRequestInterface;

class PersonalSettingsTableApplyAction implements TableAction
{
    public const string ACTION_ID = 'apply_template';

    public function __construct(
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly \ilTestQuestionSetConfigFactory $question_set_config_factory,
        private readonly PersonalSettingsRepository $repository,
        private readonly SettingsFactory $factory,
        private readonly \ilObjTest $test_obj,
        private readonly GlobalTemplate $tpl,
    ) {
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    public function buildTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        return $this->ui_factory->table()->action()->single(
            $this->lng->txt('apply'),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, ParticipantTableActions::SHOW_ACTION),
            $row_id_token
        )->withAsync();
    }

    public function buildModal(
        URLBuilder $url_builder,
        array $selected_templates
    ): ?Modal {
        $template = $this->checkSelectedTemplate($selected_templates);
        $question_set_type_changed = $this->hasDifferentQuestionSetType($template->getId());

        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt($question_set_type_changed ? 'personal_settings_apply_changed_confirmation' : 'personal_settings_apply_confirmation'),
            $url_builder->buildURI()->__toString()
        )->withAffectedItems(
            [
                $this->ui_factory->modal()->interruptiveItem()->standard(
                    (string) $template->getId(),
                    $template->getName(),
                    null,
                    sprintf($this->lng->txt('personal_settings_apply_description'), $this->test_obj->getTitle()),
                ),
            ]
        )->withActionButtonLabel($this->lng->txt('apply'));
    }

    public function onSubmit(
        URLBuilder $url_builder,
        ServerRequestInterface $request,
        array $selected_templates,
    ): ?Modal {
        $template = $this->checkSelectedTemplate($selected_templates);

        $old_question_set_config = $this->hasDifferentQuestionSetType($template->getId()) ?
            $this->question_set_config_factory->getQuestionSetConfig() :
            null;

        $this->repository->applyTemplate($this->test_obj->getTestId(), $template->getId());

        if ($old_question_set_config && $old_question_set_config->doesQuestionSetRelatedDataExist()) {
            $old_question_set_config->removeQuestionSetRelatedData();
        }

        $this->tpl->setOnScreenMessage(
            GlobalTemplate::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('personal_settings_apply_success'),
            true
        );
        return null;
    }

    private function checkSelectedTemplate(array $selected_templates): PersonalSettingsTemplate
    {
        if (count($selected_templates) !== 1) {
            throw new \InvalidArgumentException('personal_settings_invalid_selection');
        }

        // Do not apply if user datasets exist
        if ($this->test_obj->evalTotalPersons() > 0) {
            throw new \InvalidArgumentException('personal_settings_apply_not_possible');
        }

        return reset($selected_templates);
    }

    private function hasDifferentQuestionSetType(int $template_id): bool
    {
        $template_settings = $this->repository->getSettings($template_id);
        $template_main_settings = $this->factory->createMainSettings($template_settings);
        return $template_main_settings->getGeneralSettings()->getQuestionSetType() !== $this->test_obj->getQuestionSetType();
    }
}
