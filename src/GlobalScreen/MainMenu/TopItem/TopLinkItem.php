<?php namespace ILIAS\GlobalScreen\MainMenu\TopItem;

use ILIAS\GlobalScreen\MainMenu\AbstractBaseItem;
use ILIAS\GlobalScreen\MainMenu\hasAction;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isTopItem;

/**
 * Class TopLinkItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopLinkItem extends AbstractBaseItem implements hasTitle, hasAction, isTopItem {

	/**
	 * @var bool
	 */
	protected $is_external_action = false;
	/**
	 * @var string
	 */
	protected $title = '';
	/**
	 * @var string
	 */
	protected $action = '';


	/**
	 * @param string $title
	 *
	 * @return hasTitle|TopLinkItem
	 */
	public function withTitle(string $title): hasTitle {
		$clone = clone($this);
		$clone->title = $title;

		return $clone;
	}


	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @param string $action
	 *
	 * @return hasAction|TopLinkItem
	 */
	public function withAction(string $action): hasAction {
		$clone = clone($this);
		$clone->action = $action;

		return $clone;
	}


	/**
	 * @return string
	 */
	public function getAction(): string {
		return $this->action;
	}


	/**
	 * @param bool $is_external
	 *
	 * @return TopLinkItem
	 */
	public function withIsLinkToExternalAction(bool $is_external): hasAction {
		$clone = clone $this;
		$clone->is_external_action = $is_external;

		return $clone;
	}


	/**
	 * @return bool
	 */
	public function isLinkWithExternalAction(): bool {
		return $this->is_external_action;
	}
}
