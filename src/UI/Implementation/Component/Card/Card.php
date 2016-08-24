<?php

/* Copyright (c) 2016 Amstutz Timon <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Component\Card as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Card implements C\Card {
	use ComponentHelper;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var \ILIAS\UI\Component\Component
	 */
	protected $header_section;

	/**
	 * @var \ILIAS\UI\Component\Component[]
	 */
	protected $content_sections;

	/**
	 * @var \ILIAS\UI\Component\Image\Image
	 */
	protected $image;


	/**
	 * @param $title
	 * @param \ILIAS\UI\Component\Image\Image|null $image
	 */
	public function __construct($title, \ILIAS\UI\Component\Image\Image $image = null){
		$this->checkStringArg("title", $title);

		$this->title = $title;
		$this->image = $image;
	}

	/**
	 * @inheritdoc
	 */
	public function withTitle($title){
		$this->checkStringArg("title", $title);

		$clone = clone $this;
		$clone->title = $title;

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle(){
		return $this->title;
	}

	/**
	 * @inheritdoc
	 */
	public function withImage(\ILIAS\UI\Component\Image\Image $image){
		$clone = clone $this;
		$clone->image = $image;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getImage(){
		return $this->image;
	}

	/**
	 * @inheritdoc
	 */
	public function withSections(array $sections){
		$classes = [\ILIAS\UI\Component\Component::class];
		$this->checkArgListElements("sections",$sections,$classes);

		$clone = clone $this;
		$clone->content_sections = $sections;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getSections(){
		return $this->content_sections;
	}
}
?>
