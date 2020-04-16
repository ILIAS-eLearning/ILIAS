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

require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMObjectGUI.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResource.php");

/**
* GUI class for SCORM Resource element
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMResourceGUI extends ilSCORMObjectGUI
{
    public function __construct($a_id)
    {
        parent::__construct();
        $this->sc_object = new ilSCORMResource($a_id);
        $files = &$this->sc_object->getFiles();
    }

    public function view()
    {
        $this->tpl = new ilTemplate("tpl.main.html", true, true);
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.scorm_obj.html", "Modules/ScormAicc");
        $this->displayParameter(
            $this->lng->txt("cont_import_id"),
            $this->sc_object->getImportId()
        );
        $this->displayParameter(
            $this->lng->txt("cont_resource_type"),
            $this->sc_object->getResourceType()
        );
        $this->displayParameter(
            $this->lng->txt("cont_scorm_type"),
            $this->sc_object->getScormType()
        );
        $this->displayParameter(
            $this->lng->txt("cont_href"),
            $this->sc_object->getHref()
        );
        $this->displayParameter(
            $this->lng->txt("cont_xml_base"),
            $this->sc_object->getXmlBase()
        );
        $this->tpl->setCurrentBlock("partable");
        $this->tpl->setVariable("TXT_OBJECT_TYPE", $this->lng->txt("cont_resource"));
        $this->tpl->parseCurrentBlock();

        // files
        $files = &$this->sc_object->getFiles();
        for ($i = 0; $i < count($files); $i++) {
            $this->displayParameter(
                $this->lng->txt("cont_href"),
                $files[$i]->getHRef()
            );
        }
        $this->tpl->setCurrentBlock("partable");
        $this->tpl->setVariable("TXT_OBJECT_TYPE", $this->lng->txt("cont_files"));
        $this->tpl->parseCurrentBlock();

        // dependencies
        $deps = &$this->sc_object->getDependencies();
        for ($i = 0; $i < count($deps); $i++) {
            $this->displayParameter(
                $this->lng->txt("cont_id_ref"),
                $deps[$i]->getIdentifierRef()
            );
        }
        $this->tpl->setCurrentBlock("partable");
        $this->tpl->setVariable("TXT_OBJECT_TYPE", $this->lng->txt("cont_dependencies"));
        $this->tpl->parseCurrentBlock();
    }
}
