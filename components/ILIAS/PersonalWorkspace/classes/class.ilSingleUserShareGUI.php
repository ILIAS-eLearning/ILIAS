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
 * @author Jesús López <lopez@leifos.com>
 */
class ilSingleUserShareGUI
{
    protected ?int $wsp_node_id;
    protected ?ilWorkspaceAccessHandler $wsp_access_handler;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;

    public function __construct(
        ?ilWorkspaceAccessHandler $wsp_access_handler = null,
        ?int $wsp_node_id = null
    ) {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();

        // personal workspace
        $this->wsp_access_handler = $wsp_access_handler;
        $this->wsp_node_id = $wsp_node_id;
    }

    public function executeCommand(): void
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
    }

    public function initShareForm(ilPropertyFormGUI $form = null): void
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getShareForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function getShareForm(): ilPropertyFormGUI
    {
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

    protected function saveShare(): void
    {
        $form = $this->getShareForm();

        if ($form->checkInput()) {
            if ($user_id = ilObjUser::_lookupId((string) $form->getInput('name'))) {
                $existing = $this->wsp_access_handler->getPermissions($this->wsp_node_id);

                if (!in_array($user_id, $existing)) {
                    $this->wsp_access_handler->addPermission($this->wsp_node_id, $user_id);
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("element_shared"), true);
                } else {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt("element_already_shared"), true);
                }
                $this->ctrl->returnToParent($this);
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('search_no_match'), true);
            }
        }
        $this->ctrl->redirect($this);
    }

    public function cancel(): void
    {
        $this->ctrl->returnToParent($this);
    }
}
