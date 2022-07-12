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
class ilDataCollectionAccessAdapter implements ilDataCollectionAccessPort
{

    private ilAccess $ilAccess;

    private function __construct(ilAccess $ilAccess)
    {
        $this->ilAccess = $ilAccess;
    }

    public static function new() : self
    {
        global $DIC;
        return new self($DIC->access());
    }

    public function hasVisiblePermission(int $refId) : bool
    {
        return $this->ilAccess->checkAccess('visible', "", $refId);
    }

    public function hasReadPermission(int $refId) : bool
    {
        return $this->ilAccess->checkAccess('read', "", $refId);
    }

    public function hasWritePermission(int $refId) : bool
    {
        return $this->ilAccess->checkAccess('write', "", $refId);
    }

    public function hasEditPermission(int $refId) : bool
    {
        return $this->ilAccess->checkAccess('edit_permission', "", $refId);
    }

    public function hasVisibleOrReadPermission(int $refId) : bool
    {
        return ($this->hasVisiblePermission($refId) || $this->hasReadPermission($refId));
    }
}