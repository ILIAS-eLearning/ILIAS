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
use ILIAS\Skill\Node;
use ILIAS\Skill\Personal;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SelfEvaluationTable
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected \ilObjUser $user;
    protected UI\Factory $ui_fac;
    protected ServerRequestInterface $request;
    protected Data\Factory $df;
    protected \ilSkillTreeRepository $tree_repo;
    protected Node\SkillTreeNodeManager $node_manager;
    protected Personal\SelfEvaluationManager $self_evaluation_manager;
    protected int $top_skill_id = 0;
    protected int $tref_id = 0;
    protected int $basic_skill_id = 0;

    public function __construct(int $top_skill_id, int $tref_id, int $basic_skill_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ui_fac = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->df = new Data\Factory();

        $this->top_skill_id = $top_skill_id;
        $this->tref_id = $tref_id;
        $this->basic_skill_id = $basic_skill_id;

        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        $tree_id = $this->tree_repo->getTreeIdForNodeId($this->basic_skill_id);
        $this->node_manager = $DIC->skills()->internal()->manager()->getTreeNodeManager($tree_id);
        $this->self_evaluation_manager = $DIC->skills()->internal()->manager()->getSelfEvaluationManager();
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        $title = $this->node_manager->getWrittenPath($this->basic_skill_id);
        $table = $this->ui_fac->table()
                              ->data($title, $columns, $data_retrieval)
                              ->withActions($actions)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "title" => $this->ui_fac->table()->column()->text($this->lng->txt("skmg_skill_level"))
                                    ->withIsSortable(false),
            "description" => $this->ui_fac->table()->column()->text($this->lng->txt("description"))
                                          ->withIsSortable(false),
            "status" => $this->ui_fac->table()->column()->status($this->lng->txt("status"))
                                     ->withIsSortable(false)
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        $query_params_namespace = ["skl_self_evaluation_table"];

        $uri_select = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass(
                "ilpersonalskillsgui",
                "saveSelfEvaluation"
            )
        );
        $url_builder_select = new UI\URLBuilder($uri_select);
        list($url_builder_select, $action_parameter_token_select, $row_id_token_select) =
            $url_builder_select->acquireParameters(
                $query_params_namespace,
                "action",
                "level_ids"
            );

        $actions = [
            "select" => $this->ui_fac->table()->action()->single(
                $this->lng->txt("skmg_select_level"),
                $url_builder_select->withParameter($action_parameter_token_select, "selectLevel"),
                $row_id_token_select
            )
        ];

        return $actions;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->top_skill_id,
            $this->tref_id,
            $this->basic_skill_id,
            $this->lng,
            $this->user,
            $this->self_evaluation_manager
        ) implements UI\Component\Table\DataRetrieval {
            use TableRecords;

            public function __construct(
                protected int $top_skill_id,
                protected int $tref_id,
                protected int $basic_skill_id,
                protected \ilLanguage $lng,
                protected \ilObjUser $user,
                protected Personal\SelfEvaluationManager $self_evaluation_manager
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
                $records = $this->getRecords($range);
                foreach ($records as $idx => $record) {
                    $row_id = $record["id"];

                    yield $row_builder->buildDataRow((string) $row_id, $record);
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return count($this->getRecords());
            }

            protected function getRecords(Data\Range $range = null): array
            {
                $current_level_id = $this->self_evaluation_manager->getSelfEvaluation(
                    $this->user->getId(),
                    $this->top_skill_id,
                    $this->tref_id,
                    $this->basic_skill_id
                );

                $skill = \ilSkillTreeNodeFactory::getInstance($this->basic_skill_id);
                $i = 0;
                foreach ($skill->getLevelData() as $level) {
                    $records[$i]["id"] = $level["id"];
                    $records[$i]["title"] = $level["title"];
                    $records[$i]["description"] = $level["description"];
                    $records[$i]["status"] = ($current_level_id == $level["id"])
                        ? $this->lng->txt("checked")
                        : $this->lng->txt("unchecked");

                    $i++;
                }

                if ($range) {
                    $records = $this->limitRecords($records, $range);
                }

                return $records;
            }
        };

        return $data_retrieval;
    }
}
