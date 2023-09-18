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

class ilSystemStyleDeleteGUI
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected array $styles = [];
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct(ilLanguage $lng, ilCtrl $ctrl)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->lng = $lng;
        $this->ctrl = $ctrl;
    }

    public function addStyle(ilSkin $skin, ilSkinStyle $style, string $img_path): void
    {
        $this->styles[] = [
            'var' => 'style_' . sizeof($this->styles),
            'id' => $skin->getId() . ':' . $style->getId(),
            'text' => $skin->getName() . ' / ' . $style->getName(),
            'img' => $img_path,
            'alt' => ''
        ];
    }

    public function getDeleteStyleFormHTML(): string
    {
        $this->main_tpl->setOnScreenMessage('question', $this->lng->txt('info_delete_sure'), true);

        $table_form = new ilConfirmationTableGUI(true);
        $table_form->setFormName('delete_style');

        $table_form->addCommandButton('confirmDelete', $this->lng->txt('confirm'));
        $table_form->addCommandButton('cancel', $this->lng->txt('cancel'));
        $table_form->setFormAction($this->ctrl->getFormActionByClass('ilSystemStyleOverviewGUI'));
        $table_form->setData($this->styles);
        return $table_form->getHTML();
    }
}
