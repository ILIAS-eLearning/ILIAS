<?php

/**
 * @author martin@fluxlabs.ch
 */
interface ilDataCollectionOutboundsPort
{
    public function getDataCollectionUi() : ilDataCollectionUiPort;

    public function getDataCollectionLanguage() : ilDataCollectionLanguagePort;

    public function getDataCollectionAccess() : ilDataCollectionAccessPort;

    public function getDataCollectionEndpoint() : ilDataCollectionEndpointPort;

    public function getDataCollectionGuiClassFactory(
        ilObjDataCollectionGUI $dataCollectionGUI,
        ilObjDataCollection|ilObject $dataCollection
    ) : ilDataCollectionGuiClassFactoryPort;
}