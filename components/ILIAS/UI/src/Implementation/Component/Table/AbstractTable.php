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
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractTable extends Table implements C\JavaScriptBindable
{
    use I\JavaScriptBindable;

    /**
     * @var array<string, I\Table\Column\Column>
     */
    protected $columns = [];

    /**
     * @var array<string, I\Table\Action\Action>
     */
    protected $actions_single = [];

    /**
     * @var array<string, I\Table\Action\Action>
     */
    protected $actions_multi = [];

    /**
     * @var array<string, I\Table\Action\Action>
     */
    protected $actions_std = [];

    protected ?string $id = null;
    protected C\Signal $multi_action_signal;
    protected C\Signal $selection_signal;
    protected C\Signal $async_action_signal;
    protected ?ServerRequestInterface $request = null;

    /**
     * @param array<string, I\Table\Column\Column> $columns
     */
    public function __construct(
        I\SignalGeneratorInterface $signal_generator,
        protected I\Input\ViewControl\Factory $view_control_factory,
        protected I\Input\Container\ViewControl\Factory $view_control_container_factory,
        protected \ILIAS\UI\Storage $storage,
        string $title,
        array $columns,
    ) {
        $this->checkArgListElements('columns', $columns, [I\Table\Column\Column::class]);
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
     * @param array<string, I\Table\Column\Column> $columns
     * @return array<string, I\Table\Column\Column>
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
     * @return array<string, I\Table\Column\Column>
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
        // check actions against internal interface, will not be
        // deletaged to rendering chain and cannot be customised
        $this->checkArgListElements('actions', $actions, [I\Table\Action\Action::class]);
        $clone = clone $this;

        foreach ($actions as $id => $action) {
            switch (true) {
                case ($action instanceof I\Table\Action\Single):
                    $clone->actions_single[$id] = $action;
                    break;
                case ($action instanceof I\Table\Action\Multi):
                    $clone->actions_multi[$id] = $action;
                    break;
                case ($action instanceof I\Table\Action\Standard):
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

    public function getMultiActionSignal(): C\Signal
    {
        return $this->multi_action_signal;
    }

    public function getSelectionSignal(): C\Signal
    {
        return $this->selection_signal;
    }

    public function getAsyncActionSignal(): C\Signal
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
     * @return array<string, I\Table\Action\Action>
     */
    public function getMultiActions(): array
    {
        return array_merge($this->actions_multi, $this->actions_std);
    }

    /**
     * @return array<string, I\Table\Action\Action>
     */
    public function getSingleActions(): array
    {
        return array_merge($this->actions_single, $this->actions_std);
    }

    /**
     * @return array<string, I\Table\Action\Action>
     */
    public function getAllActions(): array
    {
        return array_merge($this->actions_single, $this->actions_multi, $this->actions_std);
    }

    public function getColumnCount(): int
    {
        return count($this->columns);
    }

    protected function getStorageData(): ?array
    {
        if (null !== ($storage_id = $this->getStorageId())) {
            return $this->storage[$storage_id] ?? null;
        }
        return null;
    }

    protected function setStorageData(array $data): void
    {
        if (null !== ($storage_id = $this->getStorageId())) {
            $this->storage[$storage_id] = $data;
        }
    }

    public function withId(string $id): static
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    protected function getStorageId(): ?string
    {
        if (null !== ($id = $this->getId())) {
            return static::STORAGE_ID_PREFIX . $id;
        }
        return null;
    }

    protected function getId(): ?string
    {
        return $this->id;
    }

    protected function applyValuesToViewcontrols(
        I\Input\Container\ViewControl\ViewControl $view_controls,
        ServerRequestInterface $request
    ): I\Input\Container\ViewControl\ViewControl {
        $stored_values = new I\Input\ArrayInputData($this->getStorageData() ?? []);
        $view_controls = $view_controls
            ->withStoredInput($stored_values)
            ->withRequest($request);
        $this->setStorageData($view_controls->getComponentInternalValues());
        return $view_controls;
    }
}
