<?php

/**
 * cat-tms-patch start
 */

namespace ILIAS\TMS\Cockpit;
use CaT\Ente;

class CockpitItemImpl implements CockpitItem {
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $tooltip;

	/**
	 * @var string
	 */
	protected $link;

	/**
	 * @var string
	 */
	protected $icon_path;

	/**
	 * @var string
	 */
	protected $active_icon_path;

	/**
	 * @var string
	 */
	protected $identifier;

	public function __construct(Ente\Entity $entity, $title, $tooltip, $link, $icon_path, $active_icon_path, $identifier) {
		assert('is_string($title)');
		assert('is_string($tooltip)');
		assert('is_string($link)');
		assert('is_string($title)');
		assert('is_string($title)');
		assert('is_string(identifier)');

		$this->entity = $entity;
		$this->title = $title;
		$this->tooltip = $tooltip;
		$this->link = $link;
		$this->icon_path = $icon_path;
		$this->active_icon_path = $active_icon_path;
		$this->identifier = $identifier;
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
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @inheritdoc
	 */
	public function getTooltip() {
		return $this->tooltip;
	}

	/**
	 * @inheritdoc
	 */
	public function getLink() {
		return $this->link;
	}

	/**
	 * @inheritdoc
	 */
	public function getIconPath() {
		return $this->icon_path;
	}

	/**
	 * @inheritdoc
	 */
	public function getActiveIconPath() {
		return $this->active_icon_path;
	}

	/**
	 * @inheritdoc
	 */
	public function getIdentifier() {
		return $this->identifier;
	}
}

/**
 * cat-tms-patch end
 */