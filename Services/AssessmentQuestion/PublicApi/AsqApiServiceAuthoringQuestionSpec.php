<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqAdditionalConfigSection;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiServiceAuthoringQuestionSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiIdContainerContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiEventSubscriber;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiIdQuestionContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\DomainObjectId;
use ILIAS\UI\Component\Link\Link;

/**
 * Interface AsqApiServicePlaySpecContract
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AsqApiServiceAuthoringQuestionSpec implements AsqApiServiceAuthoringQuestionSpecContract {

	/**
	 * AsqApiAuthoringQuestionServiceSpecInterface constructor.
	 *
	 * @param AsqApiIdContainerContract $container_id
	 * @param AsqApiIdQuestionContract  $question_uuid
	 * @param int                       $actor_user_id
	 * @param Link                      $container_backlink
	 */
	public function __construct(AsqApiIdContainerContract $container_id, AsqApiIdQuestionContract $question_uuid, int $actor_user_id, Link $container_backlink) {

	}


	public function addAdditionalConfigSection(AsqAdditionalConfigSection $asq_additional_config_section) {
		// TODO: Implement addAdditionalConfigSection() method.
	}


	public function subscribeToQuestionCreatedPublicEvent(AsqApiEventSubscriber $asq_public_event_subscriber) {
		// TODO: Implement subscribeToQuestionCreatedPublicEvent() method.
	}


	public function subscribeToQuestionEditedPublicEvent(AsqApiEventSubscriber $asq_public_event_subscriber) {
		// TODO: Implement subscribeToQuestionEditedPublicEvent() method.
	}


	public function subscribeToQuestionDeletedPublicEvent(AsqApiEventSubscriber $asq_public_event_subscriber) {
		// TODO: Implement subscribeToQuestionDeletedPublicEvent() method.
	}


	public function withSpecificRevision(string $revision_uuid) {
		// TODO: Implement withSpecificRevision() method.
	}


	public function getActorUserId(): int {
		// TODO: Implement getActorUserId() method.
	}


	public function getContainerBacklink(): Link {
		// TODO: Implement getContainerBacklink() method.
	}


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