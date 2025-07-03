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

namespace ILIAS\Repository\Table;

use ILIAS\Repository\RetrievalInterface;

abstract class CommonTableBuilder
{
    protected TableAdapterGUI $table;

    public function __construct(
        protected object $parent_gui,
        protected string $parent_cmd,
        bool $numeric_ids = true
    ) {
        $this->table = new TableAdapterGUI(
            $this->getId(),
            $this->getTitle(),
            $this->getRetrieval(),
            $parent_gui,
            $parent_cmd,
            $this->getNamespace(),
            $this->getOrderingCommand(),
            \Closure::fromCallable([$this, 'activeAction']),
            \Closure::fromCallable([$this, 'transformRow']),
            $numeric_ids
        );
        $this->table = $this->build($this->table);
    }

    abstract protected function getId(): string;

    abstract protected function getTitle(): string;

    abstract protected function getRetrieval(): RetrievalInterface;

    protected function getNamespace(): string
    {
        return "";
    }

    protected function getOrderingCommand(): string
    {
        return "";
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        return true;
    }

    /**
     * transform raw data array to table row data array
     */
    protected function transformRow(array $data_row): array
    {
        return $data_row;
    }

    abstract protected function build(TableAdapterGUI $table): TableAdapterGUI;

    final public function getTable(
    ): TableAdapterGUI {
        return $this->table;
    }
}
