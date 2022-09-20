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

/**
 * Class ilBiblTableQueryInfo
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTableQueryInfo implements ilBiblTableQueryInfoInterface
{
    /**
     * @var \ilBiblTableQueryFilterInterface[]
     */
    protected array $filters = [];
    protected string $sorting_column = '';
    protected string $sorting_direction = ilBiblTableQueryInfoInterface::SORTING_ASC;
    protected int $offset = 0;
    protected int $limit = 10000;


    public function getSortingColumn(): string
    {
        return $this->sorting_column;
    }


    public function setSortingColumn(string $sorting_column): void
    {
        $this->sorting_column = $sorting_column;
    }


    public function getSortingDirection(): string
    {
        return $this->sorting_direction;
    }


    public function setSortingDirection(string $sorting_direction): void
    {
        $this->sorting_direction = $sorting_direction;
    }


    public function getOffset(): int
    {
        return $this->offset;
    }


    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }


    public function getLimit(): int
    {
        return $this->limit;
    }


    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }


    /**
     * @inheritDoc
     */
    public function addFilter(ilBiblTableQueryFilterInterface $filter): void
    {
        $this->filters[] = $filter;
    }


    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
