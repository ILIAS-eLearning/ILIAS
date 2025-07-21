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

use ILIAS\TestQuestionPool\Skill\SkillAssignmentTableAction;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class SkillAssignmentTableActions
{
    public const string ROW_ID_PARAMETER = 'a_id';
    public const string FULL_ROW_ID_PARAMETER = SkillAssignmentTable::ID . '_' . self::ROW_ID_PARAMETER;
    public const string ACTION_PARAMETER = 'action';
    public const string ACTION_TYPE_PARAMETER = 'action_type';
    public const string SHOW_ACTION = 'showAction';
    public const string ALL_OBJECTS = 'ALL_OBJECTS';

    /**
     * @param array<SkillAssignmentTableAction> $actions
     */
    public function __construct(protected readonly ilGlobalTemplateInterface $tpl, private array $actions)
    {
    }

    public function getEnabledActions(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): array {
        return array_filter(
            array_map(
                static function (SkillAssignmentTableAction $action) use (
                    $url_builder,
                    $row_id_token,
                    $action_token,
                    $action_type_token
                ): ?Action {
                    return $action->isAvailable()
                        ? $action->getTableAction($url_builder, $row_id_token, $action_token, $action_type_token)
                        : null;
                },
                $this->actions
            )
        );
    }

    public function getAction(string $action_id): ?SkillAssignmentTableAction
    {
        return $this->actions[$action_id] ?? null;
    }

    public function onDataRow(DataRow $row, mixed $record): DataRow
    {
        return array_reduce(
            array_keys($this->actions),
            fn(DataRow $c, string $v): DataRow => $this->actions[$v]->allowActionForRecord($record)
                ? $c
                : $c->withDisabledAction($v),
            $row
        );
    }
}
