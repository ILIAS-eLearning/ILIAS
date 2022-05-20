<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\ContentPage\PageMetrics\Event\PageUpdatedEvent;

/**
 * Class ilContentPagePageCommandForwarder
 */
class ilContentPagePageCommandForwarder implements ilContentPageObjectConstants
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
    const PRESENTATION_MODE_EMBEDDED_PRESENTATION = 'PRESENTATION_MODE_EMBEDDED_PRESENTATION';

    /** @var string */
    protected $presentationMode = self::PRESENTATION_MODE_EDITING;
    /** @var ilCtrl */
    protected $ctrl;
    /** @var ilLanguage */
    protected $lng;
    /** @var ilTabsGUI */
    protected $tabs;
    /** @var ilObjContentPage */
    protected $parentObject;
    /** @var string */
    protected $backUrl = '';
    /** @var ilObjUser */
    protected $actor;
    /** @var callable[] */
    protected $updateListeners = [];
    protected $isMediaRequest = false;

    /**
     * ilContentPagePageCommandForwarder constructor.
     * @param ServerRequestInterface $request
     * @param ilCtrl $ctrl
     * @param ilTabsGUI $tabs
     * @param ilLanguage $lng
     * @param ilObjContentPage $parentObject
     * @param ilObjUser $actor
     */
    public function __construct(
        ServerRequestInterface $request,
        ilCtrl $ctrl,
        ilTabsGUI $tabs,
        ilLanguage $lng,
        ilObjContentPage $parentObject,
        ilObjUser $actor
    ) {
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->lng = $lng;
        $this->parentObject = $parentObject;
        $this->actor = $actor;

        $this->lng->loadLanguageModule('content');

        $this->backUrl = $request->getQueryParams()['backurl'] ?? '';

        if (strlen($this->backUrl) > 0) {
            $this->ctrl->setParameterByClass('ilcontentpagepagegui', 'backurl', rawurlencode($this->backUrl));
        }
    }

    public function setIsMediaRequest(bool $isMediaRequest) : void
    {
        $this->isMediaRequest = $isMediaRequest;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function onPageUpdate(array $parameters) : void
    {
        foreach ($this->updateListeners as $listener) {
            call_user_func_array(
                $listener,
                [
                    new PageUpdatedEvent($parameters['page'])
                ]
            );
        }
    }

    /**
     * @param callable $updateListener
     */
    public function addUpdateListener(callable $updateListener) : void
    {
        $this->updateListeners[] = $updateListener;
    }

    /**
     * @param string $language
     * @param bool $isEmbedded
     * @return ilContentPagePageGUI
     */
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

    /**
     * @param string $language
     * @return bool|mixed
     */
    protected function doesPageExistsForLanguage(string $language) : bool
    {
        return ilContentPagePage::_exists($this->parentObject->getType(), $this->parentObject->getId(), $language);
    }

    /**
     * @param string $language
     */
    protected function ensurePageObjectExists(string $language)
    {
        if (!$this->doesPageExistsForLanguage($language)) {
            $pageObject = new ilContentPagePage();
            $pageObject->setParentId($this->parentObject->getId());
            $pageObject->setId($this->parentObject->getId());
            $pageObject->setLanguage($language);
            $pageObject->createFromXML();
        }
    }

    /**
     *
     */
    protected function setBackLinkTab() : void
    {
        $backUrl = $this->ctrl->getLinkTargetByClass('ilObjContentPageGUI', self::UI_CMD_VIEW);
        if (strlen($this->backUrl) > 0) {
            $backUrlParts = parse_url(ilUtil::stripSlashes($this->backUrl));

            $script = basename($backUrlParts['path']);

            $backUrl = './' . implode('?', [
                $script, $backUrlParts['query']
            ]);
        }

        $this->tabs->setBackTarget($this->lng->txt('back'), $backUrl);
    }

    /**
     * @param string $language
     * @return ilContentPagePageGUI
     */
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

    /**
     * @param string $language
     * @return ilContentPagePageGUI
     */
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

    /**
     * @param string $language
     * @return ilContentPagePageGUI
     */
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

    /**
     * @param string $presentationMode
     */
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
        $ot = ilObjectTranslation::getInstance($this->parentObject->getId());
        $language = $ot->getEffectiveContentLang($this->actor->getCurrentLanguage(), $this->parentObject->getType());

        switch ($this->presentationMode) {
            case self::PRESENTATION_MODE_EDITING:

                $pageObjectGui = $this->buildEditingPageObjectGUI($this->isMediaRequest ? $language : '');
                return (string) $this->ctrl->forwardCommand($pageObjectGui);

            case self::PRESENTATION_MODE_PRESENTATION:
                $pageObjectGUI = $this->buildPresentationPageObjectGUI($language);

                if (is_string($ctrlLink) && strlen($ctrlLink) > 0) {
                    $pageObjectGUI->setFileDownloadLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_FILE);
                    $pageObjectGUI->setFullscreenLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DISPLAY_FULLSCREEN);
                    $pageObjectGUI->setSourcecodeDownloadScript($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_PARAGRAPH);
                }

                return $this->ctrl->getHTML($pageObjectGUI);

            case self::PRESENTATION_MODE_EMBEDDED_PRESENTATION:
                $pageObjectGUI = $this->buildEmbeddedPresentationPageObjectGUI($language);

                if (is_string($ctrlLink) && strlen($ctrlLink) > 0) {
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
