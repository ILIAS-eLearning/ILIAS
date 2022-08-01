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
* GUI class for SCORM Organizations element
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMOrganizationsGUI extends ilSCORMObjectGUI
{
    public function __construct(int $a_id)
    {
        parent::__construct();
        $this->sc_object = new ilSCORMOrganizations($a_id);
    }

    public function view() : void
    {
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.scorm_obj.html", "Modules/ScormAicc");
        $this->tpl->setCurrentBlock("par_table");
        $this->tpl->setVariable("TXT_OBJECT_TYPE", $this->lng->txt("cont_organizations"));
        $this->displayParameter(
            $this->lng->txt("cont_def_organization"),
            $this->sc_object->getDefaultOrganization()
        );
        $this->tpl->parseCurrentBlock();
    }
}
