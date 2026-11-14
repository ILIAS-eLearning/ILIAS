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
use ILIAS\Data\URI;

class Ordering extends AbstractTable implements C\Table\Ordering
{
    use TableViewControlFieldSelection;

    public const STORAGE_ID_PREFIX = self::class . '_';
    public const VIEWCONTROL_KEY_FIELDSELECTION = 'selected_optional';

    protected bool $ordering_disabled = false;

    public function __construct(
        I\SignalGeneratorInterface $signal_generator,
        I\Input\ViewControl\Factory $view_control_factory,
        I\Input\Container\ViewControl\Factory $view_control_container_factory,
        protected OrderingRowBuilder $row_builder,
        string $title,
        array $columns,
        protected C\Table\OrderingRetrieval $binding,
        protected URI $target_url,
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
    }

    public function getRowBuilder(): OrderingRowBuilder
    {
        return $this->row_builder
            ->withMultiActionsPresent($this->hasMultiActions())
            ->withSingleActions($this->getSingleActions())
            ->withVisibleColumns($this->getVisibleColumns());
    }

    public function getDataBinding(): C\Table\OrderingRetrieval
    {
        return $this->binding;
    }

    public function withOrderingDisabled(bool $flag): self
    {
        $clone = clone $this;
        $clone->ordering_disabled = $flag;
        return $clone;
    }

    public function isOrderingDisabled(): bool
    {
        return $this->ordering_disabled;
    }

    public function getTargetURL(): ?URI
    {
        return $this->target_url;
    }

    public function getData(): array
    {
        if (!$request = $this->getRequest()) {
            return [];
        }
        $ordered = $request->getParsedBody();
        asort($ordered, SORT_NUMERIC);
        return array_keys($ordered);
    }

    /**
     * @return array<self, I\Input\Container\ViewControl\ViewControl>
     */
    public function applyViewControls(): array
    {
        $table = $this;
        $view_controls = $this->getViewControls();

        if ($request = $this->getRequest()) {
            $view_controls = $this->applyValuesToViewcontrols($view_controls, $request);
            $data = $view_controls->getData();
            $table = $table
                ->withSelectedOptionalColumns($data[self::VIEWCONTROL_KEY_FIELDSELECTION] ?? null);
        }

        return [
            $table,
            $view_controls
        ];
    }

    protected function getViewControls(): I\Input\Container\ViewControl\ViewControl
    {
        $view_controls = [
            self::VIEWCONTROL_KEY_FIELDSELECTION => $this->getViewControlFieldSelection(),
        ];
        $view_controls = array_filter($view_controls);
        return $this->view_control_container_factory->standard($view_controls);
    }
}
