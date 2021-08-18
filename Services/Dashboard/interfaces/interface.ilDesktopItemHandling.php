<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Interface for gui classes (e.g ilLuceneSearchGUI) that offer add/remove to/from desktop
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
interface ilDesktopItemHandling
{
    /**
     * Add desktop item
     * @access public
     */
    public function addToDeskObject();
    
    /**
     * Remove from desktop
     * @access public
     */
    public function removeFromDeskObject();
}
