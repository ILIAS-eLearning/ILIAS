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
 * ilWorkflowDbHelper is part of the petri net based workflow engine.
 *
 * This helper takes care of all database related actions which are part of the
 * internal workings of the workflow engine.
 *
 * Hint: This is not the place to stuff your db-calls for activities, kid!
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowDbHelper
{
    private const DB_MODE_CREATE = 0;
    private const DB_MODE_UPDATE = 1;

    /**
     * Takes a workflow as an argument and saves it to the database.
     *
     * @param ilWorkflow $workflow
     */
    public static function writeWorkflow(ilWorkflow $workflow) : void
    {
        global $DIC;
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

        if ($mode === self::DB_MODE_UPDATE) {
            $ilDB->update(
                'wfe_workflows',
                [
                    'workflow_type' => ['text', $wf_data['type']],
                    'workflow_content' => ['text', $wf_data['content']],
                    'workflow_class' => ['text', $workflow->getWorkflowClass()],
                    'workflow_location' => ['text', $workflow->getWorkflowLocation()],
                    'subject_type' => ['text', $wf_subject['type']],
                    'subject_id' => ['integer', $wf_subject['identifier']],
                    'context_type' => ['text', $wf_context['type']],
                    'context_id' => ['integer', $wf_context['identifier']],
                    'workflow_instance' => ['clob', $instance],
                    'active' => ['integer', (int) $active]
                ],
                [
                    'workflow_id' => ['integer', $wf_id]
                ]
            );
        }

        if ($mode === self::DB_MODE_CREATE) {
            $ilDB->insert(
                'wfe_workflows',
                [
                    'workflow_id' => ['integer', $wf_id],
                    'workflow_type' => ['text', $wf_data['type']],
                    'workflow_class' => ['text', $workflow->getWorkflowClass()],
                    'workflow_location' => ['text', $workflow->getWorkflowLocation()],
                    'workflow_content' => ['text', $wf_data['content']],
                    'subject_type' => ['text', $wf_subject['type']],
                    'subject_id' => ['integer', $wf_subject['identifier']],
                    'context_type' => ['text', $wf_context['type']],
                    'context_id' => ['integer', $wf_context['identifier']],
                    'workflow_instance' => ['clob', $instance],
                    'active' => ['integer', (int) $active]
                ]
            );
        }

        if ($require_data_persistance) {
            self::persistWorkflowIOData($workflow);
        }
    }

    public static function persistWorkflowIOData(ilWorkflow $workflow) : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $workflow_id = $workflow->getDbId();

        $input_data = $workflow->getInputVars();
        foreach ($input_data as $name => $value) {
            $ilDB->replace(
                'wfe_io_inputs',
                ['workflow_id' => $workflow_id, 'name' => $name],
                ['value' => $value]
            );
        }

        $output_data = $workflow->getOutputVars();
        foreach ($output_data as $name => $value) {
            $ilDB->replace(
                'wfe_io_outputs',
                ['workflow_id' => $workflow_id, 'name' => $name],
                ['value' => $value]
            );
        }
    }

    /**
     * Takes a workflow as an argument and deletes the corresponding entry
     * from the database.
     *
     * @param ilWorkflow $a_workflow
     */
    public static function deleteWorkflow(ilWorkflow $a_workflow) : void
    {
        global $DIC;
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
        }
    }

    /**
     * Takes a detector as an argument and saves it to the database.
     * @param ilDetector $a_detector
     */
    public static function writeDetector(ilDetector $a_detector) : void
    {
        global $DIC;
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

        if ($mode === self::DB_MODE_UPDATE) {
            $ilDB->update(
                'wfe_det_listening',
                [
                    'workflow_id' => ['integer', $wf_id],
                    'type' => ['text', $det_data['type']],
                    'content' => ['text', $det_data['content']],
                    'subject_type' => ['text', $det_subject['type']],
                    'subject_id' => ['integer', $det_subject['identifier']],
                    'context_type' => ['text', $det_context['type']],
                    'context_id' => ['integer', $det_context['identifier']],
                    'listening_start' => ['integer', $det_listen['listening_start']],
                    'listening_end' => ['integer', $det_listen['listening_end']]
                ],
                [
                    'detector_id' => ['integer', $det_id]
                ]
            );
        }
        
        if ($mode === self::DB_MODE_CREATE) {
            $ilDB->insert(
                'wfe_det_listening',
                [
                    'detector_id' => ['integer', $det_id],
                    'workflow_id' => ['integer', $wf_id],
                    'type' => ['text', $det_data['type']],
                    'content' => ['text', $det_data['content']],
                    'subject_type' => ['text', $det_subject['type']],
                    'subject_id' => ['integer', $det_subject['identifier']],
                    'context_type' => ['text', $det_context['type']],
                    'context_id' => ['integer', $det_context['identifier']],
                    'listening_start' => ['integer', $det_listen['listening_start']],
                    'listening_end' => ['integer', $det_listen['listening_end']]
                ]
            );
        }
    }

    /**
     * Takes a detector as an argument and deletes the corresponding entry
     * from the database.
     * @param ilExternalDetector $detector
     */
    public static function deleteDetector(ilExternalDetector $detector) : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($detector->hasDbId()) {
            $ilDB->manipulate(
                'DELETE
				FROM wfe_det_listening
				WHERE detector_id = ' . $ilDB->quote($detector->getDbId(), 'integer')
            );
            $detector->setDbId(null);
        }
    }

    /**
     * Gets a list of all listening detectors for the given event.
     * @param string  $type         Type of the event.
     * @param string  $content      Content of the event.
     * @param string  $subject_type Type of the subject, e.g. usr.
     * @param int $subject_id   Identifier of the subject, eg. 6.
     * @param string  $context_type Type of the context, e.g. crs.
     * @param int $context_id   Identifier of the context, e.g. 48
     * @return int[]	Array of workflow ids with listening detectors.
     */
    public static function getDetectors(
        string $type,
        string $content,
        string $subject_type,
        int $subject_id,
        string $context_type,
        int $context_id
    ) : array {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $now = ilWorkflowUtils::time();
        $workflows = [];

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
            $workflows[] = (int) $row['workflow_id'];
        }

        return $workflows;
    }

    /**
     * Wakes a workflow from the database.
     * @param int $id workflow_id.
     * @return ilWorkflow An ilWorkflow-implementing instance.
     */
    public static function wakeupWorkflow(int $id) : ?ilWorkflow
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query(
            'SELECT workflow_class, workflow_location, workflow_instance
			FROM wfe_workflows
			WHERE workflow_id = ' . $ilDB->quote($id, 'integer')
        );

        $workflow = $ilDB->fetchAssoc($result);

        $path = rtrim($workflow['workflow_location'], '/') . '/' . $workflow['workflow_class'];

        if ($path !== '/' && is_file($path)) {
            require_once $path;
            $instance = unserialize($workflow['workflow_instance']);
        } else {
            $instance = null;
        }
        return $instance;
    }

    /**
     * Takes a detector as an argument and saves it to the database.
     * @param array $event
     * @param       $process_id
     * @return mixed
     */
    public static function writeStartEventData(array $event, $process_id)// TODO PHP8-REVIEW Missing type hint or PHPDoc
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $event_id = $ilDB->nextId('wfe_startup_events');

        $ilDB->insert(
            'wfe_startup_events',
            [
                'event_id' => ['integer', $event_id],
                'workflow_id' => ['text', $process_id],
                'type' => ['text', $event['type']],
                'content' => ['text', $event['content']],
                'subject_type' => ['text', $event['subject_type']],
                'subject_id' => ['integer', $event['subject_id']],
                'context_type' => ['text', $event['context_type']],
                'context_id' => ['integer', $event['context_id']]
            ]
        );

        return $event_id;
    }

    public static function writeStaticInput(string $key, string $value, string $start_event) : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->insert(
            'wfe_static_inputs',
            [
                'input_id' => ['integer', $ilDB->nextId('wfe_static_inputs')],
                'event_id' => ['integer', $start_event],
                'name' => ['text', $key],
                'value' => ['text', $value]
            ]
        );
    }

    public static function findApplicableWorkflows($component, $event, $params) : array// TODO PHP8-REVIEW Missing type hints or PHPDoc
    {
        $query = "SELECT event_id, workflow_id FROM wfe_startup_events WHERE
		type = '" . $component . "' AND content = '" . $event . "' AND subject_type = '" . $params->getSubjectType() . "'
		AND context_type = '" . $params->getContextType() . "' ";

        $query .= "AND ( subject_id = '" . $params->getSubjectId() . "' OR subject_id ='0' ) ";
        $query .= "AND ( context_id = '" . $params->getContextId() . "' OR context_id ='0' ) ";

        global $DIC;
        $ilDB = $DIC['ilDB'];

        $workflows = [];
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            $workflows[] = ['event' => $row['event_id'], 'workflow' => $row['workflow_id']];
        }
        return $workflows;
    }

    public static function getStaticInputDataForEvent($event_id) : array// TODO PHP8-REVIEW Missing type hint or PHPDoc
    {
        $query = "SELECT name, value FROM wfe_static_inputs WHERE event_id = '" . $event_id . "'";

        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query($query);

        $retval = [];

        while ($row = $ilDB->fetchAssoc($result)) {
            $retval[$row['name']] = $row['value'];
        }

        return $retval;
    }

    public static function deleteStartEventData($event_id) : void// TODO PHP8-REVIEW Missing type hint or PHPDoc
    {
        global $DIC;

        /** @var ilDBInterface $ilDB */
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query(
            'SELECT event_id FROM wfe_startup_events 
				  WHERE workflow_id = ' . $ilDB->quote($event_id, 'integer')
        );
        $events = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            $events[] = $row['event_id'];
        }

        $ilDB->manipulate(
            'DELETE
				FROM wfe_startup_events
				WHERE workflow_id = ' . $ilDB->quote($event_id, 'integer')
        );

        if (count($events) > 0) {
            $ilDB->manipulate(
                'DELETE
				FROM wfe_static_inputs
				WHERE ' . $ilDB->in('event_id', $events, false, 'integer')
            );
        }
    }
}
