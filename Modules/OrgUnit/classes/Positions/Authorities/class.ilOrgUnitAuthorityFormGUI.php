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
 ********************************************************************
 */

use ILIAS\Modules\OrgUnit\ARHelper\BaseForm;

/**
 * Class ilOrgUnitAuthorityFormGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitAuthorityFormGUI extends BaseForm
{
    protected ActiveRecord $object;
    private const F_TITLE = 'title';
    private const F_DESCRIPTION = 'description';

    public function initFormElements(): void
    {
        $te = new ilTextInputGUI($this->lng->txt(self::F_TITLE), self::F_TITLE);
        $te->setRequired(true);
        $this->addItem($te);

        $te = new ilTextAreaInputGUI($this->lng->txt(self::F_DESCRIPTION), self::F_DESCRIPTION);
        $this->addItem($te);

        $c = new ilCustomInputGUI($this->lng->txt('authorities'));
        $f = $this->parent_gui->dic()->ui()->factory();
        $r = $this->parent_gui->dic()->ui()->renderer();
        $modal = $f->modal()->roundtrip("Modal", $f->legacy(''))->withCloseWithKeyboard(false);
        $button = $f->button()
                    ->shy($this->lng->txt("open_authorities_modal"), '#')
                    ->withOnClick($modal->getShowSignal());

        $c->setHtml($r->render([$button, $modal]));
        $this->addItem($c);
    }

    public function fillForm(): void
    {
        $array = array(
            self::F_TITLE => $this->object->getTitle(),
            self::F_DESCRIPTION => $this->object->getDescription(),
        );

        $this->setValuesByArray($array);
    }

    public function fillObject(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        $this->object->setTitle($this->getInput(self::F_TITLE));
        $this->object->setDescription($this->getInput(self::F_DESCRIPTION));

        return true;
    }
}
