<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilWorkflowEngine is part of the petri net based workflow engine.
 *
 * The workflow engine is the central hub of activity in the system. It takes
 * care for hibernating/waking of workflows, handling and dispatching events 
 * and so on.
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngine
{
	/**
	 * True, if this instance is made to handle a lot of requests.
	 * @var boolean
	 */
	private $mass_action;

	/**
	 * ilWorkflowEngine constructor.
	 *
	 * @param bool $a_mass_action
	 */
	public function __construct($a_mass_action = false)
	{
		$this->mass_action = (bool) $a_mass_action;
	}

	/**
	 * @param string  $type
	 * @param string  $content
	 * @param string  $subject_type
	 * @param integer $subject_id
	 * @param string  $context_type
	 * @param integer $context_id
	 */
	public function processEvent(
		$type,
		$content,
		$subject_type,
		$subject_id,
		$context_type,
		$context_id
	)
	{
		/** @var ilSetting $ilSetting */
		global $ilSetting;
		if(0 === (bool)$ilSetting->get('wfe_activation', 0))
		{
			return;
		}

		// Get listening event-detectors.
		/** @noinspection PhpIncludeInspection */
		require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowDbHelper.php';
		$workflows = ilWorkflowDbHelper::getDetectors(
			$type,
			$content,
			$subject_type,
			$subject_id,
			$context_type,
			$context_id
		);

		if (count($workflows) != 0)
		{
			foreach ($workflows as $workflow_id)
			{
				$wf_instance = ilWorkflowDbHelper::wakeupWorkflow($workflow_id);
				$wf_instance->handleEvent(
					array(
						$type,
						$content,
						$subject_type,
						$subject_id,
						$context_type,
						$context_id
					)
				);
				ilWorkflowDbHelper::writeWorkflow($wf_instance);
			}
		}
	}

	/**
	 * @param string $component
	 * @param string $event
	 * @param array  $parameter
	 */
	public function handleEvent($component, $event, $parameter)
	{
		// Event incoming, check ServiceDisco (TODO, for now we're using a non-disco factory), call appropriate extractors.

		/** @noinspection PhpIncludeInspection */
		require_once './Services/WorkflowEngine/classes/extractors/class.ilExtractorFactory.php';
		/** @noinspection PhpIncludeInspection */
		require_once './Services/WorkflowEngine/interfaces/ilExtractor.php';

		$extractor = ilExtractorFactory::getExtractorByEventDescriptor($component);

		if($extractor instanceof ilExtractor)
		{
			$extracted_params = $extractor->extract($event, $parameter);

			$this->processEvent(
				$component,
				$event,
				$extracted_params->getSubjectType(),
				$extracted_params->getSubjectId(),
				$extracted_params->getContextType(),
				$extracted_params->getContextId()
			);

			$this->launchArmedWorkflows($extracted_params);
		}
	}

	/**
	 * @param \ilExtractedParams $extractedParams
	 */
	public function launchArmedWorkflows(ilExtractedParams $extractedParams)
	{
		$a = 1;
	}
}