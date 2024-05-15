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

use ILIAS\UI\Component\Input\ViewControl\FieldSelection;
use ILIAS\UI\Component\Table\Column\Column;

trait TableViewControlFieldSelection
{
    /**
     * @var string[]
     */
    protected ?array $selected_optional_column_ids = null;

    /**
     * @param array<string, Column> $columns
     */
    protected function initViewControlFieldSelection(array $columns): void
    {
        $this->selected_optional_column_ids = $this->filterVisibleColumnIds($columns);
    }

    protected function getViewControlFieldSelection(): ?FieldSelection
    {
        $optional_cols = $this->getOptionalColumns();
        if ($optional_cols === []) {
            return null;
        }

        return $this->view_control_factory
            ->fieldSelection(array_map(
                static fn($c): string => $c->getTitle(),
                $optional_cols
            ))
            ->withValue($this->getSelectedOptionalColumns());
    }

    /**
     * @param array<string, Column> $columns
     * @return array<string>
     */
    protected function filterVisibleColumnIds(array $columns): array
    {
        return array_keys(
            array_filter(
                $columns,
                static fn($c): bool => $c->isInitiallyVisible()
            )
        );
    }

    /**
     * @param string[] $selected_optional_column_ids
     */
    public function withSelectedOptionalColumns(?array $selected_optional_column_ids): static
    {
        $clone = clone $this;
        $clone->selected_optional_column_ids = $selected_optional_column_ids;
        return $clone;
    }

    /**
     * @return string[]
     */
    public function getSelectedOptionalColumns(): array
    {
        if (is_null($this->selected_optional_column_ids)) {
            return array_keys($this->getInitiallyVisibleColumns());
        }
        return $this->selected_optional_column_ids;
    }

    /**
     * @return array<int, Column>
     */
    protected function getOptionalColumns(): array
    {
        return array_filter(
            $this->getColumns(),
            static fn($c): bool => $c->isOptional()
        );
    }

    /**
     * @return array<int, Column>
     */
    protected function getInitiallyVisibleColumns(): array
    {
        return array_filter(
            $this->getOptionalColumns(),
            static fn($c): bool => $c->isInitiallyVisible()
        );
    }

    /**
     * @return array<string, Column>
     */
    public function getVisibleColumns(): array
    {
        $visible_optional_columns = $this->getSelectedOptionalColumns();
        return array_filter(
            $this->getColumns(),
            fn(Column $col, string $col_id): bool => !$col->isOptional() || in_array($col_id, $visible_optional_columns, true),
            ARRAY_FILTER_USE_BOTH
        );
    }


}
