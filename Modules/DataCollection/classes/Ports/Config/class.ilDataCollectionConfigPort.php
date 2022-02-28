<?php

/**
 * @author martin@fluxlabs.ch
 */
interface ilDataCollectionConfigPort
{
    public function getDataCollectionUi(): ilDataCollectionUiPort;
    public function getDataCollectionLanguage(): ilDataCollectionLanguagePort;
    public function getDataCollectionAccess(): ilDataCollectionAccessPort;
}