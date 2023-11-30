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

declare(strict_types=1);

use ILIAS\UI\Component\Component;

/**
 * @author            Michael Jansen <mjansen@databay.de>
 * @ilCtrl_Calls      ilObjLegalNoticeGUI: ilPermissionGUI, ilImprintGUI
 * @ilCtrl_isCalledBy ilObjLegalNoticeGUI: ilAdministrationGUI
 */
class ilObjLegalNoticeGUI extends ilObject2GUI
{
    private ilImprintGUI $legal_notice_gui;

    public function __construct(int $a_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;
        $this->legal_notice_gui = new ilImprintGUI();
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        $this->legal_notice_gui->setPresentationTitle($this->lng->txt("adm_imprint"));
    }

    public function getType(): string
    {
        return 'impr';
    }

    public function executeCommand(): void
    {
        $this->prepareOutput();

        $nextClass = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();


        switch (strtolower($nextClass)) {
            case strtolower(ilPermissionGUI::class):
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            case (strtolower(ilImprintGUI::class)):
                if (!$this->checkPermissionBool("write")) {
                    $this->legal_notice_gui->setEnableEditing(false);
                }
                $ret = $this->ctrl->forwardCommand($this->legal_notice_gui);

                $this->tpl->setContent($ret);
                break;
            default:
                $cmd .= 'Cmd';
                if (method_exists($this, $cmd)) {
                    $this->tpl->setContent($this->$cmd());
                }
                break;
        }
    }

    public function viewCmd(): string
    {
        // @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
        // $this->ctrl->setCmd('preview');
        return $this->ctrl->forwardCommand($this->legal_notice_gui);
    }
}
