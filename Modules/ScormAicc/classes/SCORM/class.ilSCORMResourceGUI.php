<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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

    public function view(): void
    {
        $this->tpl = new ilGlobalTemplate("tpl.main.html", true, true);
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
