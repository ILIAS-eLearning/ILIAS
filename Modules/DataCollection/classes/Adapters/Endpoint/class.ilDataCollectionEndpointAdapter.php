<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

/**
 * @author martin@fluxlabs.ch
 */
class ilDataCollectionEndpointAdapter implements ilDataCollectionEndpointPort
{
    private ilCtrl $ctrl;

    private function __construct(ilCtrl $ctrl)
    {
        $this->ctrl = $ctrl;
    }

    public static function new(): self
    {
        global $DIC;
        return new self($DIC->ctrl());
    }

    /**
     * @throws ilCtrlException
     */
    public function getListTablesLink(): string
    {
        return $this->ctrl->getLinkTargetByClass("ilDclTableListGUI", "listTables");
    }

    public function getEditRecordLink(int $viewId, int $recordId): string
    {
        $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, 'tableview_id', $viewId);
        $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, 'record_id', $recordId);
        return $this->ctrl->getLinkTargetByClass(ilDclDetailedViewGUI::class, 'renderRecord');
    }

    /**
     * @throws ilCtrlException
     */
    public function getListRecordsLink(?string $viewId = null): string
    {
        if ($viewId !== null) {
            $this->ctrl->setParameterByClass("ildclrecordlistgui", "tableview_id", $viewId);
        }
        return $this->ctrl->getLinkTargetByClass("ildclrecordlistgui", "show");
    }

    public function getInfoScreenLink(): string
    {
        return $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary");
    }

    public function redirect(string $link): void
    {
        $this->ctrl->redirectToURL($link);
    }

    /**
     * @throws ilCtrlException
     */
    public function getEditDclLink(ilObjDataCollectionGUI $dataCollectionGUI): string
    {
        return $this->ctrl->getLinkTarget($dataCollectionGUI, "editObject");
    }

    /**
     * @throws ilCtrlException
     */
    public function getDataCollectionExportLink(): string
    {
        return $this->ctrl->getLinkTargetByClass("ildclexportgui", "");
    }

    /**
     * @throws ilCtrlException
     */
    public function getListPermissionsLink(): string
    {
        return $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm");
    }

    public function getCreateDclLink(ilObjDataCollectionGUI $dataCollectionGUI): string
    {
        return $this->ctrl->getLinkTarget($dataCollectionGUI, "createObject");
    }

    public function getSaveDclEndpoint(ilObjDataCollectionGUI $dataCollectionGUI): string
    {
        return $this->ctrl->getLinkTarget($dataCollectionGUI, "saveObject");
    }

    public function getQueryRecordDataEndpoint(): string
    {
        return $this->ctrl->getLinkTargetByClass(
            array(
                'ilrepositorygui',
                'ilobjdatacollectiongui',
                'ildclrecordeditgui',
            ),
            'getRecordData',
            '',
            true
        );
    }

    /**
     * @throws ilCtrlException
     */
    public function getDataCollectionHomeLink(ilObjDataCollectionGUI $dataCollectionGUI): string
    {
        return $this->ctrl->getLinkTarget($dataCollectionGUI, "render");
    }

    public function isAsyncCall(): bool
    {
        return $this->ctrl->isAsynch();
    }

    /**
     * @throws ilCtrlException
     */
    public function forwardCommand(object $guiObject): void
    {
        $this->ctrl->forwardCommand($guiObject);
    }

    /**
     * @throws ilCtrlException
     */
    public function saveParameterTableId(object $guiObject): void
    {
        $this->ctrl->saveParameter($guiObject, "table_id");
    }

    public function saveParameterTableviewId(object $guiObject): void
    {
        $this->ctrl->saveParameter($guiObject, "tableview_id");
    }
}
