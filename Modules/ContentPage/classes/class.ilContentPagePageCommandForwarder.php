<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\ContentPage\PageMetrics\Event\PageUpdatedEvent;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

class ilContentPagePageCommandForwarder implements ilContentPageObjectConstants
{
    /**
     * presentation mode for authoring
     */
    public const PRESENTATION_MODE_EDITING = 'PRESENTATION_MODE_EDITING';

    /**
     * presentation mode for requesting
     */
    public const PRESENTATION_MODE_PRESENTATION = 'PRESENTATION_MODE_PRESENTATION';

    /**
     * presentation mode for embedded presentation, e.g. in a kiosk mode
     */
    public const PRESENTATION_MODE_EMBEDDED_PRESENTATION = 'PRESENTATION_MODE_EMBEDDED_PRESENTATION';

    protected string $presentationMode = self::PRESENTATION_MODE_EDITING;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilObjContentPage $parentObject;
    protected string $backUrl = '';
    protected ilObjUser $actor;
    /** @var callable[] */
    protected array $updateListeners = [];
    protected GlobalHttpState $http;
    protected Refinery $refinery;

    public function __construct(
        GlobalHttpState $http,
        ilCtrl $ctrl,
        ilTabsGUI $tabs,
        ilLanguage $lng,
        ilObjContentPage $parentObject,
        ilObjUser $actor,
        Refinery $refinery
    ) {
        $this->http = $http;
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->lng = $lng;
        $this->parentObject = $parentObject;
        $this->actor = $actor;
        $this->refinery = $refinery;

        $this->lng->loadLanguageModule('content');

        $this->backUrl = '';
        if ($this->http->wrapper()->query()->has('backurl')) {
            $this->backUrl = $this->http->wrapper()->query()->retrieve(
                'backurl',
                $this->refinery->kindlyTo()->string()
            );
        }

        if ($this->backUrl !== '') {
            $this->ctrl->setParameterByClass(ilContentPagePageGUI::class, 'backurl', rawurlencode($this->backUrl));
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function onPageUpdate(array $parameters) : void
    {
        foreach ($this->updateListeners as $listener) {
            $listener(new PageUpdatedEvent($parameters['page']));
        }
    }

    public function addUpdateListener(callable $updateListener) : void
    {
        $this->updateListeners[] = $updateListener;
    }

    protected function getPageObjectGUI(string $language, bool $isEmbedded = false) : ilContentPagePageGUI
    {
        $pageObjectGUI = new ilContentPagePageGUI($this->parentObject->getId(), 0, $isEmbedded, $language);
        $pageObjectGUI->setStyleId(
            ilObjStyleSheet::getEffectiveContentStyleId(
                $this->parentObject->getStyleSheetId(),
                $this->parentObject->getType()
            )
        );

        $pageObjectGUI->obj->addUpdateListener($this->parentObject, 'update');

        return $pageObjectGUI;
    }

    protected function doesPageExistsForLanguage(string $language) : bool
    {
        return ilContentPagePage::_exists($this->parentObject->getType(), $this->parentObject->getId(), $language);
    }

    protected function ensurePageObjectExists(string $language) : void
    {
        if (!$this->doesPageExistsForLanguage($language)) {
            $pageObject = new ilContentPagePage();
            $pageObject->setParentId($this->parentObject->getId());
            $pageObject->setId($this->parentObject->getId());
            $pageObject->setLanguage($language);
            $pageObject->createFromXML();
        }
    }

    protected function setBackLinkTab() : void
    {
        $backUrl = $this->ctrl->getLinkTargetByClass(ilObjContentPageGUI::class, self::UI_CMD_VIEW);
        if ($this->backUrl !== '') {
            $backUrlParts = parse_url(ilUtil::stripSlashes($this->backUrl));

            $script = basename($backUrlParts['path']);

            $backUrl = './' . implode('?', [
                $script, $backUrlParts['query']
            ]);
        }

        $this->tabs->setBackTarget($this->lng->txt('back'), $backUrl);
    }

    protected function buildEditingPageObjectGUI(string $language) : ilContentPagePageGUI
    {
        $this->tabs->clearTargets();

        $this->setBackLinkTab();

        $this->ensurePageObjectExists($language);

        $pageObjectGUI = $this->getPageObjectGUI($language);
        $pageObjectGUI->setEnabledTabs(true);

        $page = $pageObjectGUI->getPageObject();
        $page->addUpdateListener($this, 'onPageUpdate', ['page' => $page]);

        return $pageObjectGUI;
    }

    protected function buildPresentationPageObjectGUI(string $language) : ilContentPagePageGUI
    {
        $this->ensurePageObjectExists($language);

        $pageObjectGUI = $this->getPageObjectGUI($language);
        $pageObjectGUI->setEnabledTabs(false);

        $pageObjectGUI->setStyleId(
            ilObjStyleSheet::getEffectiveContentStyleId(
                $this->parentObject->getStyleSheetId(),
                $this->parentObject->getType()
            )
        );

        return $pageObjectGUI;
    }

    protected function buildEmbeddedPresentationPageObjectGUI(string $language) : ilContentPagePageGUI
    {
        $this->ensurePageObjectExists($language);

        $pageObjectGUI = $this->getPageObjectGUI($language, true);
        $pageObjectGUI->setEnabledTabs(false);

        $pageObjectGUI->setStyleId(
            ilObjStyleSheet::getEffectiveContentStyleId(
                $this->parentObject->getStyleSheetId(),
                $this->parentObject->getType()
            )
        );

        return $pageObjectGUI;
    }

    public function setPresentationMode(string $presentationMode) : void
    {
        $this->presentationMode = $presentationMode;
    }

    /**
     * @param string $ctrlLink
     * @return string
     * @throws ilCtrlException
     * @throws ilException
     */
    public function forward(string $ctrlLink = '') : string
    {
        switch ($this->presentationMode) {
            case self::PRESENTATION_MODE_EDITING:

                $pageObjectGui = $this->buildEditingPageObjectGUI('');
                return (string) $this->ctrl->forwardCommand($pageObjectGui);

            case self::PRESENTATION_MODE_PRESENTATION:
                $ot = ilObjectTranslation::getInstance($this->parentObject->getId());
                $language = $ot->getEffectiveContentLang($this->actor->getCurrentLanguage(), $this->parentObject->getType());

                $pageObjectGUI = $this->buildPresentationPageObjectGUI($language);

                if (is_string($ctrlLink) && $ctrlLink !== '') {
                    $pageObjectGUI->setFileDownloadLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_FILE);
                    $pageObjectGUI->setFullscreenLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DISPLAY_FULLSCREEN);
                    $pageObjectGUI->setSourcecodeDownloadScript($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_PARAGRAPH);
                }

                return $this->ctrl->getHTML($pageObjectGUI);

            case self::PRESENTATION_MODE_EMBEDDED_PRESENTATION:
                $ot = ilObjectTranslation::getInstance($this->parentObject->getId());
                $language = $ot->getEffectiveContentLang($this->actor->getCurrentLanguage(), $this->parentObject->getType());

                $pageObjectGUI = $this->buildEmbeddedPresentationPageObjectGUI($language);

                if (is_string($ctrlLink) && $ctrlLink !== '') {
                    $pageObjectGUI->setFileDownloadLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_FILE);
                    $pageObjectGUI->setFullscreenLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DISPLAY_FULLSCREEN);
                    $pageObjectGUI->setSourcecodeDownloadScript($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_PARAGRAPH);
                }

                return $pageObjectGUI->getHTML();

            default:
                throw new ilException('Unknown presentation mode given');
        }
    }
}
