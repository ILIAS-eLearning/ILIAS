<?php

/**
 * @author martin@fluxlabs.ch
 */
interface ilDataCollectionEndpointPort
{
    public function getListTablesLink() : string;

    public function getListRecordsLink(string $viewId) : string;

    public function getEditRecordLink(string $viewId, int $recordId) : string;

    public function getListPermissionsLink() : string;

    public function getInfoScreenLink() : string;

    public function getDataCollectionHomeLink(ilObjDataCollectionGUI $dataCollectionGUI) : string;

    public function getEditDclLink(ilObjDataCollectionGUI $dataCollectionGUI) : string;

    public function getCreateDclLink(ilObjDataCollectionGUI $dataCollectionGUI) : string;

    public function getSaveDclEndpoint(ilObjDataCollectionGUI $dataCollectionGUI) : string;

    public function getDataCollectionExportLink() : string;

    public function getQueryRecordDataEndpoint(): string;

    public function isAsyncCall() : bool;

    public function redirect(string $link) : void;

    public function saveParameterTableId(object $guiObject): void;

    public function saveParameterTableviewId(object $guiObject): void;

    public function forwardCommand(object $guiObject): void;
}