<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author Jesús López <lopez@leifos.com>
* @version $Id$
*
* @ingroup ServicesPersonalWorkspace
*/
class ilSingleUserShareGUI
{
    protected $tpl;
    protected $ctrl;
    protected $lng;

    public function __construct($wsp_access_handler = null, $wsp_node_id = null)
    {
        global $DIC;

        $this->tpl               = $DIC['tpl'];
        $this->ctrl              = $DIC['ilCtrl'];
        $this->lng               = $DIC['lng'];

        // personal workspace
        $this->wsp_access_handler = $wsp_access_handler;
        $this->wsp_node_id = $wsp_node_id;
    }

    public function executeCommand()
    {
        $forward_class = $this->ctrl->getNextClass($this);

        switch ($forward_class) {
            default:
                if (!($cmd = $this->ctrl->getCmd())) {
                    $cmd = "initShareForm";
                }
                $this->$cmd();
                break;
        }
        return true;
    }

    public function initShareForm(ilPropertyFormGUI $form = null)
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getShareForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function getShareForm()
    {
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();

        $form->setTitle($this->lng->txt("share_content"));
        $form->setId('share_usr');

        $form->setFormAction($this->ctrl->getFormAction($this, 'saveShare'));

        $inp = new ilTextInputGUI($this->lng->txt("share_with"), 'name');
        $inp->setSize(20);
        $form->addItem($inp);

        $form->addCommandButton('saveShare', $this->lng->txt("share"));
        $form->addCommandButton('cancel', $this->lng->txt("cancel"));

        return $form;
    }

    protected function saveShare()
    {
        $form = $this->getShareForm();

        if ($form->checkInput()) {
            if ($user_id = ilObjUser::_lookupId($form->getInput('name'))) {
                $existing = $this->wsp_access_handler->getPermissions($this->wsp_node_id);

                if (!in_array($user_id, $existing)) {
                    $this->wsp_access_handler->addPermission($this->wsp_node_id, $user_id);
                    ilUtil::sendSuccess($this->lng->txt("element_shared"), true);
                } else {
                    ilUtil::sendInfo($this->lng->txt("element_already_shared"), true);
                }
                $this->ctrl->returnToParent($this);
            } else {
                ilUtil::sendFailure($this->lng->txt('search_no_match'), true);
            }
        }
        $this->ctrl->redirect($this);
    }

    public function cancel()
    {
        $this->ctrl->returnToParent($this);
    }
}
