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
use ILIAS\UI\Component\Table\Action\Action;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\JavaScriptBindable as JSBindable;

abstract class AbstractTable extends Table implements JSBindable
{
    use JavaScriptBindable;

    /**
     * @var array<string, Column>
     */
    protected $columns = [];

    /**
     * @var array<string, Action>
     */
    protected $actions_single = [];

    /**
     * @var array<string, Action>
     */
    protected $actions_multi = [];

    /**
     * @var array<string, Action>
     */
    protected $actions_std = [];

    protected Signal $multi_action_signal;
    protected Signal $selection_signal;
    protected Signal $async_action_signal;
    protected ?ServerRequestInterface $request = null;


    /**
     * @param array<string, Column> $columns
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        string $title,
        array $columns
    ) {
        $this->checkArgListElements('columns', $columns, [Column::class]);
        if ($columns === []) {
            throw new \InvalidArgumentException('cannot construct a table without columns.');
        }

        parent::__construct($title);
        $this->multi_action_signal = $signal_generator->create();
        $this->selection_signal = $signal_generator->create();
        $this->async_action_signal = $signal_generator->create();
        $this->columns = $this->enumerateColumns($columns);
    }

    /**
     * @param array<string, Column> $columns
     * @return array<string, Column>
     */
    private function enumerateColumns(array $columns): array
    {
        $ret = [];
        $idx = 0;
        foreach ($columns as $id => $col) {
            $ret[$id] = $col->withIndex($idx++);
        }
        return $ret;
    }

    /**
     * @return array<string, Column>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @inheritdoc
     */
    public function withActions(array $actions): static
    {
        $this->checkArgListElements('actions', $actions, [T\Action\Action::class]);
        $clone = clone $this;

        foreach ($actions as $id => $action) {
            switch (true) {
                case ($action instanceof T\Action\Single):
                    $clone->actions_single[$id] = $action;
                    break;
                case ($action instanceof T\Action\Multi):
                    $clone->actions_multi[$id] = $action;
                    break;
                case ($action instanceof T\Action\Standard):
                    $clone->actions_std[$id] = $action;
                    break;
            }
        }
        return $clone;
    }

    public function withRequest(ServerRequestInterface $request): static
    {
        $clone = clone $this;
        $clone->request = $request;
        return $clone;
    }
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    public function getMultiActionSignal(): Signal
    {
        return $this->multi_action_signal;
    }

    public function getSelectionSignal(): Signal
    {
        return $this->selection_signal;
    }

    public function getAsyncActionSignal(): Signal
    {
        return $this->async_action_signal;
    }

    public function hasSingleActions(): bool
    {
        return $this->getSingleActions() !== [];
    }

    public function hasMultiActions(): bool
    {
        return $this->getMultiActions() !== [];
    }

    /**
     * @return array<string, T\Action\Action>
     */
    public function getMultiActions(): array
    {
        return array_merge($this->actions_multi, $this->actions_std);
    }

    /**
     * @return array<string, T\Action\Action>
     */
    public function getSingleActions(): array
    {
        return array_merge($this->actions_single, $this->actions_std);
    }

    /**
     * @return array<string, T\Action\Action>
     */
    public function getAllActions(): array
    {
        return array_merge($this->actions_single, $this->actions_multi, $this->actions_std);
    }

    public function getColumnCount(): int
    {
        return count($this->columns);
    }
}
