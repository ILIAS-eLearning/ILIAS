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
 * ilWorkflowEngine is part of the petri net based workflow engine.
 *
 * The workflow engine is the central hub of activity in the system. It takes
 * care for hibernating/waking of workflows, handling and dispatching events
 * and so on.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngine
{
    public function __construct()
    {
    }

    public function processEvent(
        string $type,
        string $content,
        string $subject_type,
        int $subject_id,
        string $context_type,
        int $context_id
    ): void {
        global $DIC;
        $ilSetting = $DIC->settings();

        if (0 === (int) $ilSetting->get('wfe_activation', '0')) {
            return;
        }

        // Get listening event-detectors.
        $workflows = ilWorkflowDbHelper::getDetectors(
            $type,
            $content,
            $subject_type,
            $subject_id,
            $context_type,
            $context_id
        );

        if (count($workflows) !== 0) {
            foreach ($workflows as $workflow_id) {
                $wf_instance = ilWorkflowDbHelper::wakeupWorkflow($workflow_id);
                if ($wf_instance === null) {
                    continue;
                }

                $wf_instance->handleEvent(
                    [
                        $type,
                        $content,
                        $subject_type,
                        $subject_id,
                        $context_type,
                        $context_id
                    ]
                );
                ilWorkflowDbHelper::writeWorkflow($wf_instance);
            }
        }
    }

    /**
     * @param string $component
     * @param string $event
     * @param array  $parameter
     * @noinspection PhpUndefinedMethodInspection
     */
    public function handleEvent(string $component, string $event, array $parameter): void
    {
        global $DIC;
        /** @var ilSetting $ilSetting */
        $ilSetting = $DIC['ilSetting'];

        if (0 === (int) $ilSetting->get('wfe_activation', '0')) {
            return;
        }

        // Event incoming, check ServiceDisco (TODO, for now we're using a non-disco factory), call appropriate extractors.

        $extractor = ilExtractorFactory::getExtractorByEventDescriptor($component);

        if ($extractor instanceof ilExtractor) {
            $extracted_params = $extractor->extract($event, $parameter);

            $ilLocalSetting = new ilSetting('wfe');
            $mappers = json_decode(
                $ilLocalSetting->get('custom_mapper', json_encode([], JSON_THROW_ON_ERROR)),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            foreach ((array) $mappers as $mapper) {
                if (!is_file($mapper['location'])) {
                    continue;
                }

                include_once $mapper['location'];
                if (!class_exists($mapper['class'])) {
                    continue;
                }

                $mapper_class = $mapper['class'];
                /** @noinspection PhpUndefinedMethodInspection */
                $extracted_params = $mapper_class::mapParams($component, $event, $parameter, $extracted_params);
                $component = $mapper_class::mapComponent($component, $event, $parameter, $extracted_params);
                $event = $mapper_class::mapEvent($component, $event, $parameter, $extracted_params);
            }

            $this->processEvent(
                $component,
                $event,
                $extracted_params->getSubjectType(),
                $extracted_params->getSubjectId(),
                $extracted_params->getContextType(),
                $extracted_params->getContextId()
            );

            $this->launchArmedWorkflows($component, $event, $extracted_params);
        }
    }

    public function launchArmedWorkflows(string $component, string $event, ilExtractedParams $extractedParams): void
    {
        global $DIC;
        /** @var ilSetting $ilSetting */
        $ilSetting = $DIC['ilSetting'];

        if (0 === (int) $ilSetting->get('wfe_activation', '0')) {
            return;
        }

        $workflows = ilWorkflowDbHelper::findApplicableWorkflows($component, $event, $extractedParams);

        foreach ($workflows as $workflow) {
            $data = ilWorkflowDbHelper::getStaticInputDataForEvent($workflow['event']);

            if (!is_file(ilObjWorkflowEngine::getRepositoryDir() . $workflow['workflow'] . '.php')) {
                continue;
            }

            require_once ilObjWorkflowEngine::getRepositoryDir() . $workflow['workflow'] . '.php';
            $class = substr($workflow['workflow'], 4);
            /** @var ilBaseWorkflow $workflow_instance */
            $workflow_instance = new $class();

            $workflow_instance->setWorkflowClass('wfd.' . $class . '.php');
            $workflow_instance->setWorkflowLocation(ilObjWorkflowEngine::getRepositoryDir());

            if (count($workflow_instance->getInputVars())) {
                foreach ($workflow_instance->getInputVars() as $input_var) {
                    $workflow_instance->setInstanceVarById($input_var['name'], $data[ $input_var['name'] ]);
                }
            }

            $workflow_instance->setInstanceVarByRole($extractedParams->getContextType(), $extractedParams->getContextId());
            $workflow_instance->setInstanceVarByRole($extractedParams->getSubjectType(), $extractedParams->getSubjectId());

            ilWorkflowDbHelper::writeWorkflow($workflow_instance);

            $workflow_instance->startWorkflow();
            $workflow_instance->handleEvent(
                [
                    'time_passed',
                    'time_passed',
                    'none',
                    0,
                    'none',
                    0
                ]
            );

            ilWorkflowDbHelper::writeWorkflow($workflow_instance);
        }
    }
}
