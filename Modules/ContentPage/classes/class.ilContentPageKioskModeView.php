<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\KioskMode\State;
use ILIAS\KioskMode\URLBuilder;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;

/**
 * Class ilContentPageKioskModeView
 */
class ilContentPageKioskModeView extends ilKioskModeView
{
	const CMD_TOGGLE_LEARNING_PROGRESS = 'toggleManualLearningProgress';

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
		$learningProgress = \ilObjectLP::getInstance($this->contentPageObject->getId());
		if ($learningProgress->getCurrentMode() == \ilLPObjSettings::LP_MODE_MANUAL) {
			$isCompleted = ilLPMarks::_hasCompleted($GLOBALS['DIC']->user()->getId(), $this->contentPageObject->getId());

			$this->lng->loadLanguageModule('copa');
			$learningProgressToggleCtrlLabel = $this->lng->txt('copa_btn_lp_toggle_state_completed');
			if (!$isCompleted) {
				$learningProgressToggleCtrlLabel = $this->lng->txt('copa_btn_lp_toggle_state_not_completed');
			}

			$builder->generic(
				$learningProgressToggleCtrlLabel,
				self::CMD_TOGGLE_LEARNING_PROGRESS,
				1
			);
		}
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
		if (self::CMD_TOGGLE_LEARNING_PROGRESS === $command) {
			$learningProgress = \ilObjectLP::getInstance($this->contentPageObject->getId());
			if ($learningProgress->getCurrentMode() == \ilLPObjSettings::LP_MODE_MANUAL) {
				$marks = new ilLPMarks($this->contentPageObject->getId(), $GLOBALS['DIC']->user()->getId());
				$marks->setCompleted(!$marks->getCompleted());
				$marks->update();

				\ilLPStatusWrapper::_updateStatus($this->contentPageObject->getId(), $GLOBALS['DIC']->user()->getId());
			}
		}
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
		global $DIC;

		\ilLearningProgress::_tracProgress(
			$GLOBALS['DIC']->user()->getId(),
			$this->contentPageObject->getId(),
			$this->contentPageObject->getRefId(),
			$this->contentPageObject->getType()
		);

		$DIC->ui()->mainTemplate()->setVariable('LOCATION_CONTENT_STYLESHEET', \ilObjStyleSheet::getContentStylePath(
			$this->contentPageObject->getStyleSheetId()
		));
		$DIC->ui()->mainTemplate()->setCurrentBlock('SyntaxStyle');
		$DIC->ui()->mainTemplate()->setVariable('LOCATION_SYNTAX_STYLESHEET', \ilObjStyleSheet::getSyntaxStylePath());
		$DIC->ui()->mainTemplate()->parseCurrentBlock();

		$forwarder = new \ilContentPagePageCommandForwarder(
			$DIC->http()->request(), $DIC->ctrl(), $DIC->tabs(), $this->lng, $this->contentPageObject
		);
		$forwarder->setPresentationMode(\ilContentPagePageCommandForwarder::PRESENTATION_MODE_EMBEDDED_PRESENTATION);

		return new Legacy($forwarder->forward(''));
	}
}