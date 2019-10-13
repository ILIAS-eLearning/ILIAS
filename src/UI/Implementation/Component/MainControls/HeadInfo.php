<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class HeadInfo
 *
 * @package ILIAS\UI\Implementation\Component\MainControls
 */
class HeadInfo implements MainControls\HeadInfo {

	use ComponentHelper;
	/**
	 * @var bool
	 */
	private $is_important = false;
	/**
	 * @var string
	 */
	private $title = '';
	/**
	 * @var string
	 */
	private $description = '';
	/**
	 * @var
	 */
	private $button;


	/**
	 * HeadInfo constructor.
	 *
	 * @param string $title
	 */
	public function __construct(string $title) {
		$this->title = $title;
	}


	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @inheritDoc
	 */
	public function withDescription(string $info_message): MainControls\HeadInfo {
		$clone = clone $this;
		$clone->description = $info_message;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getDescription(): string {
		return $this->description;
	}


	/**
	 * @inheritDoc
	 */
	public function withButton(Shy $shy_button): \ILIAS\UI\Component\MainControls\HeadInfo {
		$clone = clone $this;
		$clone->button = $shy_button;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getButton(): Shy {
		return $this->button;
	}


	/**
	 * @inheritDoc
	 */
	public function withImportance(bool $is_important): \ILIAS\UI\Component\MainControls\HeadInfo {
		$clone = clone $this;
		$clone->is_important = $is_important;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isImportant(): bool {
		return $this->is_important;
	}
}
