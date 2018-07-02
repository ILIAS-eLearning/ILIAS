<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

use CaT\Ente;

/**
 * This keeps the value of placeholders for email templates.
 * It is provided as an ente-component, since there will be multiple plugins participating
 * in the process.
 */
class MailPlaceholderValue implements PlaceholderValue {
	/**
	 * @var string
	 */
	protected $placeholder;

	/**
	 * @var string
	 */
	protected $value;

	public function __construct(Ente\Entity $entity, $placeholder, $value)
	{
		$this->entity = $entity;
		$this->placeholder = $placeholder;
		$this->value = $value;
	}

	/**
	 * @inheritdocs
	 */
	public function entity() {
		return $this->entity;
	}

	/**
	 * @inheritdocs
	 */
	public function getPlaceholder() {
		return $this->placeholder;
	}

	/**
	 * @inheritdocs
	 */
	public function getValue() {
		return $this->value;
	}
}