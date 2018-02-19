<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Data object for info about a course template.
 */
class CourseTemplateInfo {
	/**
	 * @var	string
	 */
	protected $title;

	/**
	 * @var	int
	 */
	protected $ref_id;

	/**
	 * @var	string|null
	 */
	protected $category_title;

	/**
	 * @var	string|null
	 */
	protected $type_title;

	/**
	 * @param	string	$title
	 * @param	int	$ref_id
	 * @param	string|null	$category_title
	 */
	public function __construct($title, $ref_id, $category_title, $type_title) {
		assert('is_string($title)');
		assert('is_int($ref_id)');
		assert('is_string($category_title) || is_null($category_title)');
		assert('is_string($type_title) || is_null($type_title)');
		$this->title = $title;
		$this->ref_id = $ref_id;
		$this->category_title = $category_title;
		$this->type_title = $type_title;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return int
	 */
	public function getRefId() {
		return $this->ref_id;
	}

	/**
	 * @return string|null
	 */
	public function getCategoryTitle() {
		return $this->category_title;
	}

	/**
	 * @return string|null
	 */
	public function getTypeTitle() {
		return $this->type_title;
	}
}
