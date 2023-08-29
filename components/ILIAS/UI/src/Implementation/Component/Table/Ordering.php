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
use ILIAS\UI\Component\Table\Column\Column;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Ordering extends AbstractTable implements T\Ordering
{
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        protected OrderingRowBuilder $row_builder,
        string $title,
        array $columns,
        protected T\OrderingBinding $binding
    ) {
        parent::__construct(
            $signal_generator,
            $title,
            $columns
        );
    }

    public function getRowBuilder(): OrderingRowBuilder
    {
        return $this->row_builder
            ->withMultiActionsPresent($this->hasMultiActions())
            ->withSingleActions($this->getSingleActions())
            ->withVisibleColumns($this->getColumns());
    }

    public function getDataBinding(): T\OrderingBinding
    {
        return $this->binding;
    }
}
