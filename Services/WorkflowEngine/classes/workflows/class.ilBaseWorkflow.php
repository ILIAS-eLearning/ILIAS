<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilExternalDetector.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilWorkflow.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';

/**
 * ilBaseWorkflow is part of the petri net based workflow engine.
 *
 * The base workflow class is the ancestor for all concrete workflow implementations.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
abstract class ilBaseWorkflow implements ilWorkflow
{
    /**
     *Holds a list of references nodes attached to the workflow.
     *
     * @var \ilNode[] $nodes Array of ilNode
     */
    protected $nodes;

    /**
     * Holds a list of references to all external detectors of all nodes attached to the workflow.
     *
     * @var \ilExternalDetector[] $detectors Array of ilDetector
     */
    protected $detectors;

    /**
     * Holds a reference to the start node of the workflow.
     *
     * @var ilNode $start_node Node, which is to be activated to start the workflow.
     */
    protected $start_node;

    /**
     * Holds the activation state of the workflow.
     *
     * @var boolean $active
     */
    protected $active;

    /**
     * This holds the database id of the workflow
     *
     * @var integer $db_id
     */
    protected $db_id;

    /**
     * Holds the type of the workflow.
     *
     * Aka its name for easy identification in case a manual search needs to be
     * done on the database. eg. cmpl_crs_ref_48
     *
     * This is intended to be a per-workflow information.
     *
     * @var string $workflow_type Name of type of the workflow.
     */
    protected $workflow_type;

    /**
     * Holds a content description of the workflow instance.
     *
     * Also, just to make this man-handleable. E.g. cmpl_usr_id_6
     *
     * This is intended to be a per-instance information,
     *
     * @var string $workflow_content Content description of the workflow.
     */
    protected $workflow_content;

    /**
     * Holds the classname of the workflow definition.
     * @var string $workflow_class Name of the class. e.g. ComplianceWorkflow1 for class.ilComplianceWorkflow1.php
     */
    protected $workflow_class;

    /**
     * Holds the path to the workflow definition class relative to the applications root.
     * @var string $workflow_location Path to class, e.g. Services/WorkflowEngine for './Services/WorkflowEngine/classes/class..."
     */
    protected $workflow_location;

    /**
     * Holding the subject type of the workflow.
     *
     * This setting holds the identifier 'what kind of' the workflow is about.
     * E.g. crs, usr
     * @var string $workflow_subject_type Name of the subject type.
     */
    protected $workflow_subject_type;

    /**
     * This is the actual identifier of the 'who'. If subject_type is a usr, this
     * is a usr_id. If subject_type is a grp, this is a group_id. (or  group ref id)
     *
     * @var integer $workflow_subject_identifier Identifier of the events subject.
     */
    protected $workflow_subject_identifier;

    /**
     * Type of the workflows context.
     *
     * This is the second 'what kind of' the workflow is rigged to.
     *
     * @var string $workflow_context_type Type if the events context type.
     */
    protected $workflow_context_type;

    /**
     * Identifier of the workflows context.
     *
     * This is the 'who' for second entity the workflow is bound to.
     *
     * @var integer $workflow_context_identifier Identifier of the events context.
     */
    protected $workflow_context_identifier;

    /**
     * Array of instance variables to be shared across the workflow.
     *
     * @var array $instance_vars Associative array of  mixed.
     */
    protected $instance_vars = array();

    /** @var array $data_inputs Input data for the workflow (readonly). */
    protected $data_inputs;

    /** @var array $data_outputs Output data for the workflow. */
    protected $data_outputs;

    /** @var bool $require_data_persistence True, if the persistence needs to deal with data. */
    protected $require_data_persistence = false;

    /**
     * Default constructor
     *
     * Here the definition of the workflow is to be done.
     */
    public function __construct()
    {
    }

    /**
     * Starts the workflow, activating the start_node.
     */
    public function startWorkflow()
    {
        // Write the workflow to the database, so detectors find a parent id to save with them.
        require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowDbHelper.php';
        $this->active = true;
        ilWorkflowDbHelper::writeWorkflow($this);
        $this->onStartWorkflow();

        // Figure out, if there is a start-node set - or nodes at all.
        if ($this->start_node == null) {
            if (count($this->nodes) != 0) {
                $this->start_node = $this->nodes[0];
            } else {
                //ilWorkflowDbHelper::deleteWorkflow($this);
                throw new Exception('No start_node, no node, no start. Doh.');
            }
        }
        $this->start_node->activate();
        ilWorkflowDbHelper::writeWorkflow($this);
    }

    /**
     * Stops the workflow, deactivating all nodes.
     */
    public function stopWorkflow()
    {
        $this->active = false;
        foreach ($this->nodes as $node) {
            $node->deactivate();
        }
        $this->onStopWorkflow();
    }

    /**
     * Method called on start of the workflow, prior to activating the first node.
     * @return void
     */
    public function onStartWorkflow()
    {
        return;
    }

    /**
     * Method called on stopping of the workflow, after deactivating all nodes.
     *
     * Please note: Stopping a workflow 'cancels' the execution. The graceful
     * end of a workflow is handled with @see onWorkflowFinished().
     * @return void
     */
    public function onStopWorkflow()
    {
        return;
    }

    /**
     * Method called after workflow is finished, after detecting no more nodes
     * are active.
     * This is the graceful end of the workflow.
     * Forced shutdown of a workflow is handled in @see onStopWorkflow().
     * @return void
     */
    public function onWorkflowFinished()
    {
        return;
    }

    /**
     * Returns the activation status of the workflow.
     *
     * @return boolean
     */
    public function isActive()
    {
        return (bool) $this->active;
    }

    /**
     * Handles an event.
     *
     * The event is passed to all active event handlers.
     *
     * @param string[] $params
     */
    public function handleEvent($params)
    {
        $active_nodes_available = false;
        // Hier nur an aktive Nodes dispatchen.
        foreach ((array) $this->detectors as $detector) {
            $node = $detector->getContext();
            if ($node->isActive()) {
                $detector->trigger($params);
                $node = $detector->getContext();
                if ($node->isActive()) {
                    $active_nodes_available = true;
                }
            }
        }

        if ($active_nodes_available == false) {
            $this->active = false;
            $this->onWorkflowFinished();
        }
    }

    /**
     * @param \ilDetector $detector
     */
    public function registerDetector(ilDetector $detector)
    {
        $reflection_class = new ReflectionClass($detector);
        if (in_array('ilExternalDetector', $reflection_class->getInterfaceNames())) {
            $this->detectors[] = $detector;
        }
    }

    /**
     * Returns the workflow type and content currently set to the workflow.
     *
     * @return  array array('type' => $this->workflow_type, 'content' => $this->workflow_content)
     */
    public function getWorkflowData()
    {
        return array('type' => $this->workflow_type, 'content' => $this->workflow_content);
    }

    /**
     * Get the workflow subject set to the workflow.
     *
     * @return array array('type' => $this->workflow_subject_type, 'identifier' => $this->workflow_subject_identifier)
     */
    public function getWorkflowSubject()
    {
        return array('type' => $this->workflow_subject_type, 'identifier' => $this->workflow_subject_identifier);
    }

    /**
     * Get the event context set to the workflow.
     *
     * @return array array('type' => $this->workflow_context_type, 'identifier' => $this->workflow_context_identifier)
     */
    public function getWorkflowContext()
    {
        return array('type' => $this->workflow_context_type, 'identifier' => $this->workflow_context_identifier);
    }

    /**
     * Sets the database id of the detector.
     *
     * @param integer $id
     */
    public function setDbId($id)
    {
        $this->db_id = $id;
    }

    /**
     * Returns the database id of the detector if set.
     *
     * @return int
     * @throws \ilWorkflowObjectStateException
     */
    public function getDbId()
    {
        if ($this->db_id != null) {
            return $this->db_id;
        } else {
            require_once './Services/WorkflowEngine/exceptions/ilWorkflowObjectStateException.php';
            throw new ilWorkflowObjectStateException('No database ID set.');
        }
    }

    /**
     * Returns, if the detector has a database id.
     * @return boolean If a database id is set.
     */
    public function hasDbId()
    {
        if ($this->db_id == null) {
            return false;
        }
        return true;
    }

    /**
     * Sets the start node of the workflow. This node is activated, when the
     * workflow is started.
     *
     * @param ilNode $node
     */
    public function setStartNode(ilNode $node)
    {
        $this->start_node = $node;
    }

    /**
     * This method adds a node to the workflow.
     *
     * @param ilNode $node
     */
    public function addNode(ilNode $node)
    {
        $this->nodes[] = $node;
    }

    /**
     * Sets the classname of the workflow definition.
     *
     * @see $this->workflow_class
     *
     * @param string $class
     */
    public function setWorkflowClass($class)
    {
        $this->workflow_class = $class;
    }

    /**
     * Returns the currently set workflow class definition name.
     *
     * @see $this->workflow_class
     *
     * @return string Class name
     */
    public function getWorkflowClass()
    {
        return $this->workflow_class;
    }

    /**
     * Sets the location of the workflow definition file as relative path.
     *
     * @see $this->workflow_location
     *
     * @param string $path e.g. Services/WorkflowEngine
     */
    public function setWorkflowLocation($path)
    {
        $this->workflow_location = $path;
    }

    /**
     * Returns the currently set path to the workflow definition.
     *
     * @see $this->workflow_location
     *
     * @return string
     */
    public function getWorkflowLocation()
    {
        return $this->workflow_location;
    }

    /**
     * Returns all nodes attached to the workflow.
     *
     * @return ilNode[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * Autoloader function to dynamically include files for instantiation of
     * objects during deserialization.
     *
     * @param string $class_name
     */
    public static function autoload($class_name)
    {
        switch (true) {
            case strtolower(substr($class_name, strlen($class_name) - 8, 8)) == 'activity':
                $componentDirectory = 'activities';
                break;

            case strtolower(substr($class_name, strlen($class_name) - 8, 8)) == 'detector':
                $componentDirectory = 'detectors';
                break;

            case strtolower(substr($class_name, strlen($class_name) - 7, 7)) == 'emitter':
                $componentDirectory = 'emitters';
                break;

            case strtolower(substr($class_name, strlen($class_name) - 4, 4)) == 'node':
                $componentDirectory = 'node';
                break;
                
            default:
                return;
        }

        $filename = './Services/WorkflowEngine/classes/' . $componentDirectory . '/class.' . $class_name . '.php';
        if (file_exists($filename)) {
            require_once $filename;
        }
    }

    /**
     * @return bool
     */
    public function isDataPersistenceRequired()
    {
        return $this->require_data_persistence;
    }

    /**
     * @return void
     */
    public function resetDataPersistenceRequirement()
    {
        $this->require_data_persistence = false;
    }

    #region InstanceVars

    /*
     * Instancevars work like this:
     * array(
     * 	'id' => 'string',
     * 	'name' => 'string',
     * 	'value' => mixed
     * );
     *
     */

    /**
     * @param string $id
     * @param string $name
     * @param bool   $reference
     * @param string $reference_target
     * @param string $type
     * @param string $role
     */
    public function defineInstanceVar(
        $id,
        $name,
        $reference = false,
        $reference_target = '',
        $type = 'mixed',
        $role = 'undefined'
    ) {
        $this->instance_vars[] = array(
            'id' => $id,
            'name' => $name,
            'value' => null,
            'reference' => $reference,
            'target' => $reference_target,
            'type' => $type,
            'role' => $role
        );
    }

    /**
     * Returns if an instance variable of the given name is set.
     *
     * @param string $name
     * @return boolean True, if a variable by that name is set.
     */
    public function hasInstanceVarByName($name)
    {
        foreach ($this->instance_vars as $instance_var) {
            if ($instance_var['name'] == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns if an instance variable of the given id is set.
     *
     * @param string $id
     *
     * @return boolean True, if a variable by that id is set.
     */
    public function hasInstanceVarById($id)
    {
        foreach ($this->instance_vars as $instance_var) {
            if ($instance_var['id'] == $id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the given instance variables content
     *
     * @param string $name Name of the variable.
     * @return mixed Content of the variable.
     */
    public function getInstanceVarByName($name)
    {
        foreach ($this->instance_vars as &$instance_var) {
            if ($instance_var['name'] == $name) {
                if ($instance_var['reference'] === true) {
                    return $this->getInstanceVarByName($instance_var['target']);
                } else {
                    return $instance_var['value'];
                }
            }
        }
        return false;
    }

    /**
     * Returns the given instance variables content
     *
     * @param string $name Name of the variable.
     * @return mixed Content of the variable.
     */
    public function getInstanceVarById($id)
    {
        foreach ($this->instance_vars as $instance_var) {
            if ($instance_var['id'] == $id) {
                if ($instance_var['reference'] === true) {
                    return $this->getInstanceVarById($instance_var['target']);
                } else {
                    return $instance_var['value'];
                }
            }
        }
        return false;
    }

    /**
     * Sets the given instance var with the given content.
     * @param string $name Name of the variable
     * @param mixed $value
     */
    public function setInstanceVarByName($name, $value)
    {
        foreach ($this->instance_vars as &$instance_var) {
            if ($instance_var['name'] == $name) {
                if ($instance_var['reference'] === true) {
                    $this->setInstanceVarById($instance_var['target'], $value);
                } else {
                    $instance_var['value'] = $value;
                }
            }
        }
    }

    /**
     * Sets the given instance var with the given content.
     *
     * @param string $id    Name of the variable
     * @param mixed  $value
     */
    public function setInstanceVarById($id, $value)
    {
        foreach ($this->instance_vars as &$instance_var) {
            if ($instance_var['id'] == $id) {
                if ($instance_var['reference'] === true) {
                    $this->setInstanceVarById($instance_var['target'], $value);
                } else {
                    $instance_var['value'] = $value;
                    return;
                }
            }
        }
    }

    /**
     * Sets the given instance var with the given content.
     * *only during startup to write event params*
     * @param string $role    Role of the variable
     * @param mixed  $value
     */
    public function setInstanceVarByRole($role, $value)
    {
        foreach ($this->instance_vars as &$instance_var) {
            if ($instance_var['role'] == $role) {
                if ($instance_var['reference'] === true) {
                    $this->setInstanceVarById($instance_var['target'], $value);
                } else {
                    $instance_var['value'] = $value;
                    return;
                }
            }
        }
    }

    /**
     * Returns an array with all set instance variables.
     *
     * @return array Associative array of mixed.
     */
    public function getInstanceVars()
    {
        return (array) $this->instance_vars;
    }

    /**
     * Empties the instance variables.
     */
    public function flushInstanceVars()
    {
        $this->instance_vars = array();
    }

    #endregion

    #region Data IO

    /**
     * @deprecated
     */
    public function defineInputVar($name)
    {
        $this->data_inputs[$name] = null;
        $this->require_data_persistence = true;
    }

    /**
     * @deprecated
     */
    public function defineOutputVar($name)
    {
        $this->data_outputs[$name] = null;
        $this->require_data_persistence = true;
    }

    /**
     * @deprecated
     */
    public function readInputVar($name)
    {
        if ($this->data_inputs[$name]) {
            return $this->data_inputs[$name];
        }
        return null;
    }

    /**
     * @deprecated
     */
    public function hasInputVar($name)
    {
        return array_key_exists($name, (array) $this->data_inputs);
    }

    /**
     * @deprecated
     */
    public function hasOutputVar($name)
    {
        return array_key_exists($name, (array) $this->data_outputs);
    }

    /**
     * @deprecated
     */
    public function writeInputVar($name, $value)
    {
        $this->data_inputs[$name] = $value;
        $this->require_data_persistence = true;
    }

    /**
     * @deprecated
     */
    public function readOutputVar($name)
    {
        if ($this->data_outputs[$name]) {
            return $this->data_outputs[$name];
        }
        return null;
    }

    /**
     * @deprecated
     */
    public function writeOutputVar($name, $value)
    {
        $this->data_outputs[$name] = $value;
        $this->require_data_persistence = true;
    }

    public function getInputVars()
    {
        return (array) $this->data_inputs;
    }

    /**
     * @deprecated
     */
    public function getOutputVars()
    {
        return (array) $this->data_outputs;
    }

    /**
     * @param string $name
     * @param mixed  $definition
     */
    public function registerInputVar($name, $definition)
    {
        $definition['name'] = $name;
        $this->data_inputs[$name] = $definition;
    }

    /**
     * @param string $name
     */
    public function registerOutputVar($name)
    {
        $this->data_outputs[] = $name;
    }

    #endregion
}

spl_autoload_register(array('ilBaseWorkflow', 'autoload'));
