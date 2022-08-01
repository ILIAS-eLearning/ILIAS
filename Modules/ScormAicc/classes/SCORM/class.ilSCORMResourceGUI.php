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
* GUI class for SCORM Resource element
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMResourceGUI extends ilSCORMObjectGUI
{
    public function __construct(int $a_id)
    {
        parent::__construct();
        $this->sc_object = new ilSCORMResource($a_id);
        $files = &$this->sc_object->getFiles();
    }

    /**
     * @throws ilTemplateException
     */
    public function view() : void
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
        foreach ($files as $value) {
            $this->displayParameter(
                $this->lng->txt("cont_href"),
                $value->getHRef()
            );
        }
        $this->tpl->setCurrentBlock("partable");
        $this->tpl->setVariable("TXT_OBJECT_TYPE", $this->lng->txt("cont_files"));
        $this->tpl->parseCurrentBlock();

        // dependencies
        $deps = &$this->sc_object->getDependencies();
        foreach ($deps as $value) {
            $this->displayParameter(
                $this->lng->txt("cont_id_ref"),
                $value->getIdentifierRef()
            );
        }
        $this->tpl->setCurrentBlock("partable");
        $this->tpl->setVariable("TXT_OBJECT_TYPE", $this->lng->txt("cont_dependencies"));
        $this->tpl->parseCurrentBlock();
    }
}
