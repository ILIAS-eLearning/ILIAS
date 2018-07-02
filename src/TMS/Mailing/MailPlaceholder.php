<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

use CaT\Ente;

/**
 * This keeps placeholders for email templates.
 * It is provided as an ente-component, since there will be multiple plugins participating
 * in the process.
 */
class MailPlaceholder implements Placeholder {
	/**
	 * @var string
	 */
	protected $placeholder;

	/**
	 * @var string
	 */
	protected $description;

	public function __construct(Ente\Entity $entity, callable $txt, $placeholder, $description)
	{
		$this->entity = $entity;
		$this->txt = $txt;
		$this->placeholder = $placeholder;
		$this->description = $description;
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
	public function getDescription() {
		return $this->description;
	}

	/**
	 * i18n
	 *
	 * @param	string	$id
	 * @return	string	$text
	 */
	protected function txt($id)
	{
		assert('is_string($id)');
		return call_user_func($this->txt, $id);
	}
}