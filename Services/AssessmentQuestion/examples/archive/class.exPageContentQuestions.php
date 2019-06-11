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
	 * @var ilAsqQuestionResourcesCollector
	 */
	protected $qstResourcesCollector;
	
	/**
	 * exPageContentQuestions constructor.
	 */
	public function __construct()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$this->qstResourcesCollector = $DIC->question()->getQuestionResourcesCollector();
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
			 * the actual rendering of the question depends on an ilAsqQuestionInstance
			 * that got correctly configured. therefore the question instance is build
			 * by a corresponding ilAsqFactory method 
			 */
			
			$questionInstance = $DIC->question()->getOfflineExportableQuestionInstance(
				$questionId, $image_path, $a_mode
			);
			
			/**
			 * the exporter for the question offline presentation can be requested from the ilAsqFactory
			 */
			
			$qstOfflinePresentationExporter = $DIC->question()->getQuestionOfflinePresentationExporter(
				$questionInstance
			);
			
			/**
			 * the ilAsqQuestionOfflinePresentationExporter returns a renderable component.
			 * 
			 * an instance of ilAsqQuestionResourcesCollector needs to be injected that
			 * collects all kind resources the consumer needs to organize for the offline presentation.
			 * (js/css files, media files, mobs)
			 */
			
			$qstOfflinePresentations[$questionId] = $qstOfflinePresentationExporter->exportQuestion(
				$this->qstResourcesCollector, $a_no_interaction
			);
		}

		return $qstOfflinePresentations;
	}
}