<?php

/**
 * @author martin@fluxlabs.ch
 */
interface ilDataCollectionAccessPort
{
    public function hasVisibleOrReadPermission(int $refId) : bool;

    public function hasReadPermission(int $refId) : bool;

    public function hasWritePermission(int $refId) : bool;

    public function hasEditPermission(int $refId) : bool;

    public function hasVisiblePermission(int $refId) : bool;
}