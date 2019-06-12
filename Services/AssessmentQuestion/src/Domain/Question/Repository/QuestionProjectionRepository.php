<?php
namespace ILIAS\AssessmentQuestion\Domainmodel\Question;
use  ILIAS\AssessmentQuestion\Domainmodel\Common\Projection;
use ILIAS\AssessmentQuestion\Domainmodel\Event\QuestionWasCreated;

interface QuestionProjectionRepository extends Projection
{
	/**
	 * Projects a posts creation event
	 *
	 * @param QuestionWasCreated $event
	 *
	 * @return void
	 */
	public function projectQuestionWasCreated(QuestionWasCreated $event);
}