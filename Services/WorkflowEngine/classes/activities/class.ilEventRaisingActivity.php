<?php

declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilEventRaisingActivity
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilEventRaisingActivity implements ilActivity, ilWorkflowEngineElement
{
    /** @var ilWorkflowEngineElement $context Holds a reference to the parent object */
    private $context;

    /** Type of the event to be raised. */
    protected string $event_type = '';

    /** Name of the event to be raised. */
    protected string $event_name = '';

    /** @var array $fixed_params Fixed params that are always to be sent with the event. Will be overriden by context. */
    protected array $fixed_params = [];

    protected ?string $name;

    public function __construct(ilNode $a_context)
    {
        $this->context = $a_context;
        $this->event_type = 'Services/WorkflowEngine';
        $this->event_name = 'nondescript';
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addFixedParam(string $key, $value): void
    {
        $this->fixed_params[] = ['key' => $key, 'value' => $value];
    }

    public function getEventName(): string
    {
        return $this->event_name;
    }

    public function setEventName(string $event_name): void
    {
        $this->event_name = $event_name;
    }

    public function getEventType(): string
    {
        return $this->event_type;
    }

    public function setEventType(string $event_type): void
    {
        $this->event_type = $event_type;
    }

    /**
     * Executes this action according to its settings.
     * @return void
     * @todo Use exceptions / internal logging.
     */
    public function execute(): void
    {
        global $DIC;
        /** @var ilAppEventHandler $ilAppEventHandler */
        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        $ilAppEventHandler->raise(
            $this->event_type,
            $this->event_name,
            $this->getParamsArray()
        );
    }

    /**
     * @return array
     */
    public function getParamsArray(): array
    {
        // TODO: Get logic for getting values from incoming data associations.

        $params = [];
        $params[] = ['key' => 'context', 'value' => $this];

        return array_merge($this->fixed_params, $params);
    }

    /**
     * Returns a reference to the parent node.
     *
     * @return ilNode Reference to the parent node.
     */
    public function getContext()
    {
        return $this->context;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
