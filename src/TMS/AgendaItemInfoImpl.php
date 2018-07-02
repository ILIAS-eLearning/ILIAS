<?php
/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente;

/**
 * This is an information about a agenda item.
 */
class AgendaItemInfoImpl implements AgendaItemInfo {
	/**
	 * @var	Ente\Entity
	 */
	protected $entity;

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string[]
	 */
	protected $topics;

	/**
	 * @var string
	 */
	protected $contents;

	/**
	 * @var bool
	 */
	protected $idd_relevant;

	/**
	 * @var string
	 */
	protected $goals;


	/**
	 * @var int 	$id
	 * @var string 	$title
	 * @var string[] $topics
	 * @var string 	$contents
	 * @var bool 	$idd_relevant
	 * @var string 	$goals
	 */
	public function __construct(
		Ente\Entity $entity,
		$id,
		$title,
		array $topics,
		$contents,
		$idd_relevant,
		$goals
	) {
		assert('is_int($id)');
		assert('is_string($title)');
		assert('is_string($contents)');
		assert('is_bool($idd_relevant)');
		assert('is_string($goals)');

		$this->entity = $entity;
		$this->id = $id;
		$this->title = $title;
		$this->topics = $topics;
		$this->contents = $contents;
		$this->idd_relevant = $idd_relevant;
		$this->goals = $goals;
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
	public function getId() {
		return $this->id;
	}

	/**
	 * @inheritdocs
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @inheritdocs
	 */
	public function getTopics() {
		return $this->topics;
	}
	/**
	 * @inheritdocs
	 */
	public function getContents() {
		return $this->contents;
	}
	/**
	 * @inheritdocs
	 */
	public function getIDDRelevant() {
		return $this->idd_relevant;
	}
	/**
	 * @inheritdocs
	 */
	public function getGoals() {
		return $this->goals;
	}
}
