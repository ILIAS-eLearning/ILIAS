<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Interface for GUI classes (PDGUI, LuceneSearchGUI...) that have to handle administration commands (cut delete link)
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
interface ilAdministrationCommandHandling
{
    
    /**
     * Show delete confirmation
     */
    public function delete();
    
    /**
     * Cancel delete
     */
    public function cancelDelete();
    
    /**
     * Perform Delete
     */
    public function performDelete();
    
    /**
     * Cut object
     */
    public function cut();
    
    /**
     * Target selection link
     * @return
     */
    public function showLinkIntoMultipleObjectsTree();
    
    /**
     * Target selection cut
     * @return
     */
    public function showMoveIntoObjectTree();
    
    /**
     * Perform paste into multiple objects
     * @return
     */
    public function performPasteIntoMultipleObjects();
    
    /**
     * Paste
     */
    public function paste();
    
    /**
     * clear clipboard
     */
    public function clear();
    
    /**
     * Enable administration panel
     */
    public function enableAdministrationPanel();
    
    /**
     * Disable administration panel
     */
    public function disableAdministrationPanel();
    
    /**
     * Cancel move/link
     */
    public function cancelMoveLinkObject();

    /**
     * cancel action but keep objects in clipboard
     * @return void
     */
    public function keepObjectsInClipboardObject();
}
