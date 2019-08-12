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
	 * presentation mode for embedded presentation, e.g. in a kiosk mode
	 */
	const PRESENTATION_MODE_EMBEDDED_PRESENTATION =  'PRESENTATION_MODE_EMBEDDED_PRESENTATION';

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

		$this->lng->loadLanguageModule('content');

		$this->backUrl = $request->getQueryParams()['backurl'] ?? '';

		if (strlen($this->backUrl) > 0) {
			$this->ctrl->setParameterByClass('ilcontentpagepagegui', 'backurl', rawurlencode($this->backUrl));
		}
	}

	/**
	 * @param bool $isEmbedded
	 * @return \ilContentPagePageGUI
	 */
	protected function getPageObjectGUI($isEmbedded = false): \ilContentPagePageGUI
	{
		$pageObjectGUI = new \ilContentPagePageGUI($this->parentObject->getId(), 0, $isEmbedded);
		$pageObjectGUI->setStyleId(
			\ilObjStyleSheet::getEffectiveContentStyleId(
			$this->parentObject->getStyleSheetId(), $this->parentObject->getType())
		);

		$pageObjectGUI->obj->addUpdateListener($this->parentObject, 'update');

		return $pageObjectGUI;
	}

	/**
	 * 
	 */
	protected function ensurePageObjectExists()
	{ 
		if (!\ilContentPagePage::_exists($this->parentObject->getType(), $this->parentObject->getId())) {
			$pageObject = new \ilContentPagePage();
			$pageObject->setParentId($this->parentObject->getId());
			$pageObject->setId($this->parentObject->getId());
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
	protected function buildEditingPageObjectGUI(): \ilContentPagePageGUI
	{
		$this->tabs->clearTargets();

		$this->setBackLinkTab();

		$this->ensurePageObjectExists();

		$pageObjectGUI = $this->getPageObjectGUI();
		$pageObjectGUI->setEnabledTabs(true);

		return $pageObjectGUI;
	}

	/**
	 * @return \ilContentPagePageGUI
	 */
	protected function buildPresentationPageObjectGUI(): \ilContentPagePageGUI
	{
		$this->ensurePageObjectExists();

		$pageObjectGUI = $this->getPageObjectGUI();
		$pageObjectGUI->setEnabledTabs(false);

		$pageObjectGUI->setStyleId(
			\ilObjStyleSheet::getEffectiveContentStyleId(
				$this->parentObject->getStyleSheetId(), $this->parentObject->getType()
			)
		);

		return $pageObjectGUI;
	}

	/**
	 * @return \ilContentPagePageGUI
	 */
	protected function buildEmbeddedPresentationPageObjectGUI(): \ilContentPagePageGUI
	{
		$this->ensurePageObjectExists();

		$pageObjectGUI = $this->getPageObjectGUI(true);
		$pageObjectGUI->setEnabledTabs(false);

		$pageObjectGUI->setStyleId(
			\ilObjStyleSheet::getEffectiveContentStyleId(
				$this->parentObject->getStyleSheetId(), $this->parentObject->getType()
			)
		);

		return $pageObjectGUI;
	}

	/**
	 * @param string $presentationMode
	 */
	public function setPresentationMode(string $presentationMode)
	{
		$this->presentationMode = $presentationMode;
	}

	/**
	 * @param string $ctrlLink
	 * @return string
	 * @throws ilCtrlException
	 * @throws ilException
	 */
	public function forward(string $ctrlLink = ''): string
	{
		switch ($this->presentationMode) {
			case self::PRESENTATION_MODE_EDITING:

				$pageObjectGui = $this->buildEditingPageObjectGUI();
				return (string) $this->ctrl->forwardCommand($pageObjectGui);

			case self::PRESENTATION_MODE_PRESENTATION:
				$pageObjectGUI = $this->buildPresentationPageObjectGUI();

				if (is_string($ctrlLink) && strlen($ctrlLink) > 0) {
					$pageObjectGUI->setFileDownloadLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_FILE);
					$pageObjectGUI->setFullscreenLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DISPLAY_FULLSCREEN);
					$pageObjectGUI->setSourcecodeDownloadScript($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_PARAGRAPH);
				}

				return $this->ctrl->getHTML($pageObjectGUI);

			case self::PRESENTATION_MODE_EMBEDDED_PRESENTATION:
				$pageObjectGUI = $this->buildEmbeddedPresentationPageObjectGUI();

				if (is_string($ctrlLink) && strlen($ctrlLink) > 0) {
					$pageObjectGUI->setFileDownloadLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_FILE);
					$pageObjectGUI->setFullscreenLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DISPLAY_FULLSCREEN);
					$pageObjectGUI->setSourcecodeDownloadScript($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_PARAGRAPH);
				}

				return $pageObjectGUI->getHTML();

			default:
				throw new \ilException('Unknown presentation mode given');
				break;
		}
	}
}