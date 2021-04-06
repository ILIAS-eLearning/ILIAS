<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * EventHandling Service.
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 */
class ilUIComponentService extends ilService
{

    /**
     * Constructor: read information on component
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Core modules vs. plugged in modules
     */
    public function isCore()
    {
        return true;
    }


    /**
     * Get version of service.
     */
    public function getVersion()
    {
        return "-";
    }
}
