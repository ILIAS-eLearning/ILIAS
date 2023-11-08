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

namespace ILIAS\components\ResourceStorage_\Collections\View;

use ILIAS\UI\Factory;
use ILIAS\components\ResourceStorage_\Collections\DataProvider\TableDataProvider;
use ILIAS\components\ResourceStorage_\Collections\DataProvider\DataTableDataProviderAdapter;
use ILIAS\Data\Range;
use ILIAS\HTTP\Services;
use ILIAS\components\ResourceStorage_\BinToHexSerializer;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRetrieval;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RequestToDataTable implements RequestToComponents, DataRetrieval
{
    use RIDHelper;
    use Formatter;
    use BinToHexSerializer;

    public const F_TITLE = 'title';
    public const F_SIZE = 'size';
    public const F_TYPE = 'type';
    public const F_CREATION_DATE = 'create_date';
    public const FIELD_TITLE = 'title';
    private \ILIAS\Data\Factory $data_factory;
    private \ILIAS\ResourceStorage\Services $irss;

    public function __construct(
        private Request $request,
        private Factory $ui_factory,
        private \ilLanguage $language,
        private Services $http,
        private TableDataProvider $data_provider,
        private ActionBuilder $action_builder,
        private ViewControlBuilder $view_control_builder,
        private UploadBuilder $upload_builder
    ) {
        global $DIC;
        $this->irss = $DIC->resourceStorage();
        $this->data_factory = new \ILIAS\Data\Factory();
    }

    public function getComponents(): \Generator
    {
        yield from $this->upload_builder->getDropZone();

        yield $this->ui_factory->panel()->standard(
            $this->request->getTitle(),
            $this->buildTable()
        );
    }

    /**
     * @return \ILIAS\UI\Component\Table\Data
     */
    protected function buildTable(): \ILIAS\UI\Component\Table\Data
    {
        return $this->ui_factory->table()->data(
            $this->request->getTitle(), // we already have the title in the panel
            [
                self::F_TITLE => $this->ui_factory->table()->column()->text(
                    $this->language->txt(self::F_TITLE)
                )->withIsSortable(true),
                self::F_SIZE => $this->ui_factory->table()->column()->text(
                    $this->language->txt(self::F_SIZE)
                )->withIsSortable(true),
                self::F_CREATION_DATE => $this->ui_factory->table()->column()->date(
                    $this->language->txt(self::F_CREATION_DATE),
                    $this->data_factory->dateFormat()->germanLong()
                )->withIsSortable(true),
                self::F_TYPE => $this->ui_factory->table()->column()->text(
                    $this->language->txt(self::F_TYPE)
                )->withIsSortable(false),
            ],
            $this
        )->withRequest(
            $this->http->request()
        )->withActions(
            $this->action_builder->getActions()
        )->withNumberOfRows(
            $this->request->getItemsPerPage()
        );
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        $this->initSortingAndOrdering($range, $order);

        foreach ($this->data_provider->getIdentifications() as $resource_identification) {
            $information = $this->getResourceInfo($resource_identification);
            $mime_type = $information->getMimeType();

            $data_row = $row_builder->buildDataRow(
                $this->hash($resource_identification->serialize()),
                [
                    self::F_TITLE => $information->getTitle(),
                    self::F_SIZE => $this->formatSize($information->getSize()),
                    self::F_CREATION_DATE => $information->getCreationDate(),
                    self::F_TYPE => $information->getMimeType(),
                ]
            );

            if (!in_array($mime_type, ['application/zip', 'application/x-zip-compressed'])) {
                $data_row = $data_row->withDisabledAction('unzip');
            }

            yield $data_row;
        }
    }

    private function initSortingAndOrdering(Range $range, Order $order): void
    {
        $sort_field = array_keys($order->get())[0];
        $sort_direction = $order->get()[$sort_field];

        $start = $range->getStart();
        $length = $range->getLength();
        $this->data_provider->getViewRequest()->setPage((int) round($start / $length, 0, PHP_ROUND_HALF_DOWN));
        $this->data_provider->getViewRequest()->setItemsPerPage($length);

        switch ($sort_field . '_' . $sort_direction) {
            case self::F_TITLE . '_' . Order::ASC:
                $this->data_provider->getViewRequest()->setSortation(Request::BY_TITLE_ASC);
                break;
            case self::F_TITLE . '_' . Order::DESC:
                $this->data_provider->getViewRequest()->setSortation(Request::BY_TITLE_DESC);
                break;
            case self::F_SIZE . '_' . Order::ASC:
                $this->data_provider->getViewRequest()->setSortation(Request::BY_SIZE_ASC);
                break;
            case self::F_SIZE . '_' . Order::DESC:
                $this->data_provider->getViewRequest()->setSortation(Request::BY_SIZE_DESC);
                break;
            case self::F_CREATION_DATE . '_' . Order::ASC:
                $this->data_provider->getViewRequest()->setSortation(Request::BY_CREATION_DATE_ASC);
                break;
            case self::F_CREATION_DATE . '_' . Order::DESC:
                $this->data_provider->getViewRequest()->setSortation(Request::BY_CREATION_DATE_DESC);
                break;
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return $this->data_provider->getTotal();
    }
}
