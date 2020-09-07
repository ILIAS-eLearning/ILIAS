<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Interface for GUI classes (PDGUI, LuceneSearchGUI...) that have to handle administration commands (cut delete link)
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesAdministration
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
