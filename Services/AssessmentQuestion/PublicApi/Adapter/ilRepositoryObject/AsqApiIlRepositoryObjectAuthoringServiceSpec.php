<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Adapter\ilRepositoryObject;

use ILIAS\Services\AssessmentQuestion\PublicApi\AsqAdditionalConfigSection;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqContainerId;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiAuthoringServiceSpec;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiEventSubscriber;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiContainerId;
use ILIAS\UI\Component\Link\Link;

/**
 * Class AsqIlRepositoryObjectPublicAuthoringServiceSpec
 *
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AsqApiIlRepositoryObjectAuthoringServiceSpec implements AsqApiAuthoringServiceSpec {

	/**
	 * @var int
	 */
	protected $actor_user_id;
	/**
	 * @var \ILIAS\UI\Component\Link\Standard
	 */
	protected $container_backlink;
	/**
	 * @var AsqAdditionalConfigSection[]
	 */
	protected $asq_additional_config_sections;
	/**
	 * @var bool
	 */
	protected $container_context_learning_module = false;


	public function __construct(AsqApiContainerId $container_id, int $actor_user_id, Link $container_backlink) {
		// TODO: Implement
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
	 * still required to distinguish for the handling of questions between learning
	 * module and test.
	 *
	 * @deprecated
	 *
	 */
	public function withContainerContextLearningModule(array $new_id_listener) {
		$this->container_context_learning_module_ = true;
	}

	/**
	 * @return bool
	 */
	public function isContainerContextLearningModule(): bool {
		return $this->container_context_learning_module;
	}
}