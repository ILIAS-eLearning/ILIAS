<?php

/**
 * @author martin@fluxlabs.ch
 */
class ilDataCollectionGuiClassFactoryAdapter implements ilDataCollectionGuiClassFactoryPort
{

    private ilObjDataCollectionGUI $dataCollectionGUI;
    private ilObjDataCollection $dataCollection;

    private function __construct(
        ilObjDataCollectionGUI $dataCollectionGUI,
        ilObjDataCollection $dataCollection
    ) {
        $this->dataCollectionGUI = $dataCollectionGUI;
        $this->dataCollection = $dataCollection;
    }

    public static function new(
        ilObjDataCollectionGUI $dataCollectionGUI,
        ilObjDataCollection $dataCollection
    ) : self {
        return new self();
    }

    public function getIlInfoScreenGUI(): ilDataCollectionGuiClassPort
    {
        $lowerCaseGuiClassName = 'ilinfoscreengui';

        $guiObject = new ilInfoScreenGUI($this->dataCollectionGUI);
        $guiObject->enablePrivateNotes();
        $guiObject->addMetaDataSections($this->dataCollection->getId(), 0, $this->dataCollection->getType());

        return ilDataCollectionGuiClassAdapter::new(
            $lowerCaseGuiClassName,
            $guiObject
        );
    }

    public function getIlCommonActionDispatcherGUI() : ilDataCollectionGuiClassPort
    {
        $lowerCaseGuiClassName = 'ilcommonactiondispatchergui';

        $guiObject = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
        $guiObject->enableCommentsSettings(false);


        return ilDataCollectionGuiClassAdapter::new(
            $lowerCaseGuiClassName,
            $guiObject
        );
    }

    public function getIlPermissionGUI(object $dclGuiObject) : ilDataCollectionGuiClassPort
    {
       $lowerCaseGuiClassName = 'ilpermissiongui';

       $guiObject = new ilPermissionGUI($this);

       return ilDataCollectionGuiClassAdapter::new(
            $lowerCaseGuiClassName,
            $guiObject
       );
    }

    public function getIlObjectCopyGUI(ilObjDataCollectionGUI $dclGuiObject) : ilDataCollectionGuiClassPort
    {
        $lowerCaseGuiClassName = 'ilobjectcopygui';
        $guiObject = new ilObjectCopyGUI($dclGuiObject);
        $guiObject->setType("dcl");

        return ilDataCollectionGuiClassAdapter::new(
            $lowerCaseGuiClassName,
            $guiObject
        );
    }

    public function getIlDclTableListGUI(object $dclGuiObject) : ilDataCollectionGuiClassPort
    {
        $lowerCaseGuiClassName = 'ildcltablelistgui';
        $guiObject = new ilDclTableListGUI($dclGuiObject);

        return ilDataCollectionGuiClassAdapter::new(
            $lowerCaseGuiClassName,
            $guiObject
        );
    }

    public function getIlDclRecordListGUI(ilObjDataCollectionGUI $dclGuiObject, int $tableId) : ilDataCollectionGuiClassPort
    {
        $lowerCaseGuiClassName = 'ildclrecordlistgui';
        $guiObject = new ilDclRecordListGUI($dclGuiObject, $tableId);

        return ilDataCollectionGuiClassAdapter::new(
            $lowerCaseGuiClassName,
            $guiObject
        );
    }

    public function getIlDclRecordEditGUI(ilObjDataCollectionGUI $dclGuiObject) : ilDataCollectionGuiClassPort
    {
        $lowerCaseGuiClassName = 'ildclrecordeditgui';
        $guiObject = new ilDclRecordEditGUI($dclGuiObject);

        return ilDataCollectionGuiClassAdapter::new(
            $lowerCaseGuiClassName,
            $guiObject
        );
    }

    public function getIlObjFileGUI(ilObjDataCollectionGUI $dclGuiObject) : ilDataCollectionGuiClassPort
    {
        $lowerCaseGuiClassName = 'ildclrecordeditgui';
        $guiObject =  new ilObjFile($dclGuiObject->getRefId());
        return ilDataCollectionGuiClassAdapter::new(
            $lowerCaseGuiClassName,
            $guiObject
        );
    }

    public function getIlRatingGUI() : ilDataCollectionGuiClassPort
    {
        // TODO: Implement getIlRatingGUI() method.
    }

    public function getIlDclDetailedViewGUI() : ilDataCollectionGuiClassPort
    {
        // TODO: Implement getIlDclDetailedViewGUI() method.
    }

    public function getIlNoteGUI() : ilDataCollectionGuiClassPort
    {
        // TODO: Implement getIlNoteGUI() method.
    }

    public function getIlDclPropertyFormGUI() : ilDataCollectionGuiClassPort
    {
        // TODO: Implement getIlDclPropertyFormGUI() method.
    }

    public function getIlDclExportGUI() : ilDataCollectionGuiClassPort
    {
        // TODO: Implement getIlDclExportGUI() method.
    }

    public function getIlDclContentExporter() : ilDclContentExporter
    {
        // TODO: Implement getIlDclContentExporter() method.
    }
}