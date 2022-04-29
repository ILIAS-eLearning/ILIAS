<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilActivity.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilNode.php';

/**
 * Class ilScriptActivity
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilScriptActivity implements ilActivity, ilWorkflowEngineElement
{
    /** @var ilWorkflowEngineElement $context Holds a reference to the parent object */
    private $context;


    private $method = '';

    /** @var string $name */
    protected string $name;

    /**
     * Default constructor.
     *
     * @param ilNode $context
     */
    public function __construct(ilNode $context)
    {
        $this->context = $context;
    }

    public function setMethod($value) : void
    {
        $this->method = $value;
    }

    /**
     * Returns the value of the setting to be set.
     *
     * @see $setting_value
     *
     * @return string
     */
    public function getScript()
    {
        return $this->method;
    }

    /**
     * Executes this action according to its settings.
     * @return void
     */
    public function execute() : void
    {
        $method = $this->method;
        $return_value = $this->context->getContext()->$method($this);
        foreach ((array) $return_value as $key => $value) {
            $this->context->setRuntimeVar($key, $value);
        }
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

    public function setName($name) : void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
}
