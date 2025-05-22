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

namespace ILIAS\Test\Participants;

use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Table\Action\Action;
use Psr\Http\Message\ServerRequestInterface;

class ParticipantTableShowResultsAction implements TableAction
{
    public const ACTION_ID = 'show_results';

    public function __construct(
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly \ilTestAccess $test_access,
        private readonly \ilCtrl $ctrl,
        private readonly \ilObjTest $test_obj
    ) {
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    public function isAvailable(): bool
    {
        return $this->test_access->checkParticipantsResultsAccess()
            && $this->test_obj->evalTotalPersons() > 0;
    }

    public function getTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        return $this->ui_factory->table()->action()->standard(
            $this->lng->txt(self::ACTION_ID),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, ParticipantTableActions::SUBMIT_ACTION),
            $row_id_token
        );
    }

    public function getModal(
        URLBuilder $url_builder,
        array $selected_participants,
        bool $all_participants_selected
    ): ?Modal {
        return null;
    }

    public function onSubmit(
        URLBuilder $url_builder,
        ServerRequestInterface $request,
        array $selected_participants,
        bool $all_participants_selected
    ): ?Modal {
        if ($selected_participants === []) {
            return null;
        }
        foreach ($selected_participants as $participant) {
            if (!$this->test_access->checkResultsAccessForActiveId(
                $participant->getActiveId(),
                $this->test_obj->getTestId()
            )) {
                $this->tpl->setOnScreenMessage(
                    \ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                    $this->lng->txt('no_permission'),
                    true
                );
                return null;
            }
        }
        $this->ctrl->setParameterByClass(
            \ilTestEvaluationGUI::class,
            'active_ids',
            array_reduce(
                $selected_participants,
                static function (string $c, Participant $v): string {
                    if ($c === '') {
                        return (string) $v->getActiveId();
                    }
                    $c .= ",{$v->getActiveId()}";
                    return $c;
                },
                ''
            )
        );

        $this->ctrl->redirectByClass(\ilTestEvaluationGUI::class, 'showResults');
    }

    public function allowActionForRecord(Participant $record): bool
    {
        return $record->getActiveId() !== null && $this->test_access->checkResultsAccessForActiveId(
            $record->getActiveId(),
            $this->test_obj->getTestId()
        );
    }

    public function getSelectionErrorMessage(): ?string
    {
        return null;
    }
}
