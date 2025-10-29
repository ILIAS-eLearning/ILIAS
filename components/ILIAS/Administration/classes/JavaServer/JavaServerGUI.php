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

namespace ILIAS\Administration;

use ilCtrl;
use ilGlobalTemplateInterface;
use ilLanguage;
use ilPropertyFormGUI;
use ilFormSectionHeaderGUI;
use ilTextInputGUI;

/**
 * GUI for Java Server Settings
 *
 * @ilCtrl_isCalledBy    ILIAS\Administration\JavaServerGUI: ilObjServerInfoGUI
 */
readonly class JavaServerGUI
{
    public function __construct(
        private ilCtrl $ctrl,
        private ilGlobalTemplateInterface $tpl,
        private ilLanguage $lng,
        private Setting $settings,
        private bool $has_write_access
    ) {
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("view");
        switch ($cmd) {
            case 'view':
                $this->view();
                break;

            case 'update':
                if ($this->has_write_access) {
                    $this->update();
                }
                break;
        }
    }

    public function view(): void
    {
        $this->tpl->setContent($this->buildForm()->getHTML());
    }

    public function update(): void
    {
        $form = $this->buildForm();
        if ($form->checkInput()) {
            $this->settings->set('rpc_pdf_font', $form->getInput('rpc_pdf_font'));
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this);
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }

    public function buildForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'update'));

        // pdf fonts
        $pdf = new ilFormSectionHeaderGUI();
        $pdf->setTitle($this->lng->txt('rpc_pdf_generation'));
        $form->addItem($pdf);

        $pdf_font = new ilTextInputGUI($this->lng->txt('rpc_pdf_font'), 'rpc_pdf_font');
        $pdf_font->setInfo($this->lng->txt('rpc_pdf_font_info'));
        $pdf_font->setSize(64);
        $pdf_font->setMaxLength(1024);
        $pdf_font->setRequired(true);
        $pdf_font->setValue($this->settings->get('rpc_pdf_font', 'Helvetica, unifont'));
        $form->addItem($pdf_font);

        if ($this->has_write_access) {
            $form->addCommandButton("update", $this->lng->txt("save"));
        }
        return $form;
    }
}
