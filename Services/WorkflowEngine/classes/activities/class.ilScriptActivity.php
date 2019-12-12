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

    /** @var string $method */
    private $method = '';

    /** @var string $name */
    protected $name;

    /**
     * Default constructor.
     *
     * @param ilNode $context
     */
    public function __construct(ilNode $context)
    {
        $this->context = $context;
    }

    /**
     * @param string $value
     */
    public function setMethod($value)
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
     *
     * @return void
     */
    public function execute()
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

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
