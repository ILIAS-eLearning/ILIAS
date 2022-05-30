<?php declare(strict_types=1);

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
 * Class ilScriptActivity
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilScriptActivity implements ilActivity, ilWorkflowEngineElement
{
    /** @var ilWorkflowEngineElement $context Holds a reference to the parent object */
    private $context;

    /** @var string|Closure|null */
    private $method = null;

    protected string $name;

    public function __construct(ilNode $context)
    {
        $this->context = $context;
    }

    public function setMethod($value) : void// TODO PHP8-REVIEW Missing type hint or PHPDoc comment
    {
        $this->method = $value;
    }

    /**
     * Returns the value of the setting to be set.
     *
     * @see $setting_value
     *
     * @return string|Closure|null
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

    public function getName() : string
    {
        return $this->name;
    }
}
