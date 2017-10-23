<?php
use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * Course- and Plugin-related placeholder-values
 * Subclass and provide this at plugins.
 */
class ilTMSMailContextEnte implements Mailing\MailContext {
	use ilHandlerObjectHelper;

	protected $entity;
	protected $owner;


	public function __construct($entity, $owner) {
		$this->entity = $entity;
		$this->owner = $owner;
	}

	/**
	 * @inheritdoc
	 */
	public function placeholderIds() {
		return array();
	}

	/**
	 * @inheritdoc
	 */
	public function valueFor($placeholder_id, $contexts = array()) {
		return null;
	}


	/**
	 * @inheritdoc
	 */
	public function entity() {
		return $this->entity;
	}

	/**
	 * Return the owner of the context
	 */
	public function owner() {
		return $this->owner;
	}

	/**
	 * @inheritdoc
	 */
	protected function getEntityRefId() {
		return $this->entity()->getRefId();
	}

	/**
	 * @inheritdoc
	 */
	protected function getDIC() {
		global $DIC;
		return $DIC;
	}

}
