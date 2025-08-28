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

namespace ILIAS\Rating;

use Generator;
use ilLanguage;
use ilCtrlInterface;
use ILIAS\UI\Factory;
use ilRatingCategory;
use ILIAS\UI\URLBuilder;
use ilRatingCategoryGUI;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Table\Ordering;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table\OrderingRetrieval;
use ILIAS\UI\Component\Table\OrderingRowBuilder;

class RatingCategoryOrderingTable implements OrderingRetrieval
{
    private ?array $records = null;

    public function __construct(
        private readonly int $parent_obj_id,
        private readonly Factory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly ilCtrlInterface $ctrl,
        private readonly DataFactory $data_factory,
        private readonly ServerRequestInterface $httpRequest,
    ) {
    }

    public function getComponent(): Ordering
    {
        $query_params_namespace = ['rating', 'category', 'ordering'];
        $uri = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(ilRatingCategoryGUI::class, 'handleTableActions')
        );
        $url_builder = new URLBuilder($uri);
        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'rating_ids'
        );

        return $this->ui_factory
            ->table()
            ->ordering(
                $this,
                $uri->withParameter('cmd', 'updateOrder'),
                $this->lng->txt('drafts'),
                $this->getColumns(),
            )
            ->withId(
                'rating_category_ordering_table'
            )
            ->withRequest($this->httpRequest)
            ->withActions(
                $this->getActions(
                    $url_builder,
                    $action_parameter_token,
                    $row_id_token
                )
            );
    }

    public function getActions(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token
    ): array {
        return [
            'edit' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit'),
                $url_builder->withParameter($action_parameter_token, 'edit'),
                $row_id_token
            ),
            'delete' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('delete'),
                $url_builder->withParameter($action_parameter_token, 'confirmDelete'),
                $row_id_token
            )
        ];
    }

    public function getRows(
        OrderingRowBuilder $row_builder,
        array $visible_column_ids,
    ): Generator {
        $this->initRecords();
        foreach ($this->records as $record) {
            yield $row_builder->buildOrderingRow((string) $record['id'], $record);
        }
    }

    public function initRecords(): void
    {
        if (!$this->records) {
            $this->records = ilRatingCategory::getAllForObject($this->parent_obj_id);
        }
    }

    /**
     * @return array{
     *     title: \ILIAS\UI\Component\Table\Column\Text,
     *     description: \ILIAS\UI\Component\Table\Column\Text
     * }
     */
    private function getColumns(): array
    {
        return [
            'title' => $this->ui_factory->table()->column()->text($this->lng->txt('title')),
            'description' => $this->ui_factory->table()->column()->text($this->lng->txt('description'))
        ];
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        $this->initRecords();

        return count($this->records);
    }
}
