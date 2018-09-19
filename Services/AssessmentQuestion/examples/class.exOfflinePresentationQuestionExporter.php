<?php

/**
 * For components that needs to integrate the assessment question service in the way,
 * that questions act as independent assessment items client side, the former implementation
 * is kept and supported by the ilAsqQuestion interface method toJSON.
 * 
 * Future visions tries to unify the assessment and the learning module presentations,
 * but up to now there is no technical concept available. Existing visions for feature requests
 * to support offline and/or cached test scenarios will address the requirement of a
 * presentation implementation that acts similar to the presentation in the learning module,
 * but connected to a qualified ajax backend for solution submissions.
 */
class exOfflinePresentationQuestionExporter
{
	/**
	 * method for exporting a question for an offline presentation. it needs to reuse
	 * the current learning module integration with the JSON representation of the question. 
	 * 
	 * @param string $a_qst_ref_id // any question reference id containing NIC and questionId
	 * @param string|null $a_image_path
	 * @param string $a_output_mode
	 */
	public function exportQuestion($a_qst_ref_id, $a_image_path = null, $a_output_mode = "presentation")
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$questionId = 0; // extract $questionId from $a_qst_ref_id
		
		/**
		 * get question instance suitable for getting exported as offline presentation
		 */
		
		$questionInstance = $DIC->question()->getOfflineExportableQuestionInstance(
			$questionId, $a_image_path, $a_output_mode
		);
		
		/**
		 * use required question information within current offline presentation implementation
		 */
		
		$questionType = $questionInstance->getQuestionType();
		$questionJson = $questionInstance->toJSON();
	}
}