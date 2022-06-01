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
interface ilDataCollectionUiPort
{
    public function addOnLoadJavaScriptCode(string $a_code) : void;

    public function addJavaScriptFile(string $filePath) : void;

    public function addCssFile(string $filePath) : void;

    public function displayFailureMessage(string $message) : void;

    public function displaySuccessMessage(string $message) : void;

    public function displayErrorMessage(string $message) : void;

    public function displayInfoMessage(string $message) : void;

    public function addPermaLinkTableView(int $refId, int $tableviewId) : void;

    public function setContent(string $content) : void;

    public function addDataCollectionEndpointToNavigationHistory(int $refId, string $link) : void;

    public function addLocatorItem(string $title, string $link, int $itemId) : void;
}