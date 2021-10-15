<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Interface for GUI classes (PDGUI, LuceneSearchGUI...) that have to
 * handle administration commands (cut delete link)
 * @author Stefan Meyer <meyer@leifos.com>
 */
interface ilAdministrationCommandHandling
{
    
    /**
     * Show delete confirmation
     */
    public function delete() : void;
    
    /**
     * Cancel delete
     */
    public function cancelDelete() : void;
    
    /**
     * Perform Delete
     */
    public function performDelete() : void;
    
    /**
     * Cut object
     */
    public function cut() : void;
    
    /**
     * Target selection link
     */
    public function showLinkIntoMultipleObjectsTree() : void;
    
    /**
     * Target selection cut
     */
    public function showMoveIntoObjectTree() : void;
    
    /**
     * Perform paste into multiple objects
     */
    public function performPasteIntoMultipleObjects() : void;
    
    /**
     * Paste
     */
    public function paste() : void;
    
    /**
     * clear clipboard
     */
    public function clear() : void;
    
    /**
     * Enable administration panel
     */
    public function enableAdministrationPanel() : void;
    
    /**
     * Disable administration panel
     */
    public function disableAdministrationPanel() : void;
    
    /**
     * Cancel move/link
     */
    public function cancelMoveLinkObject() : void;

    /**
     * cancel action but keep objects in clipboard
     */
    public function keepObjectsInClipboardObject() : void;
}
