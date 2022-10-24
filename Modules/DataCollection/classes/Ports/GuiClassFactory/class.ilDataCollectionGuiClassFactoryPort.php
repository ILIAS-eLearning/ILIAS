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
interface ilDataCollectionGuiClassFactoryPort
{
    public function getIlInfoScreenGUI(): ilDataCollectionGuiClassPort;

    public function getIlCommonActionDispatcherGUI(): ilDataCollectionGuiClassPort;

    public function getIlPermissionGUI(object $dclGuiObject): ilDataCollectionGuiClassPort;

    public function getIlObjectCopyGUI(ilObjDataCollectionGUI $dclGuiObject): ilDataCollectionGuiClassPort;

    public function getIlDclTableListGUI(object $dclGuiObject): ilDataCollectionGuiClassPort;

    public function getIlDclRecordListGUI(
        ilObjDataCollectionGUI $dclGuiObject,
        int $tableId
    ): ilDataCollectionGuiClassPort;

    public function getIlDclRecordEditGUI(ilObjDataCollectionGUI $dclGuiObject): ilDataCollectionGuiClassPort;

    public function getIlObjFileGUI(ilObjDataCollectionGUI $dclGuiObject): ilDataCollectionGuiClassPort;

    public function getIlRatingGUI(): ilDataCollectionGuiClassPort;

    public function getIlDclDetailedViewGUI(): ilDataCollectionGuiClassPort;

    public function getIlNoteGUI(): ilDataCollectionGuiClassPort;

    public function getIlDclPropertyFormGUI(): ilDataCollectionGuiClassPort;

    public function getIlDclExportGUI(): ilDataCollectionGuiClassPort;

    public function getIlDclContentExporter(): ilDclContentExporter;
}
