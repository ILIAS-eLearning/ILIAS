<?php
namespace ILIAS\Messaging\Example\ExampleAsq\Domainmodel\Question;
use  ILIAS\Messaging\Example\ExampleAsq\Domainmodel\Common\Projection;
use ILIAS\Messaging\Example\ExampleAsq\Domainmodel\Event\QuestionWasCreated;

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