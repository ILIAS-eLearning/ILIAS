<?php declare(strict_types=1);

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
 * Select repository nodes
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_IsCalledBy ilRepositorySelector2InputGUI: ilFormPropertyDispatchGUI
 */
class ilRepositorySelector2InputGUI extends ilExplorerSelectInputGUI
{
    protected ?Closure $title_modifier = null;

    public function __construct(
        string $a_title,
        string $a_postvar,
        bool $a_multi = false,
        ?ilPropertyFormGUI $form = null
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->multi_nodes = $a_multi;
        $this->postvar = $a_postvar;
        $form_class = (is_null($form))
            ? ilPropertyFormGUI::class
            : get_class($form);
        $this->explorer_gui = new ilRepositorySelectorExplorerGUI(
            [$form_class, ilFormPropertyDispatchGUI::class, ilRepositorySelector2InputGUI::class],
            $this->getExplHandleCmd(),
            $this,
            "selectRepositoryItem",
            "root_id",
            "rep_exp_sel_" . $a_postvar
        );
        $this->explorer_gui->setSelectMode($a_postvar . "_sel", $this->multi_nodes);

        parent::__construct($a_title, $a_postvar, $this->explorer_gui, $this->multi_nodes);
        $this->setType("rep_select");
    }

    public function setTitleModifier(?Closure $a_val) : void
    {
        $this->title_modifier = $a_val;
        if ($a_val != null) {
            $this->explorer_gui->setNodeContentModifier(function ($a_node) use ($a_val) {
                return $a_val($a_node["child"]);
            });
        } else {
            $this->explorer_gui->setNodeContentModifier(null);
        }
    }

    public function getTitleModifier() : ?Closure
    {
        return $this->title_modifier;
    }

    public function getTitleForNodeId($a_id) : string
    {
        $c = $this->getTitleModifier();
        if (is_callable($c)) {
            return $c($a_id);
        }
        return ilObject::_lookupTitle(ilObject::_lookupObjId((int) $a_id));
    }

    public function getExplorerGUI() : ilRepositorySelectorExplorerGUI
    {
        return $this->explorer_gui;
    }

    public function setExplorerGUI(\ilRepositorySelectorExplorerGUI $explorer) : void
    {
        $this->explorer_gui = $explorer;
    }

    public function getHTML() : string
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $this->postvar);
        $html = parent::render();
        $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $this->str("postvar"));
        return $html;
    }

    public function render(string $a_mode = "property_form") : string
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $this->postvar);
        $ret = parent::render($a_mode);
        $ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $this->str("postvar"));
        return $ret;
    }
}
