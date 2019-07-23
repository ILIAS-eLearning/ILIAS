<?php

namespace ILIAS\AssessmentQuestion\Authoring\_PublicApi;

use ilLanguage;

/**
 * Class AsqAuthoringSpec
 *
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class AsqAuthoringSpec {

	/**
	 * @var int;
	 */
	public $container_obj_id;
	/**
	 * @var int;
	 */
	public $container_ref_id;
	/**
	 * @var int
	 */
	public $actor_user_id;
	/**
	 * @var \ILIAS\UI\Component\Link\Standard
	 */
	public $container_backlink;
	/**
	 * @var AsqAdditionalConfigSection[]
	 */
	public $asq_additional_config_sections;
	/**
	 * @var bool
	 */
	public $container_is_learning_module_context = false;


	/**
	 * AsqAuthoringSpec
	 */
	public function __construct(int $container_obj_id, int $container_ref_id, int $actor_user_id, \ILIAS\UI\Component\Link\Standard $container_backlink) {
		$this->container_obj_id = $container_obj_id;
		$this->container_ref_id = $container_ref_id;
		$this->actor_user_id = $actor_user_id;
		$this->container_backlink = $container_backlink;
	}


	/**
	 * @param AsqAdditionalConfigSection $asq_additional_config_section
	 *
	 * Additional Form Seccitons for a question delivered by consumer. E.G. Taxonomie.
	 */
	public function withAdditionalConfigProperty(AsqAdditionalConfigSection $asq_additional_config_section) {
		//
	}

	/**
	 * @param EventListener $listener
	 */
	public function withEventListener(EventListener $listener);


	/**
	 * @deprecated
	 *
	 * @param NewIdListener[] $new_id_listener
	 *
	 * still required to distinguish for the handling of questions between learning
	 * module and test.
	 */
	//TODO
	public function withContainerIsLearningModuleContext(array $new_id_listener) {
		$this->container_is_learning_module_context = true;
	}


	/**
	 * @return int
	 */
	public function getContainerObjId(): int {
		return $this->container_obj_id;
	}


	/**
	 * @return int
	 */
	public function getContainerRefId(): int {
		return $this->container_ref_id;
	}


	/**
	 * @return int
	 */
	public function getActorUserId(): int {
		return $this->actor_user_id;
	}


	/**
	 * @return \ILIAS\UI\Component\Link\Standard
	 */
	public function getContainerBacklink(): \ILIAS\UI\Component\Link\Standard {
		return $this->container_backlink;
	}


	/**
	 * @return AsqAdditionalConfigSection[]
	 */
	public function getAsqAdditionalConfigSections(): array {
		return $this->asq_additional_config_sections;
	}


	/**
	 * @return bool
	 */
	public function isContainerIsLearningModuleContext(): bool {
		return $this->container_is_learning_module_context;
	}


}