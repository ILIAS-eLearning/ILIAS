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
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;

class PersonalSettingsTableExportAction implements TableAction
{
    public const string ACTION_ID = 'export_template';

    public function __construct(
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly PersonalSettingsExporter $exporter,
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
            $this->lng->txt('personal_settings_export'),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, ParticipantTableActions::SUBMIT_ACTION),
            $row_id_token
        );
    }

    public function buildModal(URLBuilder $url_builder, array $selected_templates): ?Modal
    {
        return null;
    }

    public function onSubmit(
        URLBuilder $url_builder,
        ServerRequestInterface $request,
        array $selected_templates,
    ): ?Modal {
        if (count($selected_templates) !== 1) {
            throw new \InvalidArgumentException('Expected exactly one template to be selected');
        }

        $this->exporter->setTemplateId(reset($selected_templates)->getId());
        $this->exporter->deliver();

        return null;
    }
}
