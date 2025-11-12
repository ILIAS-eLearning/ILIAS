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
use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\Test\Participants\ParticipantTableActions;
use ILIAS\Test\Scoring\Marks\MarksRepository;
use ILIAS\Test\Settings\MainSettings\MainSettingsRepository;
use ILIAS\Test\Settings\ScoreReporting\ScoreSettingsRepository;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;

class PersonalSettingsTableShowAction implements TableAction
{
    public const string ACTION_ID = 'show_template';

    public function __construct(
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly \ilObjUser $user,
        private readonly PersonalSettingsRepository $repository,
        private readonly MainSettingsRepository $main_settings_repository,
        private readonly ScoreSettingsRepository $score_settings_repository,
        private readonly MarksRepository $marks_repository,
        private readonly AdditionalInformationGenerator $information_generator,
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
            $this->lng->txt('personal_settings_show'),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, ParticipantTableActions::SHOW_ACTION),
            $row_id_token
        )->withAsync();
    }

    public function buildModal(URLBuilder $url_builder, array $selected_templates): ?Modal
    {
        if (count($selected_templates) !== 1) {
            throw new \InvalidArgumentException('Expected exactly one template to show');
        }
        $template = reset($selected_templates);
        $modal_content = [];

        $environment = [
            'timezone' => new \DateTimeZone($this->user->getTimeZone()),
            'date_format' => $this->user->getDateFormat()->toString()
        ];

        $settings_id = $template->getSettingsId();
        $settings_info = array_merge(
            $this->main_settings_repository->getById($settings_id)->getArrayForLog($this->information_generator),
            $this->score_settings_repository->getById($settings_id)->getArrayForLog($this->information_generator),
        );

        $modal_content[] = $this->information_generator->parseForTable(
            array_map(static fn(mixed $v): mixed => $v ?? '', $settings_info),
            $environment
        );

        $mark_steps = $this->repository->lookupMarkSteps($template->getId());
        $mark_schema = $this->marks_repository->getMarkSchemaBySteps($mark_steps);

        $modal_content[] = $this->ui_factory->legacy()->content("<h4>{$this->lng->txt('mark_schema')}</h4>");

        // le, 2025-10-27: This is not the right way to go, as a logging facility is used directly for presentation.
        $modal_content[] = $this->information_generator->parseForTable(
            $mark_schema->toLog($this->information_generator),
            $environment
        );

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('additional_info'),
            $modal_content,
        )->withCancelButtonLabel($this->lng->txt('ok'));
    }

    public function onSubmit(
        URLBuilder $url_builder,
        ServerRequestInterface $request,
        array $selected_templates,
    ): ?Modal {
        return null;
    }
}
