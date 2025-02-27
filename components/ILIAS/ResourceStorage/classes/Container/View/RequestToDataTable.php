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

namespace ILIAS\components\ResourceStorage\Container\View;

use ILIAS\components\ResourceStorage\Container\Wrapper\Dir;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Dropdown\Standard;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Factory;
use ILIAS\components\ResourceStorage\Container\DataProvider\TableDataProvider;
use ILIAS\Data\Range;
use ILIAS\HTTP\Services;
use ILIAS\components\ResourceStorage\URLSerializer;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Signal;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RequestToDataTable implements RequestToComponents, DataRetrieval
{
    use Formatter;
    use URLSerializer;

    public const F_TITLE = 'title';
    public const F_SIZE = 'size';
    public const F_TYPE = 'type';
    public const F_MODIFICATION_DATE = 'create_date';
    public const FIELD_TITLE = 'title';
    public const HOME = 'HOME';
    private \ILIAS\Data\Factory $data_factory;
    private \ILIAS\ResourceStorage\Services $irss;
    private Renderer $ui_renderer;
    private \ilCtrlInterface $ctrl;
    /**
     * @var ActionBuilder\SingleAction[]
     */
    private array $actions;

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
        $this->data_factory = new \ILIAS\Data\Factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ctrl = $DIC->ctrl();
        $this->actions = $this->action_builder->getActionProvider()->getSingleActions(
            $this->request
        );
    }

    protected function buildTopActions(): Standard
    {
        $buttons = [];
        if ($this->request->canUserAdministrate()) {
            foreach ($this->action_builder->getActionProvider()->getTopActions() as $top_action) {
                if ($top_action->getAction() instanceof Signal) {
                    $button = $this->ui_factory->button()->shy(
                        $top_action->getLabel(),
                        '#'
                    )->withOnClick($top_action->getAction());
                } else {
                    $button = $this->ui_factory->button()->shy(
                        $top_action->getLabel(),
                        (string) $top_action->getAction()
                    );
                }

                $buttons[] = $button;
            }
        }
        return $this->ui_factory->dropdown()->standard($buttons);
    }

    protected function getBreadcrumbs(): \Generator
    {
        $get_action = function (string $path_inside_zip): string {
            $this->ctrl->setParameterByClass(
                \ilContainerResourceGUI::class,
                Request::P_PATH,
                $this->hash($path_inside_zip)
            );
            return $this->ctrl->getLinkTargetByClass(
                \ilContainerResourceGUI::class,
                \ilContainerResourceGUI::CMD_INDEX
            );
        };

        $links = [];
        // Link to Root Directory
        $links[] = $this->ui_factory->link()->standard(
            $this->language->txt('home_directory'),
            $get_action('./')
        );

        // Links to current directory and all parent directories
        if ($this->request->getPath() !== './') {
            $directories = array_filter(
                explode('/', $this->request->getPath()),
                static fn(string $part): bool => $part !== ''
            );

            foreach ($directories as $i => $directory) {
                $path_inside_zip = rtrim(
                    implode('/', array_slice($directories, 0, $i + 1)),
                    '/'
                ) . '/';
                $links[] = $this->ui_factory->link()->standard(
                    $directory,
                    $get_action($path_inside_zip)
                );
            }
        }
        yield $this->ui_factory->divider()->horizontal();

        yield $this->ui_factory->breadcrumbs($links);
    }

    public function getComponents(): \Generator
    {
        // build top actions here
        $dropdown = $this->buildTopActions();

        yield $this->ui_factory->panel()->standard(
            $this->language->txt('title_manage_container'),
            array_merge(
                iterator_to_array($this->upload_builder->getDropZone()),
                iterator_to_array($this->getBreadcrumbs()),
            )
        )->withActions($dropdown);

        yield $this->buildTable();
    }

    /**
     * @return Data
     */
    protected function buildTable(): Data
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
                self::F_MODIFICATION_DATE => $this->ui_factory->table()->column()->date(
                    $this->language->txt(self::F_MODIFICATION_DATE),
                    $this->data_factory->dateFormat()->germanLong()
                )->withIsSortable(true),
                self::F_TYPE => $this->ui_factory->table()->column()->text(
                    $this->language->txt(self::F_TYPE)
                )->withIsSortable(true),
            ],
            $this
        )->withRequest(
            $this->http->request()
        )->withActions(
            $this->action_builder->getActions()
        )->withRange(
            new Range(0, $this->request->getItemsPerPage())
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

        $regex_storage = [];

        $entries = $this->data_provider->getEntries();
        // cut entries
        $entries = array_slice(
            $entries,
            $range->getStart(),
            $range->getLength()
        );

        foreach ($entries as $entry) {
            $is_dir = $entry instanceof Dir;
            $path_inside_zip = $entry->getPathInsideZIP();

            $entry_name = trim((string) $entry, '/');

            // needed for links in table
            $this->ctrl->setParameterByClass(
                \ilContainerResourceGUI::class,
                Request::P_PATH,
                $this->hash($path_inside_zip)
            );

            $action = $this->ctrl->getLinkTargetByClass(
                \ilContainerResourceGUI::class,
                \ilContainerResourceGUI::CMD_INDEX
            );

            $title = $is_dir
                ? $this->ui_renderer->render(
                    $this->ui_factory->link()->standard($entry_name, $action)
                )
                : $entry_name;

            $data_row = $row_builder->buildDataRow(
                $this->hash($entry->getPathInsideZIP()),
                [
                    self::F_TITLE => $title,
                    self::F_SIZE => $is_dir ? '' : $this->formatSize($entry->getSize()),
                    self::F_TYPE => $is_dir ? '' : $entry->getMimeType(),
                    self::F_MODIFICATION_DATE => $entry->getModificationDate(),
                ]
            );

            foreach ($this->actions as $key => $single_action) {
                if ($is_dir && !$single_action->supportsDirectories()) {
                    $data_row = $data_row->withDisabledAction($key);
                }

                if ($single_action->getSupportedMimeTypes() !== ['*']) {
                    if ($is_dir) {
                        $data_row = $data_row->withDisabledAction($key);
                    } else {
                        if (isset($regex_storage[$key])) {
                            $regex = $regex_storage[$key];
                        } else {
                            $mime_type_quoted = [];
                            foreach ($single_action->getSupportedMimeTypes() as $mime_type) {
                                $mime_type_quoted[] = str_replace('*', '.*', preg_quote((string) $mime_type, '/'));
                            }

                            $regex_storage[$key] = $regex = implode('|', $mime_type_quoted);
                        }
                        if (!preg_match("/($regex)/", $entry->getMimeType())) {
                            $data_row = $data_row->withDisabledAction($key);
                        }
                    }
                }
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
        $this->data_provider->getViewRequest()->setPage((int) round($start / $length, 0, \RoundingMode::HalfTowardsZero));
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
            case self::F_MODIFICATION_DATE . '_' . Order::ASC:
                $this->data_provider->getViewRequest()->setSortation(Request::BY_CREATION_DATE_ASC);
                break;
            case self::F_MODIFICATION_DATE . '_' . Order::DESC:
                $this->data_provider->getViewRequest()->setSortation(Request::BY_CREATION_DATE_DESC);
                break;
            case self::F_TYPE . '_' . Order::ASC:
                $this->data_provider->getViewRequest()->setSortation(Request::BY_TYPE_ASC);
                break;
            case self::F_TYPE . '_' . Order::DESC:
                $this->data_provider->getViewRequest()->setSortation(Request::BY_TYPE_DESC);
                break;
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return $this->data_provider->getTotal();
    }
}
