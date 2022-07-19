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
 * ilDataDetector of the petri net based workflow engine.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilDataDetector implements ilDetector, ilWorkflowEngineElement
{
    /**
     * Holds a reference to the parent object
     */
    private ?ilWorkflowEngineElement $context = null;

    /**
     * Holds the current detection state.
     */
    private bool $detection_state = false;

    protected $name;

    protected ?ilNode $source_node = null;

    protected string $var_name = '';

    public function __construct(ilNode $context)
    {
        $this->context = $context;
    }

    /**
     * Returns the parent object. Type is ilNode, implements ilWorkflowEngineElement
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Trigger this detector. Params are an array. These are part of the interface
     * but ignored here.
     *
     * @todo Handle ignored $params.
     *
     * @param array $params
     *
     * @return bool False, if detector was already satisfied before.
     */
    public function trigger($params) : ?bool
    {
        return true;
    }

    /**
     * Returns if the current detector state is satisfied or not.
     *
     * @return bool
     */
    public function getDetectorState() : bool
    {
        $definitions = $this->getContext()->getContext()->getInstanceVars();

        $id = $this->var_name;
        $name = $this->var_name;
        foreach ($definitions as $definition) {
            if ($definition['id'] === $name) {
                if ($definition['reference']) {
                    $id = $definition['target'];
                }
                $name = $definition['name'];
                break;
            }
        }

        $this->getContext()->setRuntimeVar(
            $name,
            $this->getContext()->getContext()->getInstanceVarById($id)
        );
        $this->detection_state = true;

        return true;
    }

    /**
     * Sets a new detector state.
     * In this case, the only meaningful param is false, since it should only
     * be set to true, if the detector was triggered.
     * Reason this method exists, is to allow the workflow controller to
     * "fast forward" workflows to set a non-default state. I.e. a workflow
     * has to be set into a state in the middle of running. Use with care.
     * @param bool $new_state
     */
    public function setDetectorState(bool $new_state) : void
    {
        $this->detection_state = true;
        $this->context->notifyDetectorSatisfaction($this);
    }

    /**
     * Method is called, when the parent node is activated.
     * @return void
     */
    public function onActivate() : void
    {
    }

    /**
     * Method is called, when the parent node is deactivated.
     * @return void
     */
    public function onDeactivate() : void
    {
    }

    public function getActivated() : bool
    {
        return $this->detection_state;
    }

    public function setName($name) : void
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getSourceNode() : ?ilNode
    {
        return $this->source_node;
    }

    public function setSourceNode(ilNode $source_node) : void
    {
        $this->source_node = $source_node;
    }

    public function getVarName() : string
    {
        return $this->var_name;
    }

    public function setVarName(string $var_name) : void
    {
        $this->var_name = $var_name;
    }
}
