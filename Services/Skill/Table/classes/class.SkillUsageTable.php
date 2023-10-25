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

use ILIAS\Skill\Tree;
use ILIAS\UI;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillUsageTable
{
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected Tree\SkillTreeManager $tree_manager;
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
        $this->ui_ren = $DIC->ui()->renderer();
        $this->tree_manager = $DIC->skills()->internal()->manager()->getTreeManager();
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();

        $id_parts = explode(":", $cskill_id);
        $this->skill_id = (int) $id_parts[0];
        $this->tref_id = (int) $id_parts[1];
        $this->usage = $usage;
        $this->mode = $mode;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = [
            "type_info" => $this->ui_fac->table()->column()->statusIcon($this->lng->txt("skmg_type"))
                                        ->withIsSortable(false),
            "count" => $this->ui_fac->table()->column()->text($this->lng->txt("skmg_number"))
                                    ->withIsSortable(false)
        ];

        $data_retrieval = new class (
            $this->usage
        ) implements \ILIAS\UI\Component\Table\DataRetrieval {
            public function __construct(
                protected array $usage
            ) {
            }

            public function getRows(
                \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                \ILIAS\Data\Range $range,
                \ILIAS\Data\Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                $records = $this->getRecords($order);
                foreach ($records as $idx => $record) {
                    $row_id = $record["type"];

                    yield $row_builder->buildDataRow($row_id, $record);
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return null;
            }

            protected function getRecords(\ILIAS\Data\Order $order): array
            {
                $records = [];
                $i = 0;
                foreach ($this->usage as $type => $type_usages) {
                    $records[$i]["type"] = $type;
                    $records[$i]["type_info"] = \ilSkillUsage::getTypeInfoString($type);
                    $records[$i]["count"] = count($type_usages) . " " . \ilSkillUsage::getObjTypeString($type);

                    $i++;
                }

                return $records;
            }
        };

        $tree = $this->tree_repo->getTreeForNodeId($this->skill_id);
        if ($this->mode === "tree") {
            $tree_obj = $this->tree_manager->getTree($tree->getTreeId());
            $title = $tree_obj->getTitle() . " > " . \ilSkillTreeNode::_lookupTitle($this->skill_id, $this->tref_id);
        } else {
            $title = \ilSkillTreeNode::_lookupTitle($this->skill_id, $this->tref_id);
        }

        //$description = $tree->getSkillTreePathAsString($skill_id, $tref_id);

        $table = $this->ui_fac->table()
                              ->data($title, $columns, $data_retrieval);

        return $table;
    }
}
