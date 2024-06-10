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

namespace ILIAS\Test\Filter;

use ILIAS\UI\Component\Input\Container\Filter\FilterInput;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;

class FilterBuilder
{
    private array $filters = [];
    private bool $is_active = true;
    private bool $is_expanded = true;

    public function __construct(
        private readonly string $filterId
    ) {
    }

    public function addFilter(
        string $name,
        FilterInput $filter_input,
        bool $render_per_default = true
    ): self {
        $this->filters[$name] = [$filter_input, $render_per_default];
        return $this;
    }

    public function setIsActive(bool $is_active): FilterBuilder
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function setIsExpanded(bool $is_expanded): FilterBuilder
    {
        $this->is_expanded = $is_expanded;

        return $this;
    }

    public function getUIComponent(\ilUIService $ui_service, string $action): Filter
    {
        $filter_inputs = [];
        $is_input_initially_rendered = [];

        foreach ($this->filters as $filter_id => $filter) {
            [$filter_inputs[$filter_id], $is_input_initially_rendered[$filter_id]] = $filter;
        }

        return $ui_service->filter()->standard(
            $this->filterId,
            $action,
            $filter_inputs,
            $is_input_initially_rendered,
            $this->is_active,
            $this->is_expanded
        );
    }

    public function applyFilter(
        array &$select_expressions,
        array &$join_expressions,
        array &$where_expressions
    ): void {

    }

}
