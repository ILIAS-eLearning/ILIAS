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
use ILIAS\Skill\Tree;
use ILIAS\Skill\Usage;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class UsageTable
{
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected ServerRequestInterface $request;
    protected Tree\SkillTreeManager $tree_manager;
    protected Usage\SkillUsageManager $usage_manager;
    protected \ilSkillTreeRepository $tree_repo;
    protected int $skill_id = 0;
    protected int $tref_id = 0;
    protected array $usage = [];
    protected string $mode = "";

    public function __construct(string $cskill_id, array $usage, string $mode = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->tree_manager = $DIC->skills()->internal()->manager()->getTreeManager();
        $this->usage_manager = $DIC->skills()->internal()->manager()->getUsageManager();
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();

        $id_parts = explode(":", $cskill_id);
        $this->skill_id = (int) $id_parts[0];
        $this->tref_id = (int) $id_parts[1];
        $this->usage = $usage;
        $this->mode = $mode;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $data_retrieval = $this->getDataRetrieval();

        $tree = $this->tree_repo->getTreeForNodeId($this->skill_id);
        if ($this->mode === "tree") {
            $tree_obj = $this->tree_manager->getTree($tree->getTreeId());
            $title = $tree_obj->getTitle() . " > " . \ilSkillTreeNode::_lookupTitle($this->skill_id, $this->tref_id);
        } else {
            $title = \ilSkillTreeNode::_lookupTitle($this->skill_id, $this->tref_id);
        }

        //$description = $tree->getSkillTreePathAsString($skill_id, $tref_id);

        $table = $this->ui_fac->table()
                              ->data($title, $columns, $data_retrieval)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "type_info" => $this->ui_fac->table()->column()->statusIcon($this->lng->txt("skmg_type"))
                                        ->withIsSortable(false),
            "count" => $this->ui_fac->table()->column()->text($this->lng->txt("skmg_number"))
                                    ->withIsSortable(false)
        ];

        return $columns;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->usage,
            $this->usage_manager
        ) implements UI\Component\Table\DataRetrieval {
            use TableRecords;

            public function __construct(
                protected array $usage,
                protected Usage\SkillUsageManager $usage_manager
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
                    $row_id = $record["type"];

                    yield $row_builder->buildDataRow($row_id, $record);
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
                $records = [];
                $i = 0;
                foreach ($this->usage as $type => $type_usages) {
                    $records[$i]["type"] = $type;
                    $records[$i]["type_info"] = $this->usage_manager->getTypeInfoString($type);
                    $records[$i]["count"] = count($type_usages) . " " . $this->usage_manager->getObjTypeString($type);

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
