<?php

namespace ILIAS\UI\Implementation\Component\Modal;

/**
 * Class InterruptiveItem
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class InterruptiveItem implements \ILIAS\UI\Component\Modal\InterruptiveItem {

	/**
	 * @var string
	 */
	protected $id;
	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var string
	 */
	protected $description;


	/**
	 * @param        $id
	 * @param        $title
	 * @param string $description
	 */
	public function __construct($id, $title, $description = '') {
		$this->id = $id;
		$this->title = $title;
		$this->description = $description;
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
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @inheritdoc
	 */
	public function getDescription() {
		return $this->description;
	}
}