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

use ILIAS\MediaObjects\OverviewGUI\SubObjectRetrieval;
use ILIAS\MediaObjects\OverviewGUI\Table\Builder as TableBuilder;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Repository\Filter\FilterAdapterGUI;
use ILIAS\MediaObjects\InternalGUIService;
use ILIAS\MediaObjects\InternalDomainService;

class ilMediaObjectOverviewGUI
{
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;
    protected DataFactory $data_factory;

    public function __construct(
        protected SubObjectRetrieval $sub_object_retrieval
    ) {
        global $DIC;

        $this->domain = $DIC->mediaObjects()->internal()->domain();
        $this->gui = $DIC->mediaObjects()->internal()->gui();
        $this->data_factory = new DataFactory();

        $this->domain->lng()->loadLanguageModule('mob');
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();

        switch ($ctrl->getNextClass($this)) {
            default:
                $cmd = $ctrl->getCmd('show');
                $this->$cmd();
                break;
        }
    }

    protected function show(): void
    {
        $filter = $this->getFilter('show');
        $table_builder = $this->getTableBuilder('show');
        $table = $table_builder->getTable()->filterData($filter->getData() ?? []);

        $this->gui->ui()->mainTemplate()->setContent($filter->render() . $table->render());
    }

    protected function getFilter(string $cmd): FilterAdapterGUI
    {
        $lng = $this->domain->lng();
        $lom = $this->domain->learningObjectMetadata();

        $filter = $this->gui->filter(
            'mob_overview_filter',
            [self::class],
            $cmd
        );

        $filter = $filter->text('title', $lng->txt('mob'));
        if ($lom->copyrightHelper()->isCopyrightSelectionActive()) {
            $cp_selection = [];
            foreach ($lom->copyrightHelper()->getAllCopyrightPresets() as $copyright) {
                $cp_selection[$copyright->identifier()] = $copyright->title();
            }
            $filter = $filter->multiSelect('copyright', $lng->txt('mob_copyright'), $cp_selection);
        }
        $filter = $filter->duration('last_update', $lng->txt('mob_last_update'), true);

        return $filter;
    }

    protected function getTableBuilder(string $cmd): TableBuilder
    {
        return new TableBuilder(
            $this->domain,
            $this->gui,
            $this->data_factory,
            $this->sub_object_retrieval,
            $this,
            $cmd
        );
    }
}
