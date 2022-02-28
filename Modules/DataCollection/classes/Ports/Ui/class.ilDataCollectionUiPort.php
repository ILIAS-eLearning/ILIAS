<?php

/**
 * @author martin@fluxlabs.ch
 */
interface ilDataCollectionUiPort
{
    public function addOnLoadJavaScriptCode(string $a_code) : void;

    public function addJavaScriptFile(string $filePath) : void;

    public function displayFailureMessage(string $message) : void;

    public function displaySuccessMessage(string $message) : void;

    public function displayErrorMessage(string $message) : void;

    public function addTab(string $tabId, string $tabLabel, string $link) : void;

    public function addLocatorItem(string $title, string $link, int $itemId) : void;

    public function activateTab(string $tabId) : void;
}