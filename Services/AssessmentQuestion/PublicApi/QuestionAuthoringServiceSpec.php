<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionAuthoringServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\UI\Component\Link\Link;

/**
 * Interface QuestionAuthoringServiceSpec
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionAuthoringServiceSpec implements QuestionAuthoringServiceSpecContract {

	/**
	 * QuestionAuthoringServiceSpec constructor.
	 *
	 * @param int $container_obj_id
	 * @param QuestionIdContract $question_uuid
	 * @param int $actor_user_id
	 * @param Link $container_backlink
	 */
	public function __construct(int $container_obj_id, QuestionIdContract $question_uuid, int $actor_user_id, Link $container_backlink) {

	}


	/**
	 * @param AsqAdditionalConfigSection $asq_additional_config_section
	 */
	public function addAdditionalConfigSection(AsqAdditionalConfigSection $asq_additional_config_section) {
		// TODO: Implement addAdditionalConfigSection() method.
	}


	/**
	 * @param AsqApiEventSubscriber $asq_public_event_subscriber
	 */
	public function subscribeToQuestionCreatedPublicEvent(AsqApiEventSubscriber $asq_public_event_subscriber) {
		// TODO: Implement subscribeToQuestionCreatedPublicEvent() method.
	}


	/**
	 * @param AsqApiEventSubscriber $asq_public_event_subscriber
	 */
	public function subscribeToQuestionEditedPublicEvent(AsqApiEventSubscriber $asq_public_event_subscriber) {
		// TODO: Implement subscribeToQuestionEditedPublicEvent() method.
	}


	/**
	 * @param AsqApiEventSubscriber $asq_public_event_subscriber
	 */
	public function subscribeToQuestionDeletedPublicEvent(AsqApiEventSubscriber $asq_public_event_subscriber) {
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