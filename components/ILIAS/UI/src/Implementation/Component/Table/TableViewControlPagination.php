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

use ILIAS\UI\Implementation\Component\Input\ViewControl\Pagination;
use ILIAS\Data\Range;

trait TableViewControlPagination
{
    protected int $number_of_rows = 800;
    protected ?Range $range = null;

    protected function initViewControlpagination(): void
    {
        $this->range = $this->data_factory->range(0, $this->number_of_rows);
    }

    protected function getViewControlPagination(?int $total_count = null): ?Pagination
    {
        $smallest_option = current(Pagination::DEFAULT_LIMITS);
        if (is_null($total_count) || $total_count >= $smallest_option) {
            $range = $this->getRange();
            return
                $this->view_control_factory->pagination()
                    ->withTotalCount($total_count)
                    ->withValue([
                        Pagination::FNAME_OFFSET => $range->getStart(),
                        Pagination::FNAME_LIMIT => $range->getLength()
                    ]);
        }
        return null;
    }

    public function withNumberOfRows(int $number_of_rows): self
    {
        $clone = clone $this;
        $clone->number_of_rows = $number_of_rows;
        return $clone;
    }

    public function getNumberOfRows(): int
    {
        return $this->number_of_rows;
    }

    public function withRange(?Range $range): self
    {
        $clone = clone $this;
        $clone->range = $range;
        return $clone;
    }

    public function getRange(): Range
    {
        return $this->range ?? $this->data_factory->range(0, $this->number_of_rows);
    }
}
