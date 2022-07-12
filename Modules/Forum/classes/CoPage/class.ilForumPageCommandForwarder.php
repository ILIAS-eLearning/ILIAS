<?php declare(strict_types=1);

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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Style\Content\Object\ObjectFacade;

class ilForumPageCommandForwarder implements ilForumObjectConstants
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
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilObjForum $parentObject;
    protected string $backUrl = '';
    protected ilObjUser $actor;
    protected GlobalHttpState $http;
    protected ObjectFacade $content_style_domain;

    public function __construct(
        GlobalHttpState $http,
        ilCtrlInterface $ctrl,
        ilTabsGUI $tabs,
        ilLanguage $lng,
        ilObjForum $parentObject,
        ilObjUser $actor,
        ObjectFacade $content_style_domain
    ) {
        $this->http = $http;
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->lng = $lng;
        $this->parentObject = $parentObject;
        $this->actor = $actor;
        $this->content_style_domain = $content_style_domain;

        $this->lng->loadLanguageModule('content');

        $this->backUrl = '';
        if (isset($this->http->request()->getQueryParams()['backurl'])) {
            $this->backUrl = $this->http->request()->getQueryParams()['backurl'];
        }

        if ($this->backUrl !== '') {
            $this->ctrl->setParameterByClass(ilForumPageGUI::class, 'backurl', rawurlencode($this->backUrl));
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function onPageUpdate(array $parameters) : void
    {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function updateForumOnPageUpdate(array $parameters) : void
    {
        $this->parentObject->update();
    }

    protected function getPageObjectGUI(string $language, bool $isEmbedded = false) : ilForumPageGUI
    {
        $pageObjectGUI = new ilForumPageGUI($this->parentObject->getId(), 0, $isEmbedded, $language);
        $pageObjectGUI->setStyleId(
            $this->content_style_domain->getEffectiveStyleId()
        );

        $pageObjectGUI->obj->addUpdateListener($this, 'updateForumOnPageUpdate', []);

        return $pageObjectGUI;
    }

    protected function doesPageExistsForLanguage(string $language) : bool
    {
        return ilForumPage::_exists($this->parentObject->getType(), $this->parentObject->getId(), $language);
    }

    protected function ensurePageObjectExists(string $language) : void
    {
        if (!$this->doesPageExistsForLanguage($language)) {
            $pageObject = new ilForumPage();
            $pageObject->setParentId($this->parentObject->getId());
            $pageObject->setId($this->parentObject->getId());
            $pageObject->setLanguage($language);
            $pageObject->createFromXML();
        }
    }

    protected function setBackLinkTab() : void
    {
        $backUrl = $this->ctrl->getLinkTargetByClass(ilObjForumGUI::class, 'showThreads');
        if ($this->backUrl !== '') {
            $backUrlParts = parse_url(ilUtil::stripSlashes($this->backUrl));

            $script = basename($backUrlParts['path']);

            $backUrl = './' . implode('?', [
                $script, $backUrlParts['query']
            ]);
        }

        $this->tabs->setBackTarget($this->lng->txt('back'), $backUrl);
    }

    protected function buildEditingPageObjectGUI(string $language) : ilForumPageGUI
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

    protected function buildPresentationPageObjectGUI(string $language) : ilForumPageGUI
    {
        $this->ensurePageObjectExists($language);

        $pageObjectGUI = $this->getPageObjectGUI($language);
        $pageObjectGUI->setEnabledTabs(false);
        $pageObjectGUI->setStyleId(
            $this->content_style_domain->getEffectiveStyleId()
        );

        return $pageObjectGUI;
    }

    protected function buildEmbeddedPresentationPageObjectGUI(string $language) : ilForumPageGUI
    {
        $this->ensurePageObjectExists($language);

        $pageObjectGUI = $this->getPageObjectGUI($language, true);
        $pageObjectGUI->setEnabledTabs(false);
        $pageObjectGUI->setStyleId(
            $this->content_style_domain->getEffectiveStyleId()
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
