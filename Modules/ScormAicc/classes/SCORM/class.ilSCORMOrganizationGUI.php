<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMOrganization.php");

/**
* GUI class for SCORM Organization element
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMOrganizationGUI extends ilSCORMObjectGUI
{
    public function __construct($a_id)
    {
        parent::__construct();
        $this->sc_object = new ilSCORMOrganization($a_id);
    }

    public function view()
    {
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.scorm_obj.html", "Modules/ScormAicc");
        $this->tpl->setCurrentBlock("par_table");
        $this->tpl->setVariable("TXT_OBJECT_TYPE", $this->lng->txt("cont_organization"));
        $this->displayParameter(
            $this->lng->txt("cont_import_id"),
            $this->sc_object->getImportId()
        );
        $this->displayParameter(
            $this->lng->txt("cont_structure"),
            $this->sc_object->getStructure()
        );
        $this->displayParameter(
            $this->lng->txt("cont_sc_title"),
            $this->sc_object->getTitle()
        );
        $this->tpl->parseCurrentBlock();
    }
}
