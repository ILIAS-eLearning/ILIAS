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

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Input\ViewControl;
use ILIAS\UI\Component\Input\Container\ViewControl as ViewControlContainer;

class Ordering extends AbstractTable implements T\Ordering
{
    use TableViewControlFieldSelection;

    public const STORAGE_ID_PREFIX = self::class . '_';
    public const VIEWCONTROL_KEY_FIELDSELECTION = 'selected_optional';

    protected bool $ordering_disabled = false;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        ViewControl\Factory $view_control_factory,
        ViewControlContainer\Factory $view_control_container_factory,
        protected OrderingRowBuilder $row_builder,
        string $title,
        array $columns,
        protected T\OrderingBinding $binding,
        protected URI $target_url,
        \ArrayAccess $storage
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

    public function getDataBinding(): T\OrderingBinding
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
            return null;
        }
        $ordered = $request->getParsedBody();
        asort($ordered, SORT_NUMERIC);
        return array_keys($ordered);
    }

    /**
     * @return array<self, ViewControlContainer\ViewControl>
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

    protected function getViewControls(): ViewControlContainer\ViewControl
    {
        $view_controls = [
            self::VIEWCONTROL_KEY_FIELDSELECTION => $this->getViewControlFieldSelection(),
        ];
        $view_controls = array_filter($view_controls);
        return $this->view_control_container_factory->standard($view_controls);
    }
}
