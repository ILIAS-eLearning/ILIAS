<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilWorkflowDbHelper is part of the petri net based workflow engine.
 *
 * This helper takes care of all database related actions which are part of the
 * internal workings of the workflow engine.
 *
 * Hint: This is not the place to stuff your db-calls for activities, kid!
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowDbHelper
{
    const DB_MODE_CREATE = 0;
    const DB_MODE_UPDATE = 1;

    /**
     * Takes a workflow as an argument and saves it to the database.
     *
     * @global ilDB      $ilDB
     *
     * @param ilWorkflow $workflow
     */
    public static function writeWorkflow(ilWorkflow $workflow)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $require_data_persistance = $workflow->isDataPersistenceRequired();
        $workflow->resetDataPersistenceRequirement();

        if ($workflow->hasDbId()) {
            $wf_id = $workflow->getDbId();
            $mode = self::DB_MODE_UPDATE;
        } else {
            $wf_id = $ilDB->nextId('wfe_workflows');
            $workflow->setDbId($wf_id);
            $mode = self::DB_MODE_CREATE;
        }

        $wf_data = $workflow->getWorkflowData();
        $wf_subject = $workflow->getWorkflowSubject();
        $wf_context = $workflow->getWorkflowContext();
        $active = $workflow->isActive();
        $instance = serialize($workflow);

        if ($mode == self::DB_MODE_UPDATE) {
            $ilDB->update(
                'wfe_workflows',
                array(
                    'workflow_type' => array('text', $wf_data['type'] ),
                    'workflow_content' => array('text', $wf_data['content']),
                    'workflow_class' => array('text', $workflow->getWorkflowClass()),
                    'workflow_location' => array('text', $workflow->getWorkflowLocation()),
                    'subject_type' => array('text', $wf_subject['type']),
                    'subject_id' => array('integer', $wf_subject['identifier']),
                    'context_type' => array('text', $wf_context['type']),
                    'context_id' => array('integer', $wf_context['identifier']),
                    'workflow_instance' => array('clob', $instance),
                    'active' => array('integer', (int) $active)
                ),
                array(
                    'workflow_id' => array('integer', $wf_id)
                )
            );
        }

        if ($mode == self::DB_MODE_CREATE) {
            $ilDB->insert(
                'wfe_workflows',
                array(
                    'workflow_id' => array('integer', $wf_id),
                    'workflow_type' => array('text', $wf_data['type'] ),
                    'workflow_class' => array('text', $workflow->getWorkflowClass()),
                    'workflow_location' => array('text', $workflow->getWorkflowLocation()),
                    'workflow_content' => array('text', $wf_data['content']),
                    'subject_type' => array('text', $wf_subject['type']),
                    'subject_id' => array('integer', $wf_subject['identifier']),
                    'context_type' => array('text', $wf_context['type']),
                    'context_id' => array('integer', $wf_context['identifier']),
                    'workflow_instance' => array('clob', $instance),
                    'active' => array('integer', (int) $active)
                )
            );
        }

        if ($require_data_persistance) {
            self::persistWorkflowIOData($workflow);
        }
    }

    /**
     * @param \ilWorkflow $workflow
     */
    public static function persistWorkflowIOData(ilWorkflow $workflow)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $workflow_id = $workflow->getId();

        $input_data = $workflow->getInputVars();
        foreach ($input_data as $name => $value) {
            $ilDB->replace(
                'wfe_io_inputs',
                array('workflow_id' => $workflow_id, 'name' => $name),
                array('value' => $value)
            );
        }

        $output_data = $workflow->getOutputVars();
        foreach ($output_data as $name => $value) {
            $ilDB->replace(
                'wfe_io_outputs',
                array('workflow_id' => $workflow_id, 'name' => $name),
                array('value' => $value)
            );
        }
    }

    /**
     * Takes a workflow as an argument and deletes the corresponding entry
     * from the database.
     *
     * @global ilDB $ilDB
     *
     * @param ilWorkflow $a_workflow
     */
    public static function deleteWorkflow(ilWorkflow $a_workflow)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];
        
        if ($a_workflow->hasDbId()) {
            $ilDB->manipulate(
                'DELETE 
				FROM wfe_workflows
				WHERE workflow_id = ' . $ilDB->quote($a_workflow->getDbId(), 'integer')
            );
            
            // This should not be necessary, actually. Still this call makes sure
            // that there won't be orphan records polluting the database.
            $ilDB->manipulate(
                'DELETE
				FROM wfe_det_listening
				WHERE workflow_id = ' . $ilDB->quote($a_workflow->getDbId(), 'integer')
            );

            $ilDB->manipulate(
                'DELETE
				FROM wfe_io_inputs
				WHERE workflow_id = ' . $ilDB->quote($a_workflow->getDbId(), 'integer')
            );

            $ilDB->manipulate(
                'DELETE
				FROM wfe_io_outputs
				WHERE workflow_id = ' . $ilDB->quote($a_workflow->getDbId(), 'integer')
            );
        } else {
            return;
        }
    }

    /**
     * Takes a detector as an argument and saves it to the database.
     *
     * @global ilDB $ilDB
     *
     * @param ilDetector $a_detector
     */
    public static function writeDetector(ilDetector $a_detector)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        if ($a_detector->hasDbId()) {
            $det_id = $a_detector->getDbId();
            $mode = self::DB_MODE_UPDATE;
        } else {
            $det_id = $ilDB->nextId('wfe_det_listening');
            $a_detector->setDbId($det_id);
            $mode = self::DB_MODE_CREATE;
        }

        $node = $a_detector->getContext();
        $workflow = $node->getContext();
        if ($workflow->hasDbId()) {
            $wf_id = $workflow->getDbId();
        } else {
            $wf_id = null;
        }

        $det_data = $a_detector->getEvent();
        $det_subject = $a_detector->getEventSubject();
        $det_context = $a_detector->getEventContext();
        $det_listen = $a_detector->getListeningTimeframe();

        if ($det_context['identifier'] === '{{THIS:WFID}}') {
            $det_context['identifier'] = $wf_id;
        }

        if ($det_subject['identifier'] === '{{THIS:WFID}}') {
            $det_subject['identifier'] = $wf_id;
        }

        if ($mode == self::DB_MODE_UPDATE) {
            $ilDB->update(
                'wfe_det_listening',
                array(
                    'workflow_id' => array('integer', $wf_id),
                    'type' => array('text', $det_data['type'] ),
                    'content' => array('text', $det_data['content']),
                    'subject_type' => array('text', $det_subject['type']),
                    'subject_id' => array('integer', $det_subject['identifier']),
                    'context_type' => array('text', $det_context['type']),
                    'context_id' => array('integer', $det_context['identifier']),
                    'listening_start' => array('integer', $det_listen['listening_start']),
                    'listening_end' => array('integer', $det_listen['listening_end'])
                ),
                array(
                    'detector_id' => array('integer', $det_id)
                )
            );
        }
        
        if ($mode == self::DB_MODE_CREATE) {
            $ilDB->insert(
                'wfe_det_listening',
                array(
                    'detector_id' => array('integer', $det_id),
                    'workflow_id' => array('integer', $wf_id),
                    'type' => array('text', $det_data['type'] ),
                    'content' => array('text', $det_data['content']),
                    'subject_type' => array('text', $det_subject['type']),
                    'subject_id' => array('integer', $det_subject['identifier']),
                    'context_type' => array('text', $det_context['type']),
                    'context_id' => array('integer', $det_context['identifier']),
                    'listening_start' => array('integer', $det_listen['listening_start']),
                    'listening_end' => array('integer', $det_listen['listening_end'])
                )
            );
        }
    }

    /**
     * Takes a detector as an argument and deletes the corresponding entry
     * from the database.
     *
     * @param \ilDetector|\ilExternalDetector $detector
     *
     * @global ilDB                           $ilDB
     *
     */
    public static function deleteDetector(ilExternalDetector $detector)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        if ($detector->hasDbId()) {
            $ilDB->manipulate(
                'DELETE
				FROM wfe_det_listening
				WHERE detector_id = ' . $ilDB->quote($detector->getDbId(), 'integer')
            );
            $detector->setDbId(null);
        } else {
            return;
        }
    }

    /**
     * Gets a list of all listening detectors for the given event.
     *
     * @global ilDB   $ilDB
     *
     * @param string  $type         Type of the event.
     * @param string  $content      Content of the event.
     * @param string  $subject_type Type of the subject, e.g. usr.
     * @param integer $subject_id   Identifier of the subject, eg. 6.
     * @param string  $context_type Type of the context, e.g. crs.
     * @param integer $context_id   Identifier of the context, e.g. 48
     *
     * @return \integer	Array of workflow ids with listening detectors.
     */
    public static function getDetectors(
        $type,
        $content,
        $subject_type,
        $subject_id,
        $context_type,
        $context_id
    ) {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowUtils.php';
        $now = ilWorkflowUtils::time();
        $workflows = array();

        $result = $ilDB->query(
            'SELECT workflow_id
			FROM wfe_det_listening
			WHERE type = ' . $ilDB->quote($type, 'text') . '
			AND content = ' . $ilDB->quote($content, 'text') . '
			AND subject_type = ' . $ilDB->quote($subject_type, 'text') . '
			AND (subject_id = ' . $ilDB->quote($subject_id, 'integer') . ' OR subject_id = ' . $ilDB->quote(0, 'integer') . ')
			AND context_type = ' . $ilDB->quote($context_type, 'text') . '
			AND (context_id = ' . $ilDB->quote($context_id, 'integer') . ' OR context_id = ' . $ilDB->quote(0, 'integer') . ')
			AND (listening_start = ' . $ilDB->quote(0, 'integer') . ' 
				 OR listening_start <= ' . $ilDB->quote($now, 'integer') . ') AND (listening_end = ' . $ilDB->quote(0, 'integer') . '
				 OR listening_end >= ' . $ilDB->quote($now, 'integer') . ')'
        );

        while ($row = $ilDB->fetchAssoc($result)) {
            $workflows[] = $row['workflow_id'];
        }

        return $workflows;
    }

    /**
     * Wakes a workflow from the database.
     *
     * @global ilDB   $ilDB
     *
     * @param integer $id workflow_id.
     *
     * @return \ilWorkflow An ilWorkflow-implementing instance.
     *
     */
    public static function wakeupWorkflow($id)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query(
            'SELECT workflow_class, workflow_location, workflow_instance
			FROM wfe_workflows
			WHERE workflow_id = ' . $ilDB->quote($id, 'integer')
        );

        $workflow = $ilDB->fetchAssoc($result);

        require_once './Services/WorkflowEngine/classes/workflows/class.ilBaseWorkflow.php';
        $path = rtrim($workflow['workflow_location'], '/') . '/' . $workflow['workflow_class'];

        if (file_exists($path) && $path != '/') {
            require_once $path;
            $instance = unserialize($workflow['workflow_instance']);
        } else {
            $instance = null;
        }
        return $instance;
    }

    /**
     * Takes a detector as an argument and saves it to the database.
     *
     * @global ilDB $ilDB
     *
     * @param array $event
     */
    public static function writeStartEventData($event, $process_id)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $event_id = $ilDB->nextId('wfe_startup_events');

        $ilDB->insert(
            'wfe_startup_events',
            array(
                          'event_id' => array('integer', $event_id),
                          'workflow_id' => array('text', $process_id),
                          'type' => array('text', $event['type'] ),
                          'content' => array('text', $event['content']),
                          'subject_type' => array('text', $event['subject_type']),
                          'subject_id' => array('integer', $event['subject_id']),
                          'context_type' => array('text', $event['context_type']),
                          'context_id' => array('integer', $event['context_id'])
                      )
        );

        return $event_id;
    }

    /**
     * @param string $key
     * @param string $value
     * @param string $start_event
     */
    public static function writeStaticInput($key, $value, $start_event)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $ilDB->insert(
            'wfe_static_inputs',
            array(
                'input_id' => array('integer', $ilDB->nextId('wfe_static_inputs')),
                'event_id' => array('integer', $start_event),
                'name' => array('text',    $key),
                'value' => array('text',    $value)
            )
        );
    }

    public static function findApplicableWorkflows($component, $event, $params)
    {
        $query = "SELECT event_id, workflow_id FROM wfe_startup_events WHERE
		type = '" . $component . "' AND content = '" . $event . "' AND subject_type = '" . $params->getSubjectType() . "'
		AND context_type = '" . $params->getContextType() . "' ";

        $query .= "AND ( subject_id = '" . $params->getSubjectId() . "' OR subject_id ='0' ) ";
        $query .= "AND ( context_id = '" . $params->getContextId() . "' OR context_id ='0' ) ";

        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $workflows = array();
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            $workflows[] = array('event' => $row['event_id'], 'workflow' => $row['workflow_id']);
        }
        return $workflows;
    }

    public static function getStaticInputDataForEvent($event_id)
    {
        $query = "SELECT name, value FROM wfe_static_inputs WHERE event_id = '" . $event_id . "'";

        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query($query);

        $retval = array();

        while ($row = $ilDB->fetchAssoc($result)) {
            $retval[$row['name']] = $row['value'];
        }

        return $retval;
    }

    public static function deleteStartEventData($event_id)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query(
            'SELECT event_id FROM wfe_startup_events 
				  WHERE workflow_id = ' . $ilDB->quote($event_id, 'integer')
        );

        $events = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $events = $row['revent_id'];
        }

        $ilDB->manipulate(
            'DELETE
				FROM wfe_startup_events
				WHERE workflow_id = ' . $ilDB->quote($event_id, 'integer')
        );

        if (count($events)) {
            $ilDB->manipulate(
                'DELETE
				FROM wfe_static_inputs
				WHERE ' . $ilDB->in('event_id', $events, false, 'integer')
            );
        }
    }
}
