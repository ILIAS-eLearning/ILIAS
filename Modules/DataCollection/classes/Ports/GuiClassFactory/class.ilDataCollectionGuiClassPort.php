<?php

/**
 * @author martin@fluxlabs.ch
 */
interface ilDataCollectionGuiClassPort
{
    public function getLowerCaseGuiClassName() : string;

    public function getGuiObject() : object;
}