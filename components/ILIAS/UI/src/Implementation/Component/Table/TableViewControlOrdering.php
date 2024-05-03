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

use ILIAS\UI\Component\Input\ViewControl\Sortation;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\Data\Order;

trait TableViewControlOrdering
{
    protected ?Order $order = null;

    protected function initViewControlOrdering(): void
    {
        $this->order = $this->data_factory->order($this->initialOrder(), Order::ASC);
    }

    private function initialOrder(): string
    {
        $visible_cols = $this->getVisibleColumns();
        $sortable_visible_cols = array_filter(
            $visible_cols,
            static fn($c): bool => $c->isSortable()
        );
        if ($sortable_visible_cols === []) {
            return array_key_first($visible_cols);
        }
        return array_key_first($sortable_visible_cols);
    }

    protected function getViewControlOrdering(): ?Sortation
    {
        $sortable_visible_cols = array_filter(
            $this->getVisibleColumns(),
            static fn($c): bool => $c->isSortable()
        );

        if ($sortable_visible_cols === []) {
            return null;
        }

        $sort_options = [];
        foreach ($sortable_visible_cols as $col_id => $col) {

            $order_asc = $this->data_factory->order($col_id, Order::ASC);
            $order_desc = $this->data_factory->order($col_id, Order::DESC);

            $labels = $col->getOrderingLabels();
            $sort_options[$labels[0]] = $order_asc;
            $sort_options[$labels[1]] = $order_desc;
        }
        return $this->view_control_factory->sortation($sort_options);
    }

    public function withOrder(?Order $order): self
    {
        $clone = clone $this;
        $clone->order = $order;
        return $clone;
    }

    public function getOrder(): Order
    {
        return $this->order ?? $this->data_factory->order($this->initialOrder(), Order::ASC);
    }
}
