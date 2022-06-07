<?php declare(strict_types=1);

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
 *********************************************************************/

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
    
    public function performDelete() : void;
    
    public function cut() : void;
    
    public function showLinkIntoMultipleObjectsTree() : void;
    
    public function showMoveIntoObjectTree() : void;
    
    public function performPasteIntoMultipleObjects() : void;
    
    public function paste() : void;

    /**
     * clear clipboard
     */
    public function clear() : void;
    
    public function enableAdministrationPanel() : void;
    
    public function disableAdministrationPanel() : void;
    
    public function cancelMoveLinkObject() : void;

    public function keepObjectsInClipboardObject() : void;
}
