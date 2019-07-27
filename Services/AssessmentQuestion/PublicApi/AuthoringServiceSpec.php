<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use EventSubscriberContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AdditionalConfigSectionContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\UI\Component\Link\Link;

/**
 * Interface QuestionAuthoringServiceSpec
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 */
class AuthoringServiceSpec implements AuthoringServiceSpecContract {

	/**
	 * QuestionAuthoringServiceSpec constructor.
	 *
	 * @param int $container_obj_id
	 * @param QuestionIdContract $question_uuid
	 * @param int $actor_user_id
	 * @param Link $container_backlink
	 */
	public function __construct(int $container_obj_id, int $actor_user_id, Link $container_backlink) {

	}


	/**
	 * @param AdditionalConfigSection $asq_additional_config_section
	 */
	public function addAdditionalConfigSection(AdditionalConfigSectionContract $asq_additional_config_section) {
		// TODO: Implement addAdditionalConfigSection() method.
		return $this;
	}


	/**
	 * @param EventSubscriber $asq_public_event_subscriber
	 */
	public function subscribeToQuestionCreatedPublicEvent(EventSubscriberContract $asq_public_event_subscriber) {
		// TODO: Implement subscribeToQuestionCreatedPublicEvent() method.
	}


	/**
	 * @param EventSubscriber $asq_public_event_subscriber
	 */
	public function subscribeToQuestionEditedPublicEvent(EventSubscriberContract $asq_public_event_subscriber) {
		// TODO: Implement subscribeToQuestionEditedPublicEvent() method.
	}


	/**
	 * @param EventSubscriber $asq_public_event_subscriber
	 */
	public function subscribeToQuestionDeletedPublicEvent(EventSubscriberContract $asq_public_event_subscriber) {
		// TODO: Implement subscribeToQuestionDeletedPublicEvent() method.
	}


	/**
	 * @param string $revision_uuid
	 */
	public function withSpecificRevision(string $revision_uuid) {
		// TODO: Implement withSpecificRevision() method.
	}


	/**
	 * @return int
	 */
	public function getActorUserId(): int {
		// TODO: Implement getActorUserId() method.
	}


	/**
	 * @return Link
	 */
	public function getContainerBacklink(): Link {
		// TODO: Implement getContainerBacklink() method.
	}


	/**
	 * @return array
	 */
	public function getAsqAdditionalConfigSections(): array {
		// TODO: Implement getAsqAdditionalConfigSections() method.
	}

	/**
	 * @param NewIdListener[] $new_id_listener
	 *
	 * still required?? to distinguish for the handling of questions between learning
	 * module and test.
	 *
	 * @deprecated
	 *
	 */
	public function withContainerContextLearningModule(array $new_id_listener) {
		// TODO: Implement getContainerBacklink() method.
	}

	/**
	 * @return bool
	 */
	public function isContainerContextLearningModule(): bool {
		// TODO: Implement getContainerBacklink() method.
	}
}