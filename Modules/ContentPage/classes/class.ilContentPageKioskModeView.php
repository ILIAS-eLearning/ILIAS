<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\KioskMode\State;
use ILIAS\KioskMode\URLBuilder;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilContentPageKioskModeView
 */
class ilContentPageKioskModeView extends ilKioskModeView
{
	/** @var \ilObjContentPage */
	protected $contentPageObject;

	/**
	 * @inheritDoc
	 */
	protected function getObjectClass(): string
	{
		return \ilObjContentPage::class;
	}

	/**
	 * @inheritDoc
	 */
	protected function setObject(\ilObject $object)
	{
		$this->contentPageObject = $object;
	}

	/**
	 * @inheritDoc
	 */
	protected function hasPermissionToAccessKioskMode(): bool
	{
		return $this->access->checkAccess('read', '', $this->contentPageObject->getRefId());
	}

	/**
	 * @inheritDoc
	 */
	public function buildInitialState(State $empty_state): State
	{
		// TODO: Implement buildInitialState() method.
	}

	/**
	 * @inheritDoc
	 */
	public function buildControls(State $state, ControlBuilder $builder)
	{
		// TODO: Implement buildControls() method.
	}

	/**
	 * @inheritDoc
	 */
	public function updateGet(State $state, string $command, int $param = null): State
	{
		// TODO: Implement updateGet() method.
	}

	/**
	 * @inheritDoc
	 */
	public function updatePost(State $state, string $command, array $post): State
	{
		// TODO: Implement updatePost() method.
	}

	/**
	 * @inheritDoc
	 */
	public function render(
		State $state,
		Factory $factory,
		URLBuilder $url_builder,
		array $post = null
	): Component {
		// TODO: Implement render() method.
	}
}