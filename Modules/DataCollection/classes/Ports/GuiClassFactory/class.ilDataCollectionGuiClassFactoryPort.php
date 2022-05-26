<?php

/**
 * @author martin@fluxlabs.ch
 */
interface ilDataCollectionGuiClassFactoryPort
{
    public function getIlInfoScreenGUI() : ilDataCollectionGuiClassPort;

    public function getIlCommonActionDispatcherGUI() : ilDataCollectionGuiClassPort;

    public function getIlPermissionGUI(object $dclGuiObject) : ilDataCollectionGuiClassPort;

    public function getIlObjectCopyGUI(ilObjDataCollectionGUI $dclGuiObject) : ilDataCollectionGuiClassPort;

    public function getIlDclTableListGUI(object $dclGuiObject) : ilDataCollectionGuiClassPort;

    public function getIlDclRecordListGUI(
        ilObjDataCollectionGUI $dclGuiObject,
        int $tableId
    ) : ilDataCollectionGuiClassPort;

    public function getIlDclRecordEditGUI(ilObjDataCollectionGUI $dclGuiObject) : ilDataCollectionGuiClassPort;

    public function getIlObjFileGUI(ilObjDataCollectionGUI $dclGuiObject) : ilDataCollectionGuiClassPort;

    public function getIlRatingGUI() : ilDataCollectionGuiClassPort;

    public function getIlDclDetailedViewGUI() : ilDataCollectionGuiClassPort;

    public function getIlNoteGUI() : ilDataCollectionGuiClassPort;

    public function getIlDclPropertyFormGUI() : ilDataCollectionGuiClassPort;

    public function getIlDclExportGUI() : ilDataCollectionGuiClassPort;

    public function getIlDclContentExporter() : ilDclContentExporter;
}