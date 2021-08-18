<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * This class implements some dummy methods, normally provided by
 * media wiki classes. It is used by ilWikiUtil methods.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMediaWikiAdapter
{
    /**
    * Do not use any namespaces (yet)
    */
    public function getNsIndex($a_p)
    {
        return false;
    }
    
    /**
    * Do not use interwiki stuff
    */
    public function lc($a_key)
    {
        return false;
    }
}
