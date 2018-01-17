<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
abstract class CourseActionImpl implements CourseAction {

	/**
	 * @var	Ente\Entity
	 */
	protected $entity;

	/**
	 * @var	\ilObject
	 */
	protected $owner;

	/**
	 * @var int	
	 */
	protected $priority;

	/**
	 * @var	int[]
	 */
	protected $contexts;

	/**
	 * @var int
	 */
	protected $current_user_id;

	/**
	 * @param Ente\Entity 	$entity
	 * @param \ilObject 	$owner
	 * @param \ilObjUser 	$current_user will most probably be the global user.
	 * @param int 	$priority
	 * @param int[] 	$contexts
	 */
	public function __construct(Ente\Entity $entity, \ilObject $owner, \ilObjUser $current_user, $priority, array $contexts) {
		$this->entity = $entity;
		$this->owner = $owner;
		assert('is_int($priority)');
		$this->priority = $priority;
		$this->contexts = $contexts;

		$this->current_user_id = $current_user->getId();
	}

	/**
	 * @inheritdoc
	 */
	public function entity() {
		return $this->entity;
	}

	/**
	 * @inheritdoc
	 */
	public function getOwner() {
		return $this->owner;
	}

	/**
	 * @inheritdoc
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * @inheritdoc
	 */
	public function hasContext($context) {
		return in_array($context, $this->contexts);
	}

	/**
	 * @inheritdoc
	 */
	abstract public function isAllowedFor($usr_id);

	/**
	 * @inheritdoc
	 */
	abstract public function getLink(\ilCtrl $ctrl, $usr_id);

	/**
	 * @inheritdoc
	 */
	abstract public function getLabel();
}
