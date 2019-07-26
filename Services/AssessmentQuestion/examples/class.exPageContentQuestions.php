<?php

/**
 * For components that needs to integrate the assessment question service in the way,
 * that questions act as independent assessment items client side, the former implementation
 * is mostly kept but has been moved to the assessment question service.
 * 
 * Future visions tries to unify the assessment and the offline presentation
 * like known from the learning module, but up to now there is no technical concept available.
 * Existing visions for feature requests to support offline and/or cached test scenarios
 * will address the requirement of a presentation implementation that acts similar to the presentation
 * in the learning module, but connected to a qualified ajax backend for solution submissions.
 */
class exPageContentQuestions
{
	/**
	 * @var ILIAS\Services\AssessmentQuestion\PublicApi\PlayServiceSpec
	 */
	protected $playServiceSpec;
	
	/**
	 * @var ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionResourcesCollectorContract
	 */
	protected $questionResourcesCollector;
	
	/**
	 * exPageContentQuestions constructor.
	 */
	public function __construct()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$this->playServiceSpec = $DIC->assessment()->specification()->play(
			$this->object->getId(), $DIC->user()->getId()
		);
		
		$this->questionResourcesCollector = $DIC->assessment()->consumer()->questionRessourcesCollector();
	}
	
	/**
	 * @param $a_no_interaction // enables a kind of preview mode
	 * @param $a_mode // currently required by content pages
	 * @return array an array with a htmloffline presentation per question
	 */
	function getQuestionOfflinePresentations($a_no_interaction, $a_mode)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$questionReferences = array(); // initialise with ids of all questions embedded in the content page

		$qstOfflinePresentations = array();
		
		foreach($questionReferences as $questionRef)
		{
			/**
			 * the current integration of questions in e.g. the learning module
			 * stores question references containing the instId and the qstId
			 */
			
			$questionId = ilInternalLink::_extractObjIdOfTarget($questionRef);
			
			/**
			 * the consumer of offline question presentation currently needs to control
			 * the path for question resources like media files or mobs
			 */
			
			$image_path = null;
			if ($a_mode == "offline")
			{
				if ($anyObjParentType == "sahs")
				{
					$image_path = "./objects/";
				}
				if ($anyObjParentType == "lm")
				{
					$image_path = "./assessment/0/".$questionId."/images/";
				}
			}
			
			/**
			 * a question resources collector is passed to the presentation export method, that collets
			 * all kind of resources the consumer needs to organize for the offline presentation.
			 * (js/css files, media files, mobs, etc.)
			 *
			 * a ui component is returned that can be simply rendered.
			 */
			
			$playService = $DIC->assessment()->service()->play($this->playServiceSpec,
				$DIC->assessment()->consumer()->questionUuid($questionId),
				$DIC->assessment()->consumer()->revisionUuid($revisionId)
			);
			
			$qstOfflinePresentations[$questionId] = $playService->GetStandaloneQuestionExportPresentation(
				$this->questionResourcesCollector, $image_path, $a_mode, $a_no_interaction
			);
		}

		return $qstOfflinePresentations;
	}
}