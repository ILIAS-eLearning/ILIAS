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
interface ilDataCollectionEndpointPort
{
    public function getListTablesLink(): string;

    public function getListRecordsLink(string $viewId): string;

    public function getEditRecordLink(int $viewId, int $recordId): string;

    public function getListPermissionsLink(): string;

    public function getInfoScreenLink(): string;

    public function getDataCollectionHomeLink(ilObjDataCollectionGUI $dataCollectionGUI): string;

    public function getEditDclLink(ilObjDataCollectionGUI $dataCollectionGUI): string;

    public function getCreateDclLink(ilObjDataCollectionGUI $dataCollectionGUI): string;

    public function getSaveDclEndpoint(ilObjDataCollectionGUI $dataCollectionGUI): string;

    public function getDataCollectionExportLink(): string;

    public function getQueryRecordDataEndpoint(): string;

    public function isAsyncCall(): bool;

    public function redirect(string $link): void;

    public function saveParameterTableId(object $guiObject): void;

    public function saveParameterTableviewId(object $guiObject): void;

    public function forwardCommand(object $guiObject): void;
}
