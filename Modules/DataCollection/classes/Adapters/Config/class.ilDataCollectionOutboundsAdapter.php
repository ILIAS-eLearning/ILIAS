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
class ilDataCollectionOutboundsAdapter implements ilDataCollectionOutboundsPort
{
    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    public function getDataCollectionUi(): ilDataCollectionUiPort
    {
        return ilDataCollectionUiAdapter::new();
    }

    public function getDataCollectionAccess(): ilDataCollectionAccessPort
    {
        return ilDataCollectionAccessAdapter::new();
    }

    public function getDataCollectionEndpoint(): ilDataCollectionEndpointPort
    {
        return ilDataCollectionEndpointAdapter::new();
    }
}
