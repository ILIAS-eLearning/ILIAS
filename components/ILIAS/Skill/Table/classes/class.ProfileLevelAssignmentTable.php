<?php

declare(strict_types=1);

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
 ********************************************************************
 */

namespace ILIAS\Skill\Table;

use ILIAS\Data;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ProfileLevelAssignmentTable
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected ServerRequestInterface $request;
    protected Data\Factory $df;
    protected int $skill_id = 0;
    protected bool $update = false;
    protected \ilBasicSkill $skill;

    public function __construct(string $cskill_id, bool $update = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->df = new Data\Factory();

        $id_parts = explode(":", $cskill_id);
        $this->skill_id = (int) $id_parts[0];
        $this->skill = new \ilBasicSkill($this->skill_id);
        $this->update = $update;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        $title = $this->skill->getTitle() . ", " . $this->lng->txt("skmg_skill_levels");
        $table = $this->ui_fac->table()
                              ->data($title, $columns, $data_retrieval)
                              ->withActions($actions)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "title" => $this->ui_fac->table()->column()->text($this->lng->txt("title"))
                                    ->withIsSortable(false)
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        $query_params_namespace = ["skl_profile_level_assignment_table"];

        $uri_assign = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass(
                "ilskillprofilegui",
                $this->update ? "updateLevelOfProfile" : "assignLevelToProfile"
            )
        );
        $url_builder_assign = new UI\URLBuilder($uri_assign);
        list($url_builder_assign, $action_parameter_token_assign, $row_id_token_assign) =
            $url_builder_assign->acquireParameters(
                $query_params_namespace,
                "action",
                "level_ids"
            );

        $actions = [
            "assign" => $this->ui_fac->table()->action()->single(
                $this->lng->txt("skmg_assign_level"),
                $url_builder_assign->withParameter($action_parameter_token_assign, "assignLevel"),
                $row_id_token_assign
            )
        ];

        return $actions;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->skill
        ) implements UI\Component\Table\DataRetrieval {
            public function __construct(
                protected \ilBasicSkill $skill
            ) {
            }

            public function getRows(
                UI\Component\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Data\Range $range,
                Data\Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                $records = $this->getRecords($order);
                foreach ($records as $idx => $record) {
                    $row_id = $record["id"];

                    yield $row_builder->buildDataRow((string) $row_id, $record);
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return null;
            }

            protected function getRecords(Data\Order $order): array
            {
                $level_data = $this->skill->getLevelData();

                $records = [];
                $i = 0;
                foreach ($level_data as $levels) {
                    $records[$i]["id"] = $levels["id"];
                    $records[$i]["title"] = $levels["title"];

                    $i++;
                }

                return $records;
            }
        };

        return $data_retrieval;
    }
}
