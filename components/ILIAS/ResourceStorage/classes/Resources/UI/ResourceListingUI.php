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

namespace ILIAS\Services\ResourceStorage\Resources\UI;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\Services\ResourceStorage\Resources\DataSource\TableDataSource;
use ILIAS\Services\ResourceStorage\Resources\Listing\SortDirection;
use ILIAS\Services\ResourceStorage\Resources\Listing\ViewDefinition;
use ILIAS\Services\ResourceStorage\Resources\UI\Actions\ActionGenerator;
use ILIAS\Services\ResourceStorage\Resources\UI\Actions\NullActionGenerator;
use ILIAS\UI\Component\Table\PresentationRow;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ResourceListingUI
{
    public const P_RESOURCE_ID = 'resource_id';
    public const P_PAGE = 'page';
    public const P_SORTATION = 'sort';

    private \ilUIFilterService $filter_service;
    private \ilCtrlInterface $ctrl;
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\Refinery\Factory $refinery;
    private \ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $query;
    private \ilLanguage $language;
    private array $components = [];
    private ActionGenerator $action_generator;
    private \ILIAS\ResourceStorage\Services $irss;

    public function __construct(
        private ViewDefinition $view_definition,
        private TableDataSource $data_source,
        ActionGenerator $action_generator = null
    ) {
        global $DIC;
        $this->action_generator = $action_generator ?? new NullActionGenerator();
        $this->ctrl = $DIC->ctrl();
        $this->storeRequestParameters();
        $this->language = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->filter_service = $DIC->uiService()->filter();
        $this->refinery = $DIC->refinery();
        $this->irss = $DIC->resourceStorage();
        $this->query = $DIC->http()->wrapper()->query();
        // Setup DataSource
        $this->data_source->setOffsetAndLimit(
            $this->determinePage() * $this->view_definition->getItemsPerPage(),
            $this->view_definition->getItemsPerPage()
        );
        $this->data_source->setSortDirection($this->determineSortation());
        $this->initFilters();
        $data_source->process();
        $this->initUpload();
        $this->initTable();
    }

    private function initFilters(): void
    {
        // Filters
        $filters = $this->data_source->getFilterItems($this->ui_factory, $this->language);
        if ($filters !== []) {
            $embedding_gui = $this->view_definition->getEmbeddingGui();
            $this->components[] = $filter = $this->filter_service->standard(
                $embedding_gui,
                $this->ctrl->getLinkTargetByClass($embedding_gui, $this->view_definition->getEmbeddingCmd()),
                $filters,
                array_map(
                    function ($filter): bool {
                        return true;
                    },
                    $filters
                ),
                true,
                true
            );
            $this->data_source->applyFilterValues($this->filter_service->getData($filter));
        }
    }

    private function initUpload(): void
    {
        // Currently no direct upload possible here
    }

    private function initTable(): void
    {
        // Table
        $this->components[] = $this->ui_factory->table()->presentation(
            '',
            [],
            $this->getRowMapping()
        )->withData(
            $this->data_source->getResourceIdentifications()
        )->withViewControls($this->getViewControls());

        $this->components = array_merge($this->components, $this->action_generator->getCollectedModals());
    }

    public function getRowMapping(): \Closure
    {
        return function (
            PresentationRow $row,
            ResourceIdentification $resource_identification
        ): PresentationRow {
            $resource = $this->irss->manage()->getResource($resource_identification);
            $resource_to_component = new ResourceToComponent(
                $resource,
                $this->action_generator
            );
            return $resource_to_component->getAsRowMapping()($row, $resource_identification);
        };
    }

    public function getComponents(): array
    {
        return $this->components;
    }

    protected function getViewControls(): array
    {
        $view_controls = [];
        // Sortation
        $sortations = [];
        foreach (array_keys($this->data_source->getSortationsMapping()) as $sort_id) {
            $sortations[$sort_id] = $this->language->txt('sorting_' . $sort_id);
        }

        $view_controls[] = $this->ui_factory->viewControl()->sortation($sortations)
            ->withTargetURL(
                $this->ctrl->getLinkTargetByClass(
                    $this->view_definition->getEmbeddingGui(),
                    $this->view_definition->getEmbeddingCmd()
                ),
                self::P_SORTATION
            )->withLabel($this->language->txt('sorting_' . $this->determineSortation()));

        // Pagination
        $count = $this->data_source->getFilteredAmountOfItems();
        if ($count > $this->view_definition->getItemsPerPage()) {
            $view_controls[] = $this->ui_factory->viewControl()->pagination()
                ->withTargetURL(
                    $this->ctrl->getLinkTargetByClass(
                        $this->view_definition->getEmbeddingGui(),
                        $this->view_definition->getEmbeddingCmd()
                    ),
                    self::P_PAGE
                )
                ->withCurrentPage($this->determinePage())
                ->withPageSize($this->view_definition->getItemsPerPage())
                ->withTotalEntries($count)
                ->withMaxPaginationButtons(5);
        }

        return $view_controls;
    }

    private function determinePage(): int
    {
        if ($this->query->has('cmdFilter')) { // Reset Page if Filter is applied, reset, ...
            return 0;
        }

        return $this->query->has(self::P_PAGE)
            ? $this->query->retrieve(self::P_PAGE, $this->refinery->kindlyTo()->int())
            : 0;
    }

    private function determineSortation(): int
    {
        return $this->query->has(self::P_SORTATION)
            ? $this->query->retrieve(self::P_SORTATION, $this->refinery->kindlyTo()->int())
            : array_keys($this->data_source->getSortationsMapping())[0] ?? SortDirection::BY_SIZE_DESC;
    }


    protected function storeRequestParameters(): void
    {
        $this->ctrl->saveParameterByClass($this->view_definition->getEmbeddingGui(), self::P_SORTATION);
        $this->ctrl->saveParameterByClass($this->view_definition->getEmbeddingGui(), self::P_PAGE);
    }
}
