<?php

/**
 * @author martin@fluxlabs.ch
 */
class ilDataCollectionConfigAdapter implements ilDataCollectionConfigPort
{

    private function __construct()
    {

    }

    public static function new()
    {
        return new self();
    }

    public function getDataCollectionUi() : ilDataCollectionUiPort
    {
        return ilDataCollectionUiAdapter::new();
    }

    public function getDataCollectionLanguage() : ilDataCollectionLanguagePort
    {
        return ilDataCollectionLanguageAdapter::new();
    }

    public function getDataCollectionAccess() : ilDataCollectionAccessPort
    {
        return ilDataCollectionAccessAdapter::new();
    }

    public function getDataCollectionEndpoint() : ilDataCollectionEndpointPort
    {
        return ilDataCollectionEndpointPort::new();
    }
}
