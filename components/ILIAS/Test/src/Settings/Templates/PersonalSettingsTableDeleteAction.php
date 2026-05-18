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
use ILIAS\Test\Scoring\Marks\MarksRepository;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UICore\GlobalTemplate;
use Psr\Http\Message\ServerRequestInterface;

class PersonalSettingsTableDeleteAction implements TableAction
{
    public const string ACTION_ID = 'delete_template';

    public function __construct(
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly GlobalTemplate $tpl,
        private readonly PersonalSettingsRepository $repository,
        private readonly MarksRepository $marks_repository,
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
        return $this->ui_factory->table()->action()->standard(
            $this->lng->txt('delete'),
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
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt('personal_settings_delete_confirmation'),
            $url_builder->buildURI()->__toString()
        )->withAffectedItems(
            array_map(
                fn(PersonalSettingsTemplate $v) => $this->ui_factory->modal()->interruptiveItem()->standard(
                    (string) $v->getId(),
                    $v->getName()
                ),
                $selected_templates
            )
        )->withActionButtonLabel($this->lng->txt('delete'));
    }

    public function onSubmit(
        URLBuilder $url_builder,
        ServerRequestInterface $request,
        array $selected_templates,
    ): ?Modal {
        foreach ($selected_templates as $template) {
            $this->deleteTemplate($template);
        }

        $this->tpl->setOnScreenMessage(
            GlobalTemplate::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('personal_settings_delete_success'),
            true
        );
        return null;
    }

    public function deleteTemplate(PersonalSettingsTemplate $template): void
    {
        $mark_ids = $this->repository->lookupMarkSteps($template->getId());
        $this->repository->detachMarkSteps($template->getId(), $mark_ids);
        $this->marks_repository->deleteSteps($mark_ids);

        $this->repository->delete($template);
    }
}
