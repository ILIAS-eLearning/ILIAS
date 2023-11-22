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
class AssignedObjectsTable
{
    protected \ilLanguage $lng;
    protected \ilTree $tree;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected ServerRequestInterface $request;
    protected array $objects = [];

    public function __construct(array $objects)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();

        $this->objects = $objects;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $data_retrieval = $this->getDataRetrieval();

        $table = $this->ui_fac->table()
                              ->data($this->lng->txt("skmg_assigned_objects"), $columns, $data_retrieval)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "type" => $this->ui_fac->table()->column()->statusIcon($this->lng->txt("type"))
                                   ->withIsSortable(false),
            "title" => $this->ui_fac->table()->column()->text($this->lng->txt("title")),
            "path" => $this->ui_fac->table()->column()->text($this->lng->txt("path"))
                                   ->withIsSortable(false)
        ];

        return $columns;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->lng,
            $this->ui_fac,
            $this->ui_ren,
            $this->tree,
            $this->objects
        ) implements UI\Component\Table\DataRetrieval {
            use TableRecords;

            public function __construct(
                protected \ilLanguage $lng,
                protected UI\Factory $ui_fac,
                protected UI\Renderer $ui_ren,
                protected \ilTree $tree,
                protected array $objects
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
                $records = $this->getRecords($range, $order);
                foreach ($records as $idx => $record) {
                    $row_id = (string) $record["obj_id"];

                    yield $row_builder->buildDataRow($row_id, $record);
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return count($this->getRecords());
            }

            protected function getRecords(Data\Range $range = null, Data\Order $order = null): array
            {
                $records = [];
                $i = 0;
                foreach ($this->objects as $obj_id) {
                    $records[$i]["obj_id"] = $obj_id;
                    $records[$i]["title"] = \ilObject::_lookupTitle($obj_id);

                    $obj_type = \ilObject::_lookupType($obj_id);
                    $icon = $this->ui_ren->render(
                        $this->ui_fac->symbol()->icon()->standard(
                            $obj_type,
                            $this->lng->txt("icon") . " " . $this->lng->txt($obj_type),
                            "medium"
                        )
                    );
                    $records[$i]["type"] = $icon;

                    $obj_ref_id = \ilObject::_getAllReferences($obj_id);
                    $obj_ref_id = end($obj_ref_id);
                    $obj_ref_id_parent = $this->tree->getParentId($obj_ref_id);
                    $path = new \ilPathGUI();
                    $records[$i]["path"] = $path->getPath($this->tree->getParentId($obj_ref_id_parent), (int) $obj_ref_id);

                    $i++;
                }

                if ($order) {
                    $records = $this->orderRecords($records, $order);
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
