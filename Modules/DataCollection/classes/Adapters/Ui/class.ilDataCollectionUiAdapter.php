<?php
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
 ********************************************************************
 */

/**
 * @author martin@fluxlabs.ch
 */
class ilDataCollectionUiAdapter implements ilDataCollectionUiPort
{
    protected static ?self $instance = null;

    private ilGlobalTemplateInterface $tpl;
    private ILIAS\DI\UIServices $ui;
    private ilTabsGUI $tabs;
    private ilErrorHandling $error;
    private ilLocatorGUI $locatorGui;
    private ilNavigationHistory $navigationHistory;

    private function __construct(
        ilGlobalTemplateInterface $tpl,
        ILIAS\DI\UIServices $ui,
        ilTabsGUI $tabs,
        ilErrorHandling $error,
        ilLocatorGUI $locatorGui,
        ilHelpGUI $help,
        ilNavigationHistory $navigationHistory
    ) {
        $this->tpl = $tpl;
        $this->ui = $ui;
        $this->tabs = $tabs;
        $this->error = $error;
        $this->locatorGui = $locatorGui;
        $this->navigationHistory = $navigationHistory;

        $help->setScreenIdComponent("dcl");
    }

    public static function new(): self
    {
        if (is_null(static::$instance) === true) {
            global $DIC;
            static::$instance = new self(
                $DIC["tpl"],
                $DIC->ui(),
                $DIC->tabs(),
                $DIC['ilErr'],
                $DIC['ilLocator'],
                $DIC['ilHelp'],
                $DIC['ilNavigationHistory']
            );
        }

        return static::$instance;
    }

    public function addOnLoadJavaScriptCode(string $a_code): void
    {
        $this->tpl->addOnLoadCode($a_code);
    }

    public function addJavaScriptFile(string $filePath): void
    {
        $this->ui->mainTemplate()->addJavaScript("./Services/UIComponent/Modal/js/Modal.js");
    }

    public function displayFailureMessage(string $message): void
    {
        $this->ui->mainTemplate()->setOnScreenMessage('failure', $message, true);
    }

    public function displaySuccessMessage(string $message): void
    {
        $this->ui->mainTemplate()->setOnScreenMessage('success', $message, true);
    }

    public function displayErrorMessage(string $message): void
    {
        $this->error->raiseError($message);
    }

    public function displayInfoMessage(string $message): void
    {
        $this->ui->mainTemplate()->setOnScreenMessage('info', $message, true);
    }

    public function addLocatorItem(string $title, string $link, int $itemId): void
    {
        $this->locatorGui->addItem($title, $link, "", $itemId);
    }

    public function resetTabs(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->clearSubTabs();
    }

    public function setBackTab(string $label, string $link): void
    {
        $this->tabs->setBackTarget($label, $link);
    }

    public function addDataCollectionEndpointToNavigationHistory(int $refId, string $link): void
    {
        $this->navigationHistory->addItem($refId, $link, "dcl");
    }

    public function addCssFile(string $filePath): void
    {
        $this->tpl->addCss($filePath);
    }

    public function addPermaLinkTableView(int $refId, int $tableviewId): void
    {
        $this->tpl->setPermanentLink("dcl", $refId, "_" . $tableviewId);
    }

    public function setContent(string $content): void
    {
        $this->tpl->setContent($content);
    }
}
