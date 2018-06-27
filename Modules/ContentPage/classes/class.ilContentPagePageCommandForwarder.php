<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPagePageCommandForwarder
 */
class ilContentPagePageCommandForwarder implements \ilContentPageObjectConstants
{
	/**
	 * presentation mode for authoring
	 */
	const PRESENTATION_MODE_EDITING = 'PRESENTATION_MODE_EDITING';

	/**
	 * presentation mode for requesting
	 */
	const PRESENTATION_MODE_PRESENTATION = 'PRESENTATION_MODE_PRESENTATION';


	/**
	 * @var string
	 */
	protected $presentationMode = self::PRESENTATION_MODE_EDITING;

	/**
	 * @var \ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var \ilLanguage
	 */
	protected $lng;

	/**
	 * @var \ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var \ilObjContentPage
	 */
	protected $parentObject;

	/**
	 * @var string
	 */
	protected $backUrl = '';

	/**
	 * ilContentPagePageCommandForwarder constructor.
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \ilCtrl                                  $ctrl
	 * @param \ilTabsGUI                               $tabs
	 * @param \ilLanguage                              $lng
	 * @param \ilObjContentPage                        $parentObject
	 */
	public function __construct(
		\Psr\Http\Message\ServerRequestInterface $request,
		\ilCtrl $ctrl,
		\ilTabsGUI $tabs,
		\ilLanguage $lng,
		\ilObjContentPage $parentObject
	) {
		$this->ctrl         = $ctrl;
		$this->tabs         = $tabs;
		$this->lng          = $lng;
		$this->parentObject = $parentObject;

		$this->tabs->clearTargets();
		$this->lng->loadLanguageModule('content');

		$this->backUrl = isset($request->getQueryParams()['backurl']) ? $request->getQueryParams()['backurl'] : '';

		if (strlen($this->backUrl) > 0) {
			$this->ctrl->setParameterByClass('ilcontentpagepagegui', 'backurl', rawurlencode($this->backUrl));
		}
	}

	/**
	 * @param string $pageObjectType
	 * @param int $pageObjectId
	 * @return \ilContentPagePageGUI
	 */
	protected function getPageObjectGUI($pageObjectType, $pageObjectId)
	{
		$pageObjectGUI = new \ilContentPagePageGUI($pageObjectId);

		$pageObjectGUI->obj->addUpdateListener($this->parentObject, 'update');

		return $pageObjectGUI;
	}

	/**
	 * @param string $pageObjectType
	 * @param int $pageObjectId
	 */
	protected function ensurePageObjectExists($pageObjectType, $pageObjectId)
	{
		if (!\ilContentPagePage::_exists($pageObjectType, $pageObjectId)) {
			$pageObject = new \ilContentPagePage();
			$pageObject->setParentId($this->parentObject->getId());
			$pageObject->setId($pageObjectId);
			$pageObject->createFromXML();
		}
	}

	/**
	 * 
	 */
	protected function setBackLinkTab()
	{
		$backUrl = $this->ctrl->getLinkTargetByClass('ilObjContentPageGUI', self::UI_CMD_VIEW);
		if (strlen($this->backUrl) > 0) {
			$backUrlParts = parse_url(\ilUtil::stripSlashes($this->backUrl));

			$script = basename($backUrlParts['path']);

			$backUrl = './' . implode('?', [
				$script, $backUrlParts['query']
			]);
		}

		$this->tabs->setBackTarget($this->lng->txt('back'), $backUrl);
	}

	/**
	 * @return \ilContentPagePageGUI
	 */
	protected function buildEditingPageObjectGUI()
	{
		$this->setBackLinkTab();

		$this->ensurePageObjectExists(
			$this->parentObject->getType(), $this->parentObject->getId()
		);

		$pageObjectGUI = $this->getPageObjectGUI(
			$this->parentObject->getType(), $this->parentObject->getId()
		);

		$pageObjectGUI->setEnabledTabs(true);

		return $pageObjectGUI;
	}

	/**
	 * @return \ilContentPagePageGUI
	 */
	protected function buildPresentationPageObjectGUI()
	{
		$this->setBackLinkTab();

		$this->ensurePageObjectExists(
			$this->parentObject->getType(), $this->parentObject->getId()
		);

		$pageObjectGUI = $this->getPageObjectGUI(
			$this->parentObject->getType(), $this->parentObject->getId()
		);

		$pageObjectGUI->setEnabledTabs(false);

		return $pageObjectGUI;
	}

	/**
	 * @param string $presentationMode
	 */
	public function setPresentationMode($presentationMode)
	{
		$this->presentationMode = $presentationMode;
	}

	/**
	 * @return string
	 * @throws \ilException
	 */
	public function forward()
	{
		switch ($this->presentationMode) {
			case self::PRESENTATION_MODE_EDITING:

				$pageObjectGui = $this->buildEditingPageObjectGUI();
				return $this->ctrl->forwardCommand($pageObjectGui);

			case self::PRESENTATION_MODE_PRESENTATION:

				$pageObjectGUI = $this->buildPresentationPageObjectGUI();
				$this->ctrl->setCmd('getHTML');

				return $this->ctrl->forwardCommand($pageObjectGUI);

			default:
				throw new \ilException('Unknown presentation mode given');
				break;
		}
	}
}