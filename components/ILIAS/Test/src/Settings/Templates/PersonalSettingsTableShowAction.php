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
use ILIAS\Test\Settings\SettingsFactory;
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
        private readonly PersonalSettingsRepository $repository,
        private readonly \ilObjUser $user,
        private readonly SettingsFactory $factory,
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

        $settings = $this->repository->getSettings($template->getId());
        $settings_info = array_merge(
            $this->factory->createMainSettings($settings)->getArrayForLog($this->information_generator),
            $this->factory->createScoreSettings($settings)->getArrayForLog($this->information_generator),
        );

        $modal_content[] = $this->information_generator->parseForTable(
            array_map(static fn(mixed $v): mixed => $v ?? '', $settings_info),
            $environment
        );

        $modal_content[] = $this->ui_factory->legacy()->content("<h4>{$this->lng->txt('mark_schema')}</h4>");
        $modal_content[] = $this->information_generator->parseForTable(
            $this->repository->getMarkSchema($template->getId())->toLog($this->information_generator),
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
