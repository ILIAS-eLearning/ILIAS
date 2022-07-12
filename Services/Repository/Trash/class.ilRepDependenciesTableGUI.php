<?php

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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilRepDependenciesTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;

    /**
    * Constructor
    */
    public function __construct(array $a_deps)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $lng = $DIC->language();
        
        parent::__construct(null, "");
        $lng->loadLanguageModule("rep");

        $this->setTitle($lng->txt("rep_dependencies"));
        $this->setLimit(9999);
        
        $this->addColumn($this->lng->txt("rep_object_to_delete"));
        $this->addColumn($this->lng->txt("rep_dependent_object"));
        $this->addColumn($this->lng->txt("rep_dependency"));
        
        $this->setEnableHeader(true);
        //$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.rep_dep_row.html",
            "Services/Repository/Trash"
        );
        $this->disable("footer");
        $this->setEnableTitle(true);

        $deps = [];
        foreach ($a_deps as $id => $d) {
            foreach ($d as $id2 => $ms) {
                foreach ($ms as $m) {
                    $deps[] = ["dep_obj" => $id2, "del_obj" => $id, "message" => $m];
                }
            }
        }
        $this->setData($deps);
    }
    
    /**
     * Fill table row
     */
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $this->tpl->setVariable(
            "TXT_DEP_OBJ",
            $lng->txt("obj_" . ilObject::_lookupType($a_set["dep_obj"])) . ": " . ilObject::_lookupTitle($a_set["dep_obj"])
        );
        $this->tpl->setVariable(
            "TXT_DEL_OBJ",
            $lng->txt("obj_" . ilObject::_lookupType($a_set["del_obj"])) . ": " . ilObject::_lookupTitle($a_set["del_obj"])
        );
        $this->tpl->setVariable("TXT_MESS", $a_set["message"]);
    }
}
