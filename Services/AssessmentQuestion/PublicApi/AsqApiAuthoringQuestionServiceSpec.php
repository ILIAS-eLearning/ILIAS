<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqAdditionalConfigSection;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiAuthoringQuestionServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiContainerIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiEventSubscriber;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiQuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\DomainObjectId;
use ILIAS\UI\Component\Link\Link;

/**
 * Interface AsqApiPlayServiceSpecContract
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AsqApiAuthoringQuestionServiceSpec implements AsqApiAuthoringQuestionServiceSpecContract {

	/**
	 * AsqApiAuthoringQuestionServiceSpecInterface constructor.
	 *
	 * @param AsqApiContainerIdContract $container_id
	 * @param AsqApiQuestionIdContract  $question_uuid
	 * @param int                       $actor_user_id
	 * @param Link                      $container_backlink
	 */
	public function __construct(AsqApiContainerIdContract $container_id, AsqApiQuestionIdContract $question_uuid, int $actor_user_id, Link $container_backlink) {

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
}