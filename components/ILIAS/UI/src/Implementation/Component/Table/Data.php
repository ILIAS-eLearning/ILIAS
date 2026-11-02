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

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

class Data extends AbstractTable implements C\Table\Data
{
    use I\JavaScriptBindable;
    use TableViewControlFieldSelection;
    use TableViewControlPagination;
    use TableViewControlOrdering;

    public const STORAGE_ID_PREFIX = self::class . '_';
    public const VIEWCONTROL_KEY_PAGINATION = 'range';
    public const VIEWCONTROL_KEY_ORDERING = 'order';
    public const VIEWCONTROL_KEY_FIELDSELECTION = 'selection';
    public const VIEWCONTROL_KEY_ADDITIONAL = 'additional';

    protected mixed $filter = null;
    protected mixed $additional_parameters = null;
    protected mixed $additional_viewcontrol_data = null;
    protected C\Input\Container\ViewControl\ViewControlInput|null $additional_view_control = null;

    /**
     * @param array<string, I\Table\Column\Column> $columns
     */
    public function __construct(
        I\SignalGeneratorInterface $signal_generator,
        I\Input\ViewControl\Factory $view_control_factory,
        I\Input\Container\ViewControl\Factory $view_control_container_factory,
        protected \ILIAS\Data\Factory $data_factory,
        protected DataRowBuilder $data_row_builder,
        string $title,
        array $columns,
        protected C\Table\DataRetrieval $data_retrieval,
        \ILIAS\UI\Storage $storage
    ) {
        parent::__construct(
            $signal_generator,
            $view_control_factory,
            $view_control_container_factory,
            $storage,
            $title,
            $columns
        );
        $this->initViewControlFieldSelection($columns);
        $this->initViewControlOrdering();
        $this->initViewControlpagination();
    }

    public function getDataRetrieval(): C\Table\DataRetrieval
    {
        return $this->data_retrieval;
    }

    public function withFilter(mixed $filter): self
    {
        $clone = clone $this;
        $clone->filter = $filter;
        return $clone;
    }

    public function getFilter(): mixed
    {
        return $this->filter;
    }

    public function withAdditionalParameters(mixed $additional_parameters): self
    {
        $clone = clone $this;
        $clone->additional_parameters = $additional_parameters;
        return $clone;
    }

    public function getAdditionalParameters(): mixed
    {
        return $this->additional_parameters;
    }


    protected function withAdditionalViewControlData(mixed $additional_viewcontrol_data): self
    {
        $clone = clone $this;
        $clone->additional_viewcontrol_data = $additional_viewcontrol_data;
        return $clone;
    }

    public function getAdditionalViewControlData(): mixed
    {
        return $this->additional_viewcontrol_data;
    }

    public function getRowBuilder(): DataRowBuilder
    {
        return $this->data_row_builder
            ->withMultiActionsPresent($this->hasMultiActions())
            ->withSingleActions($this->getSingleActions())
            ->withVisibleColumns($this->getVisibleColumns());
    }

    /**
     * @return array{0: self, 1: I\Input\Container\ViewControl\ViewControl}
     */
    public function applyViewControls(
        mixed $filter_data,
        mixed $additional_parameters = []
    ): array {
        $request = $this->getRequest();
        if ($request === null) {
            return [
                $this,
                $this->getViewControls()
            ];
        }

        $additional_viewcontrol_data = array_filter(
            $this->applyValuesToViewcontrols($this->getViewControls(null), $request)->getData(),
            fn($key) => !in_array($key, [self::VIEWCONTROL_KEY_PAGINATION, self::VIEWCONTROL_KEY_ORDERING, self::VIEWCONTROL_KEY_FIELDSELECTION]),
            ARRAY_FILTER_USE_KEY
        );

        $total_count = $this->getDataRetrieval()->getTotalRowCount($additional_viewcontrol_data, $filter_data, $additional_parameters);
        $view_controls = $this->getViewControls($total_count);

        $data = $this->applyValuesToViewcontrols($view_controls, $request)->getData();
        $range = $data[self::VIEWCONTROL_KEY_PAGINATION];
        $range = ($range instanceof Range) ? $range : null;
        $order = $data[self::VIEWCONTROL_KEY_ORDERING];
        $order = ($order instanceof Order) ? $order : null;

        if ($range instanceof Range) {
            $range = $range->withStart($range->getStart() < $total_count ? $range->getStart() : 0);
            $range = $range->croppedTo($total_count ?? PHP_INT_MAX);
        }

        $table = $this
            ->withRange($range)
            ->withOrder($order)
            ->withSelectedOptionalColumns($data[self::VIEWCONTROL_KEY_FIELDSELECTION] ?? null)
            ->withAdditionalViewControlData($additional_viewcontrol_data);

        # This retrieves the view controls that should be displayed
        $view_controls = $table->applyValuesToViewcontrols($table->getViewControls($total_count), $request);

        return [
            $table,
            $view_controls
        ];
    }

    protected function getViewControls(?int $total_count = null): I\Input\Container\ViewControl\ViewControl
    {
        $view_controls = [
            self::VIEWCONTROL_KEY_PAGINATION => $this->getViewControlPagination($total_count),
            self::VIEWCONTROL_KEY_ORDERING => $this->getViewControlOrdering($total_count),
            self::VIEWCONTROL_KEY_FIELDSELECTION => $this->getViewControlFieldSelection(),
            $this->additional_view_control
        ];
        $view_controls = array_filter($view_controls);
        $vc = $this->view_control_container_factory->standard($view_controls);
        if ($this->getId() !== null) {
            $vc = $vc->withDedicatedName('vc' . $this->getId());
        }
        return $vc;
    }

    public function withAdditionalViewControl(
        C\Input\Container\ViewControl\ViewControlInput $view_control
    ): self {
        $clone = clone $this;
        $clone->additional_view_control = $view_control
            ->withDedicatedName(self::VIEWCONTROL_KEY_ADDITIONAL);
        return $clone;
    }

}
