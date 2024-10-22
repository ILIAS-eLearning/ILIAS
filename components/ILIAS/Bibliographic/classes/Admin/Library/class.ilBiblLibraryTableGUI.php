<?php

use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Psr\Http\Message\ServerRequestInterface AS HttpRequest;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer AS UIRenderer;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Component\Table\Data AS DataTable;
use ILIAS\UI\URLBuilderToken;

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


class ilBiblLibraryTableGUI implements DataRetrieval
{
    private ilAccessHandler $access;
    private ilCtrlInterface $ctrl;
    private DataFactory $data_factory;
    private HttpRequest $http_request;
    private ilLanguage $lng;
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;

    private DataTable $table;

    public function __construct(private readonly ilBiblAdminLibraryFacadeInterface $facade)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->data_factory = new DataFactory();
        $this->http_request = $DIC->http()->request();
        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->table = $this->buildTable();
    }


    public function getRenderedTable(): string
    {
        return $this->ui_renderer->render([$this->table]);
    }


    private function buildTable(): DataTable
    {
        return $this->ui_factory->table()->data(
            $this->lng->txt('bibl_settings_libraries'),
            $this->getColumns(),
            $this
        )->withActions(
            $this->getActions()
        )->withRange(
            new Range(0, 10)
        )->withOrder(
            new Order('bibl_library_name', Order::ASC)
        )->withRequest($this->http_request);
    }


    private function getColumns(): array
    {
        return [
            'bibl_library_name' => $this->ui_factory->table()->column()->text($this->lng->txt('bibl_library_name')),
            'bibl_library_url' => $this->ui_factory->table()->column()->text($this->lng->txt('bibl_library_url')),
            'bibl_library_img' => $this->ui_factory->table()->column()->text($this->lng->txt('bibl_library_img'))
        ];
    }


    private function getActions(): array
    {
        $namespace = ['lib'];

        $actions = [];
        if ($this->access->checkAccess('write', '', $this->http_request->getQueryParams()['ref_id'])) {
            $uri_edit = $this->data_factory->uri(
                ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                    ilBiblLibraryGUI::class,
                    ilBiblLibraryGUI::CMD_EDIT
                )
            );
            /**
             * @var URLBuilder      $url_builder_edit
             * @var URLBuilderToken $action_parameter_token_edit
             * @var URLBuilderToken $row_id_token_edit
             */
            [$url_builder_edit, $action_parameter_token_edit, $row_id_token_edit] = (
                new URLBuilder($uri_edit)
            )->acquireParameters(
                $namespace,
                'action',
                'ids'
            );

            $uri_delete = $this->data_factory->uri(
                ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                    ilBiblLibraryGUI::class,
                    ilBiblLibraryGUI::CMD_DELETE
                )
            );
            /**
             * @var URLBuilder      $url_builder_delete
             * @var URLBuilderToken $action_parameter_token_delete
             * @var URLBuilderToken $row_id_token_delete
             */
            [$url_builder_delete, $action_parameter_token_delete, $row_id_token_delete] = (
                new URLBuilder($uri_delete)
            )->acquireParameters(
                $namespace,
                'action',
                'ids'
            );

            $actions = [
                'edit' => $this->ui_factory->table()->action()->single(
                    $this->lng->txt('edit'),
                    $url_builder_edit->withParameter($action_parameter_token_edit, 'edit'),
                    $row_id_token_edit
                ),
                'delete' => $this->ui_factory->table()->action()->standard(
                    $this->lng->txt('delete'),
                    $url_builder_delete->withParameter($action_parameter_token_delete, 'delete'),
                    $row_id_token_delete
                )
            ];
        }

        return $actions;
    }


    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        $records = $this->getRecords($range, $order);
        foreach ($records as $record) {
            $row_id = (string) $record['bibl_library_id'];
            yield $row_builder->buildDataRow($row_id, $record);
        }
    }


    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->getRecords());
    }


    private function getRecords(Range $range = null, Order $order = null): array
    {
        $records = [];
        $libraries = $this->facade->libraryFactory()->getAll();
        foreach ($libraries as $library) {
            $records[] = [
                "bibl_library_id" => $library->getId(),
                "bibl_library_name" => $library->getName(),
                "bibl_library_url" => $library->getUrl(),
                "bibl_library_img" => $library->getImg(),
            ];
        }

        if ($order) {
            [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
            usort($records, static fn($a, $b) => $a[$order_field] <=> $b[$order_field]);
            if ($order_direction === 'DESC') {
                $records = array_reverse($records);
            }
        }
        if ($range) {
            $records = array_slice($records, $range->getStart(), $range->getLength());
        }

        return $records;
    }
}
