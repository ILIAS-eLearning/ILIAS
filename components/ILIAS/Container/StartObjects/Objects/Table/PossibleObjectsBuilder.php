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

namespace ILIAS\Container\StartObjects\Objects\Table;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ilLanguage;
use ILIAS\UI\Factory as UIFactory;
use ilContainerStartObjects;

class PossibleObjectsBuilder extends CommonTableBuilder
{
    public function __construct(
        object $parent_gui,
        string $parent_cmd,
        protected ilContainerStartObjects $start_objects,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory
    ) {
        parent::__construct($parent_gui, $parent_cmd, true);
    }

    protected function getId(): string
    {
        return "poss_start_objs_" . $this->start_objects->getObjId();
    }

    protected function getTitle(): string
    {
        return $this->lng->txt('crs_select_starter');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new PossibleObjectsRetrieval($this->start_objects);
    }

    protected function transformRow(array $data_row): array
    {
        $data = [
            'id' => $data_row['id'],
            'title' => $data_row['title'],
            'type' => $this->ui_factory->symbol()->icon()->standard(
                $data_row['type'],
                $this->lng->txt('obj_' . $data_row['type']),
            )
        ];
        if (isset($data_row['description'])) {
            $data['description'] = $data_row['description'];
        }
        return $data;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        return $table
            ->iconColumn('type', $this->lng->txt('type'))
            ->textColumn('title', $this->lng->txt('title'))
            ->textColumn('description', $this->lng->txt('description'))
            ->standardAction('addStarter', $this->lng->txt('crs_add_starter'));
    }
}
