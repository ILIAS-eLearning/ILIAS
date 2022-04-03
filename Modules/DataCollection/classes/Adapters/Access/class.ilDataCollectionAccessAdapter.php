<?php

/**
 * @author martin@fluxlabs.ch
 */
class ilDataCollectionAccessAdapter implements ilDataCollectionAccessPort
{

    private \ilAccess $ilAccess;

    private function __construct(\ilAccess $ilAccess)
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