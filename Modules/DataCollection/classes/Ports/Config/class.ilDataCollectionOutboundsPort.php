<?php

/**
 * @author martin@fluxlabs.ch
 */
interface ilDataCollectionOutboundsPort
{
    public function getDataCollectionUi() : ilDataCollectionUiPort;

    public function getDataCollectionAccess() : ilDataCollectionAccessPort;

    public function getDataCollectionEndpoint() : ilDataCollectionEndpointPort;
}