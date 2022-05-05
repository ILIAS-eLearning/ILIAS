<?php

/**
 * @author martin@fluxlabs.ch
 */
interface ilDataCollectionUiPort
{
    public function addOnLoadJavaScriptCode(string $a_code) : void;

    public function addJavaScriptFile(string $filePath) : void;

    public function addCssFile(string $filePath) : void;

    public function displayFailureMessage(string $message) : void;

    public function displaySuccessMessage(string $message) : void;

    public function displayErrorMessage(string $message) : void;

    public function displayInfoMessage(string $message) : void;

    public function addPermaLinkTableView(int $refId, int $tableviewId) : void;

    public function setContent(string $content) : void;

    public function addDataCollectionEndpointToNavigationHistory(int $refId, string $link) : void;

    public function addTab(string $tabId, string $tabLabel, string $link) : void;

    public function addSubTab(string $tabId, string $tabLabel, string $link) : void;

    public function activateTab(string $tabId) : void;

    public function activateSubTab(string $tabId): void;

    public function addLocatorItem(string $title, string $link, int $itemId) : void;

    public function resetTabs() : void;

    public function setBackTab(string $label, string $link);
}