<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente;

/**
 * Implementation of File.
 */
class FileImpl implements File {

	const IDENT_SEPARATOR = ':';

	/**
	 * @var	Ente\Entity
	 */
	protected $entity;

	/**
	 * @var	\ilObject
	 */
	protected $owner;

	/**
	 * @var string
	 */
	protected $ident;

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var Closure
	 */
	protected $get_file_path;

	/**
	 * @var string
	 */
	protected $type;


	public function __construct(
		$ident,
		$type,
		Ente\Entity $entity,
		\ilObject $owner,
		\Closure $get_file_path
	) {
		assert('is_string($ident)');
		assert('is_string($type)');
		$this->ident = $ident;
		$this->type = $type;
		$this->entity = $entity;
		$this->owner = $owner;
		$this->get_file_path = $get_file_path;
		$this->id = $this->buildIdForComponent();
	}

	/**
	 * @return string
	 */
	private function buildIdForComponent() {
		return implode(
			self::IDENT_SEPARATOR,
			[
				$this->entity()->id(),
				$this->owner->getRefId(),
				$this->ident
			]
		);
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
	public function getId() {
		return $this->id;
	}

	/**
	 * @inheritdoc
	 */
	public function getIdent() {
		return $this->ident;
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
	public function getType() {
		return $this->type;
	}

	/**
	 * @inheritdoc
	 */
	public function getFilePath() {
		$func = $this->getPathClosure();
		return $func($this->owner);
	}

	/**
	 * get the closure to retrieve filepath.
	 *
	 * @return \Closure
	 */
	private function getPathClosure() {
		return $this->get_file_path;
	}

}