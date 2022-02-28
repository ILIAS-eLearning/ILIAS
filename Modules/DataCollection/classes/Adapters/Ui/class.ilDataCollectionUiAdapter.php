<?php

/**
 * @author martin@fluxlabs.ch
 */
class ilDataCollectionUiAdapter implements ilDataCollectionUiPort
{
    protected static ?self $instance = null;

    private ilGlobalTemplateInterface $tpl;
    private \ILIAS\DI\UIServices $ui;
    protected \ilTabsGUI $tabs;
    protected \ilErrorHandling $error;
    protected \ilLocatorGUI $locatorGui;

    private function __construct(
        ilGlobalTemplateInterface $tpl,
        \ILIAS\DI\UIServices $ui,
        \ilTabsGUI $tabs,
        \ilErrorHandling $error,
        \ilLocatorGUI $locatorGui,
        \ilHelpGUI $help
    ) {
        $this->tpl = $tpl;
        $this->ui = $ui;
        $this->tabs = $tabs;
        $this->error = $error;
        $this->locatorGui = $locatorGui;

        $help->setScreenIdComponent("dcl");
    }

    public static function new() : self
    {
        if (is_null(static::$instance) === true) {
            global $DIC;
            static::$instance = new self($DIC["tpl"], $DIC->ui(), $DIC->tabs(), $DIC['ilErr'], $DIC['ilLocator'],
                $DIC['ilHelp']);
        }

        return static::$instance;
    }

    public function addOnLoadJavaScriptCode(string $a_code) : void
    {
        $this->tpl->addOnLoadCode($a_code);
    }

    public function addJavaScriptFile(string $filePath) : void
    {
        $this->ui->mainTemplate()->addJavaScript("./Services/UIComponent/Modal/js/Modal.js");
    }

    public function displayFailureMessage(string $message) : void
    {
        $this->ui->mainTemplate()->setOnScreenMessage('failure', $message, true);
    }

    public function displaySuccessMessage(string $message) : void
    {
        $this->ui->mainTemplate()->setOnScreenMessage('success', $message, true);
    }

    public function displayErrorMessage(string $message) : void
    {
        $this->error->raiseError($message);
    }

    public function addTab(string $tabId, string $tabLabel, string $link) : void
    {
        $this->tabs->addTab($tabId, $tabLabel, $link);
    }

    public function activateTab(string $tabId) : void
    {
        $this->tabs->activateTab($tabId);
    }

    public function addLocatorItem(string $title, string $link, int $itemId) : void
    {
        $this->locatorGui->addItem($title, $link, "", $itemId);
    }
}