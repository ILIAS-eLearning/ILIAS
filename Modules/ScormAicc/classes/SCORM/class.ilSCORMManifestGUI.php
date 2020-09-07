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

require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMManifest.php");

/**
* GUI class for SCORM Manifest element
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMmanifestGUI extends ilSCORMObjectGUI
{
    public function __construct($a_id)
    {
        parent::__construct();
        $this->sc_object = new ilSCORMManifest($a_id);
    }

    public function view()
    {
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.scorm_obj.html", "Modules/ScormAicc");
        $this->displayParameter(
            $this->lng->txt("cont_import_id"),
            $this->sc_object->getImportId()
        );
        $this->displayParameter(
            $this->lng->txt("cont_version"),
            $this->sc_object->getVersion()
        );
        $this->displayParameter(
            $this->lng->txt("cont_xml_base"),
            $this->sc_object->getXmlBase()
        );
        $this->tpl->setCurrentBlock("par_table");
        $this->tpl->setVariable("TXT_OBJECT_TYPE", $this->lng->txt("cont_manifest"));
        $this->tpl->parseCurrentBlock();
    }
}
