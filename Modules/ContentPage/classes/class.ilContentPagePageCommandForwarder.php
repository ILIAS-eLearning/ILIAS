<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\ContentPage\PageMetrics\Event\PageUpdatedEvent;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Style\Content\Object\ObjectFacade;

class ilContentPagePageCommandForwarder implements ilContentPageObjectConstants
{
    /**
     * presentation mode for authoring
     */
    final public const PRESENTATION_MODE_EDITING = 'PRESENTATION_MODE_EDITING';

    /**
     * presentation mode for requesting
     */
    final public const PRESENTATION_MODE_PRESENTATION = 'PRESENTATION_MODE_PRESENTATION';

    /**
     * presentation mode for embedded presentation, e.g. in a kiosk mode
     */
    final public const PRESENTATION_MODE_EMBEDDED_PRESENTATION = 'PRESENTATION_MODE_EMBEDDED_PRESENTATION';

    protected string $presentationMode = self::PRESENTATION_MODE_EDITING;
    protected string $backUrl = '';
    /** @var callable[] */
    protected array $updateListeners = [];
    protected bool $isMediaRequest = false;

    public function __construct(
        protected GlobalHttpState $http,
        protected ilCtrlInterface $ctrl,
        protected ilTabsGUI $tabs,
        protected ilLanguage $lng,
        protected ilObjContentPage $parentObject,
        protected ilObjUser $actor,
        protected Refinery $refinery,
        protected ObjectFacade $content_style_domain
    ) {
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

    public function setIsMediaRequest(bool $isMediaRequest): void
    {
        $this->isMediaRequest = $isMediaRequest;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function onPageUpdate(array $parameters): void
    {
        foreach ($this->updateListeners as $listener) {
            $listener(new PageUpdatedEvent($parameters['page']));
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function updateContentPageOnPageUpdate(array $parameters): void
    {
        $this->parentObject->update();
    }

    public function addUpdateListener(callable $updateListener): void
    {
        $this->updateListeners[] = $updateListener;
    }

    protected function getPageObjectGUI(string $language, bool $isEmbedded = false): ilContentPagePageGUI
    {
        $pageObjectGUI = new ilContentPagePageGUI($this->parentObject->getId(), 0, $isEmbedded, $language);
        $pageObjectGUI->setStyleId(
            $this->content_style_domain->getEffectiveStyleId()
        );

        $pageObjectGUI->obj->addUpdateListener($this, 'updateContentPageOnPageUpdate', []);

        return $pageObjectGUI;
    }

    protected function doesPageExistsForLanguage(string $language): bool
    {
        return ilContentPagePage::_exists($this->parentObject->getType(), $this->parentObject->getId(), $language);
    }

    protected function ensurePageObjectExists(string $language): void
    {
        if (!$this->doesPageExistsForLanguage($language)) {
            $pageObject = new ilContentPagePage();
            $pageObject->setParentId($this->parentObject->getId());
            $pageObject->setId($this->parentObject->getId());
            $pageObject->setLanguage($language);
            $pageObject->createFromXML();
        }
    }

    protected function setBackLinkTab(): void
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

    protected function buildEditingPageObjectGUI(string $language): ilContentPagePageGUI
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

    protected function buildPresentationPageObjectGUI(string $language): ilContentPagePageGUI
    {
        $this->ensurePageObjectExists($language);

        $pageObjectGUI = $this->getPageObjectGUI($language);
        $pageObjectGUI->setEnabledTabs(false);

        $pageObjectGUI->setStyleId(
            $this->content_style_domain->getEffectiveStyleId()
        );

        return $pageObjectGUI;
    }

    protected function buildEmbeddedPresentationPageObjectGUI(string $language): ilContentPagePageGUI
    {
        $this->ensurePageObjectExists($language);

        $pageObjectGUI = $this->getPageObjectGUI($language, true);
        $pageObjectGUI->setEnabledTabs(false);

        $pageObjectGUI->setStyleId(
            $this->content_style_domain->getEffectiveStyleId()
        );

        return $pageObjectGUI;
    }

    public function setPresentationMode(string $presentationMode): void
    {
        $this->presentationMode = $presentationMode;
    }

    /**
     * @throws ilCtrlException
     * @throws ilException
     */
    public function forward(string $ctrlLink = ''): string
    {
        $ot = ilObjectTranslation::getInstance($this->parentObject->getId());
        $language = $ot->getEffectiveContentLang($this->actor->getCurrentLanguage(), $this->parentObject->getType());

        switch ($this->presentationMode) {
            case self::PRESENTATION_MODE_EDITING:

                $pageObjectGui = $this->buildEditingPageObjectGUI($this->isMediaRequest ? $language : '');
                return (string) $this->ctrl->forwardCommand($pageObjectGui);

            case self::PRESENTATION_MODE_PRESENTATION:
                $pageObjectGUI = $this->buildPresentationPageObjectGUI($language);

                if (is_string($ctrlLink) && $ctrlLink !== '') {
                    $pageObjectGUI->setFileDownloadLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_FILE);
                    $pageObjectGUI->setFullscreenLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DISPLAY_FULLSCREEN);
                    $pageObjectGUI->setSourcecodeDownloadScript($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_PARAGRAPH);
                }

                return $this->ctrl->getHTML($pageObjectGUI);

            case self::PRESENTATION_MODE_EMBEDDED_PRESENTATION:
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
